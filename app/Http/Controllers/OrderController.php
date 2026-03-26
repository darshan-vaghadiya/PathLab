<?php

namespace App\Http\Controllers;

use App\Models\B2bClient;
use App\Models\B2bTestPrice;
use App\Models\Order;
use App\Models\OrderTest;
use App\Models\Patient;
use App\Models\ReferringDoctor;
use App\Models\Test;
use App\Models\TestPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $statusFilter = $request->input('status', 'active');

        $orders = Order::with(['patient', 'referringDoctor', 'b2bClient', 'creator'])
            ->withCount([
                'orderTests',
                'orderTests as approved_tests_count' => fn ($q) => $q->where('status', 'approved'),
                'orderTests as pending_tests_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->when($statusFilter && $statusFilter !== 'all', function ($query) use ($statusFilter) {
                $query->where('status', $statusFilter);
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('patient', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($request->payment_status, function ($query, $status) {
                $query->where('payment_status', $status);
            })
            ->when($request->source, function ($query, $source) {
                $query->where('source', $source);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Add computed test_status to each order
        $orders->getCollection()->transform(function ($order) {
            if ($order->order_tests_count === 0) {
                $order->test_status = 'no_tests';
            } elseif ($order->approved_tests_count === $order->order_tests_count) {
                $order->test_status = 'all_approved';
            } elseif ($order->pending_tests_count === $order->order_tests_count) {
                $order->test_status = 'pending';
            } else {
                $order->test_status = 'in_progress';
            }
            return $order;
        });

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only('search', 'date_from', 'date_to', 'source', 'payment_status', 'status'),
        ]);
    }

    public function create(): Response
    {
        $tests = Test::where('is_active', true)->with('category')->orderBy('name')->get();
        $testsByCategory = $tests->groupBy(fn ($t) => $t->category->name);

        return Inertia::render('Orders/Create', [
            'patients' => Patient::orderBy('name')->get(),
            'tests' => $testsByCategory,
            'packages' => TestPackage::where('is_active', true)->with('tests')->get(),
            'referringDoctors' => ReferringDoctor::where('is_active', true)->orderBy('name')->get(),
            'b2bClients' => B2bClient::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'new_patient' => 'nullable|array',
            'new_patient.name' => 'nullable|required_without:patient_id|string|max:255',
            'new_patient.age' => 'nullable|integer|min:0|max:150',
            'new_patient.age_unit' => 'nullable|in:years,months,days',
            'new_patient.gender' => 'nullable|in:male,female,other',
            'new_patient.phone' => 'nullable|string|max:20',
            'referring_doctor_id' => 'nullable|exists:referring_doctors,id',
            'b2b_client_id' => 'nullable|exists:b2b_clients,id',
            'source' => 'required|in:walk_in,referral,b2b,home_visit',
            'home_visit_address' => 'nullable|required_if:source,home_visit|string|max:500',
            'home_visit_charge' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:flat,percentage,amount,percent',
            'payment_mode' => 'nullable|in:cash,card,upi,credit',
            'amount_paid' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'selected_tests' => 'nullable|array',
            'selected_tests.*' => 'exists:tests,id',
            'selected_packages' => 'nullable|array',
            'selected_packages.*' => 'exists:test_packages,id',
        ]);

        // Must have at least one test or package
        if (empty($validated['selected_tests']) && empty($validated['selected_packages'])) {
            return back()->withErrors(['selected_tests' => 'Please select at least one test or package.']);
        }

        $order = DB::transaction(function () use ($validated, $request) {
            // Create new patient if needed
            $patientId = $validated['patient_id'] ?? null;
            if (empty($patientId) && !empty($validated['new_patient']['name'])) {
                $patient = Patient::create($validated['new_patient']);
                $patientId = $patient->id;
            }

            // Gather all test IDs (from individual tests and packages)
            $allTestIds = collect($validated['selected_tests'] ?? []);

            if (!empty($validated['selected_packages'])) {
                $packageTestIds = TestPackage::whereIn('id', $validated['selected_packages'])
                    ->with('tests')
                    ->get()
                    ->pluck('tests')
                    ->flatten()
                    ->pluck('id');

                $allTestIds = $allTestIds->merge($packageTestIds)->unique();
            }

            // Get B2B custom prices if applicable
            $b2bPrices = [];
            if (!empty($validated['b2b_client_id'])) {
                $b2bPrices = B2bTestPrice::where('b2b_client_id', $validated['b2b_client_id'])
                    ->whereIn('test_id', $allTestIds)
                    ->pluck('price', 'test_id')
                    ->toArray();
            }

            // Get standard test prices
            $tests = Test::whereIn('id', $allTestIds)->get()->keyBy('id');

            // Calculate total
            $totalAmount = 0;
            $orderTestData = [];

            foreach ($allTestIds as $testId) {
                $test = $tests[$testId] ?? null;
                if (!$test) continue;

                // Use B2B price if available, otherwise standard price
                $price = $b2bPrices[$testId] ?? $test->price;
                $totalAmount += $price;

                $orderTestData[] = [
                    'test_id' => $testId,
                    'price' => $price,
                    'status' => 'pending',
                ];
            }

            // Add home visit charge
            $homeVisitCharge = (float) ($validated['home_visit_charge'] ?? 0);
            $totalAmount += $homeVisitCharge;

            // Calculate discount
            $discountInput = (float) ($validated['discount'] ?? 0);
            $discountType = $validated['discount_type'] ?? 'flat';
            $discount = in_array($discountType, ['percentage', 'percent'])
                ? round($totalAmount * $discountInput / 100, 2)
                : $discountInput;
            $netAmount = $totalAmount - $discount;
            $amountPaid = (float) ($validated['amount_paid'] ?? 0);

            // Determine payment status
            $paymentStatus = 'pending';
            if ($amountPaid >= $netAmount && $netAmount > 0) {
                $paymentStatus = 'paid';
            } elseif ($amountPaid > 0) {
                $paymentStatus = 'partial';
            }

            // Create order
            $order = Order::create([
                'patient_id' => $patientId,
                'referring_doctor_id' => $validated['referring_doctor_id'] ?? null,
                'b2b_client_id' => $validated['b2b_client_id'] ?? null,
                'source' => $validated['source'],
                'home_visit_address' => $validated['home_visit_address'] ?? null,
                'home_visit_charge' => $homeVisitCharge,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'payment_status' => $paymentStatus,
                'payment_mode' => $validated['payment_mode'],
                'amount_paid' => $amountPaid,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Create order tests
            foreach ($orderTestData as $data) {
                OrderTest::create(array_merge($data, ['order_id' => $order->id]));
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order created successfully. Order #' . $order->order_number);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'patient',
            'referringDoctor',
            'b2bClient',
            'creator',
            'report',
            'cancelledByUser',
            'orderTests.test.category',
            'orderTests.test.parameters',
            'orderTests.results.parameter.ranges',
            'orderTests.sampleCollector',
            'orderTests.resultEnterer',
            'orderTests.approver',
        ]);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updatePayment(Request $request, Order $order): RedirectResponse
    {
        if ($order->status === 'cancelled') {
            return redirect()->back()->with('error', 'Cannot update payment for a cancelled order.');
        }

        $validated = $request->validate([
            'payment_mode' => 'required|in:cash,card,upi,credit',
            'amount_paid' => 'required|numeric|min:0',
        ]);

        $amountPaid = (float) $validated['amount_paid'];
        $netAmount = (float) $order->net_amount;

        $paymentStatus = 'pending';
        if ($amountPaid >= $netAmount && $netAmount > 0) {
            $paymentStatus = 'paid';
        } elseif ($amountPaid > 0) {
            $paymentStatus = 'partial';
        }

        $order->update([
            'payment_mode' => $validated['payment_mode'],
            'amount_paid' => $amountPaid,
            'payment_status' => $paymentStatus,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Payment updated successfully.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if ($order->isCancelled()) {
            return back()->with('error', 'This order is already cancelled.');
        }

        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);

            $order->orderTests()
                ->whereNotIn('status', ['approved'])
                ->update(['status' => 'cancelled']);
        });

        return redirect()->back()->with('success', 'Order cancelled successfully.');
    }

    public function printReceipt(Order $order)
    {
        $order->load(['patient', 'orderTests.test', 'referringDoctor']);

        $labSettings = [
            'lab_name' => \App\Models\LabSetting::get('lab_name', 'PathLab'),
            'lab_phone' => \App\Models\LabSetting::get('lab_phone', ''),
            'lab_email' => \App\Models\LabSetting::get('lab_email', ''),
            'lab_address' => \App\Models\LabSetting::get('lab_address', ''),
            'lab_logo' => \App\Models\LabSetting::get('lab_logo', ''),
            'doctor_signature' => \App\Models\LabSetting::get('doctor_signature', ''),
            'doctor_name' => \App\Models\LabSetting::get('doctor_name', ''),
            'doctor_qualification' => \App\Models\LabSetting::get('doctor_qualification', ''),
            'technician_signature' => \App\Models\LabSetting::get('technician_signature', ''),
            'technician_name' => \App\Models\LabSetting::get('technician_name', ''),
            'approver_signature' => \App\Models\LabSetting::get('approver_signature', ''),
            'approver_name' => \App\Models\LabSetting::get('approver_name', ''),
            'approver_qualification' => \App\Models\LabSetting::get('approver_qualification', ''),
            'report_design' => \App\Models\LabSetting::get('report_design', ''),
            'report_header_text' => \App\Models\LabSetting::get('report_header_text', ''),
            'report_footer_text' => \App\Models\LabSetting::get('report_footer_text', ''),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.receipt', [
            'order' => $order,
            'labSettings' => $labSettings,
        ])->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm width receipt

        return $pdf->stream("receipt-{$order->order_number}.pdf");
    }
}

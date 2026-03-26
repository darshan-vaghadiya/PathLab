<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTest;
use App\Models\OrderTestResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LabController extends Controller
{
    public function pendingSamples(Request $request): Response
    {
        $query = Order::where('orders.status', '!=', 'cancelled')
            ->whereHas('orderTests', function ($q) {
                $q->where('status', 'pending');
            })->with(['patient', 'orderTests' => function ($q) {
                $q->where('status', 'pending')->with('test:id,name,short_name');
            }])->withCount(['orderTests as pending_samples_count' => function ($q) {
                  $q->where('status', 'pending');
              }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        return Inertia::render('Lab/PendingSamples', [
            'orders' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function collectSamplesForOrder(Order $order): Response
    {
        $order->load([
            'patient',
            'orderTests' => function ($q) {
                $q->where('status', 'pending')->with('test');
            },
        ]);

        return Inertia::render('Lab/CollectSamples', [
            'order' => $order,
        ]);
    }

    public function collectSample(OrderTest $orderTest): RedirectResponse
    {
        if ($orderTest->status !== 'pending') {
            return back()->with('error', 'Sample has already been collected for this test.');
        }

        $orderTest->update([
            'status' => 'sample_collected',
            'sample_collected_at' => now(),
            'sample_collected_by' => Auth::id(),
        ]);

        return redirect()->route('lab.collectSamples', $orderTest->order)->with('success', 'Sample collected successfully.');
    }

    public function batchCollect(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_test_ids' => 'required|array|min:1',
            'order_test_ids.*' => 'exists:order_tests,id',
        ]);

        $updated = OrderTest::whereIn('id', $validated['order_test_ids'])
            ->where('order_id', $order->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'sample_collected',
                'sample_collected_at' => now(),
                'sample_collected_by' => Auth::id(),
            ]);

        return redirect()->route('lab.collectSamples', $order)->with('success', "{$updated} sample(s) collected successfully.");
    }

    public function pendingResults(Request $request): Response
    {
        $query = Order::where('orders.status', '!=', 'cancelled')
            ->whereHas('orderTests', function ($q) {
                $q->whereIn('status', ['sample_collected', 'rejected']);
            })->with(['patient', 'orderTests' => function ($q) {
                $q->whereIn('status', ['sample_collected', 'rejected'])->with(['test:id,name,short_name', 'rejector:id,name']);
            }])->withCount(['orderTests as pending_results_count' => function ($q) {
                  $q->whereIn('status', ['sample_collected', 'rejected']);
              }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        return Inertia::render('Lab/PendingResults', [
            'orders' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only('search'),
        ]);
    }

    public function enterResultsForOrder(Order $order): Response
    {
        $order->load([
            'patient',
            'orderTests' => function ($q) {
                $q->whereIn('status', ['sample_collected', 'rejected'])
                  ->with(['test.parameters.ranges', 'test.parameters.templates', 'results.parameter', 'rejector:id,name']);
            },
        ]);

        return Inertia::render('Lab/EnterResults', [
            'order' => $order,
        ]);
    }

    public function enterResult(Request $request, OrderTest $orderTest): RedirectResponse
    {
        if (!in_array($orderTest->status, ['sample_collected', 'rejected'])) {
            return back()->with('error', 'This test is not ready for result entry.');
        }

        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.parameter_id' => 'required|exists:test_parameters,id',
            'results.*.value' => 'nullable|string',
            'result_remarks' => 'nullable|string|max:1000',
        ]);

        // Create/update OrderTestResult for each parameter
        foreach ($validated['results'] as $result) {
            OrderTestResult::updateOrCreate(
                [
                    'order_test_id' => $orderTest->id,
                    'test_parameter_id' => $result['parameter_id'],
                ],
                [
                    'result_value' => $result['value'] ?? null,
                ]
            );
        }

        $updateData = [
            'result_remarks' => $validated['result_remarks'] ?? null,
            'status' => 'completed',
            'result_entered_at' => now(),
            'result_entered_by' => Auth::id(),
        ];

        // Clear rejection fields if re-entering results for a rejected test
        if ($orderTest->status === 'rejected') {
            $updateData['rejected_at'] = null;
            $updateData['rejected_by'] = null;
            $updateData['rejection_reason'] = null;
        }

        $orderTest->update($updateData);

        return redirect()->route('lab.enterResults', $orderTest->order)->with('success', 'Result entered successfully.');
    }
}

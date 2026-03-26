<?php

namespace App\Http\Controllers;

use App\Jobs\SendReportReadySms;
use App\Models\Order;
use App\Models\OrderTest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function pendingApprovals(Request $request): Response
    {
        $orders = Order::with('patient')
            ->where('orders.status', '!=', 'cancelled')
            ->whereHas('orderTests', fn ($q) => $q->whereIn('status', ['completed', 'rejected']))
            ->withCount(['orderTests as completed_tests_count' => fn ($q) => $q->whereIn('status', ['completed', 'rejected'])])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Doctor/PendingApprovals', [
            'orders' => $orders,
        ]);
    }

    public function reviewOrder(Order $order): Response
    {
        $order->load([
            'patient',
            'orderTests' => function ($query) {
                $query->whereIn('status', ['completed', 'approved', 'rejected']);
            },
            'orderTests.test.category',
            'orderTests.test.parameters',
            'orderTests.results.parameter.ranges',
            'orderTests.resultEnterer',
            'orderTests.rejector',
        ]);

        return Inertia::render('Doctor/ReviewOrder', [
            'order' => $order,
        ]);
    }

    public function approveTest(OrderTest $orderTest): RedirectResponse
    {
        if ($orderTest->status !== 'completed') {
            return back()->with('error', 'This test is not ready for approval.');
        }

        $orderTest->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Check if all tests in the order are now approved
        $this->checkAndCreateReport($orderTest->order_id);

        return back()->with('success', 'Test approved successfully.');
    }

    public function approveAllTests(Order $order): RedirectResponse
    {
        $updated = $order->orderTests()
            ->where('status', 'completed')
            ->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);

        if ($updated === 0) {
            return back()->with('error', 'No tests available for approval.');
        }

        // Check if all tests in the order are now approved
        $this->checkAndCreateReport($order->id);

        return back()->with('success', "{$updated} test(s) approved successfully.");
    }

    public function rejectTest(Request $request, OrderTest $orderTest): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($orderTest->status !== 'completed') {
            return back()->with('error', 'Only completed tests can be rejected.');
        }

        $orderTest->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => Auth::id(),
            'rejection_reason' => $validated['rejection_reason'],
            'result_entered_at' => null,
            'result_entered_by' => null,
        ]);

        return back()->with('success', 'Test rejected successfully.');
    }

    public function rejectAllTests(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $updated = $order->orderTests()
            ->where('status', 'completed')
            ->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
                'rejection_reason' => $validated['rejection_reason'],
                'result_entered_at' => null,
                'result_entered_by' => null,
            ]);

        if ($updated === 0) {
            return back()->with('error', 'No completed tests available for rejection.');
        }

        return back()->with('success', "{$updated} test(s) rejected.");
    }

    /**
     * If all tests in an order are approved, auto-create a Report record.
     */
    private function checkAndCreateReport(int $orderId): void
    {
        $order = Order::withCount([
            'orderTests',
            'orderTests as approved_tests_count' => function ($query) {
                $query->where('status', 'approved');
            },
        ])->find($orderId);

        if (!$order) return;

        // All tests approved and no report yet
        if ($order->order_tests_count > 0
            && $order->order_tests_count === $order->approved_tests_count
            && !$order->report()->exists()
        ) {
            $report = Report::create([
                'order_id' => $order->id,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            SendReportReadySms::dispatch($report);
        }
    }
}

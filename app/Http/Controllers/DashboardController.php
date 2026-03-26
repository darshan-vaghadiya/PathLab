<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderTest;
use App\Models\Report;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        // ── Core stats (exclude cancelled orders) ────────────────

        $todaysOrders = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $today)
            ->count();

        $pendingSamples = OrderTest::where('status', 'pending')
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->count();

        $pendingResults = OrderTest::where('status', 'sample_collected')
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->count();

        $pendingApprovals = OrderTest::where('status', 'completed')
            ->whereHas('order', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->count();

        $todaysRevenue = (float) Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $today)
            ->sum('net_amount');

        $monthlyRevenue = (float) Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $monthStart)
            ->sum('net_amount');

        $monthlyCollection = (float) Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $monthStart)
            ->sum('amount_paid');

        $totalOutstanding = (float) Order::where('status', '!=', 'cancelled')
            ->whereRaw('net_amount > amount_paid')
            ->selectRaw('SUM(net_amount - amount_paid) as total')
            ->value('total') ?? 0;

        $todaysReports = Report::whereDate('created_at', $today)->count();

        // ── Payment collection stats ─────────────────────────────

        $todayAmountPaid = (float) Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', $today)
            ->sum('amount_paid');

        // ── Overdue payments (balance > 0, older than 7 days, active) ──

        $overduePayments = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '<', now()->subDays(7)->toDateString())
            ->whereRaw('net_amount > amount_paid')
            ->count();

        // ── Recent orders (exclude cancelled) ────────────────────

        $recentOrders = Order::with(['patient', 'creator'])
            ->where('status', '!=', 'cancelled')
            ->withCount([
                'orderTests',
                'orderTests as approved_tests_count' => fn ($q) => $q->where('status', 'approved'),
                'orderTests as pending_tests_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) {
                if ($order->order_tests_count === 0) {
                    $order->test_status = 'no_tests';
                } elseif ($order->approved_tests_count === $order->order_tests_count) {
                    $order->test_status = 'completed';
                } elseif ($order->pending_tests_count === $order->order_tests_count) {
                    $order->test_status = 'pending';
                } else {
                    $order->test_status = 'in_progress';
                }
                return $order;
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'todayOrders' => $todaysOrders,
                'pendingSamples' => $pendingSamples,
                'pendingResults' => $pendingResults,
                'pendingApprovals' => $pendingApprovals,
                'todayRevenue' => $todaysRevenue,
                'monthlyRevenue' => $monthlyRevenue,
                'todaysReports' => $todaysReports,
                'todayAmountPaid' => $todayAmountPaid,
                'monthlyCollection' => $monthlyCollection,
                'totalOutstanding' => $totalOutstanding,
                'overduePayments' => $overduePayments,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }
}

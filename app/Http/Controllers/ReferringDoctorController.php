<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReferringDoctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReferringDoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $doctors = ReferringDoctor::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('clinic_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/ReferringDoctors', [
            'doctors' => $doctors,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'clinic_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
        ]);

        ReferringDoctor::create($validated);

        return redirect()->route('admin.referring-doctors.index')
            ->with('success', 'Referring doctor created successfully.');
    }

    public function update(Request $request, ReferringDoctor $referringDoctor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'clinic_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_value' => 'required|numeric|min:0',
        ]);

        $referringDoctor->update($validated);

        return redirect()->route('admin.referring-doctors.index')
            ->with('success', 'Referring doctor updated successfully.');
    }

    public function toggleStatus(ReferringDoctor $referringDoctor): RedirectResponse
    {
        $referringDoctor->update(['is_active' => !$referringDoctor->is_active]);

        return redirect()->route('admin.referring-doctors.index')
            ->with('success', 'Doctor status updated.');
    }

    public function destroy(ReferringDoctor $referringDoctor): RedirectResponse
    {
        $referringDoctor->delete();
        return redirect()->route('admin.referring-doctors.index')
            ->with('success', 'Referring doctor deleted successfully.');
    }

    public function commissionReport(Request $request): Response
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->toDateString();

        $doctors = ReferringDoctor::where('is_active', true)->get()->map(function ($doctor) use ($from, $to) {
            $orders = Order::where('referring_doctor_id', $doctor->id)
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->get();

            $totalOrderAmount = $orders->sum('net_amount');
            $commissionAmount = $doctor->commission_type === 'percentage'
                ? $totalOrderAmount * ($doctor->commission_value / 100)
                : $orders->count() * $doctor->commission_value;

            $doctor->orders_count = $orders->count();
            $doctor->total_order_amount = (float) $totalOrderAmount;
            $doctor->commission_amount = round($commissionAmount, 2);

            return $doctor;
        });

        return Inertia::render('Admin/CommissionReport', [
            'doctors' => $doctors,
            'filters' => ['from' => $from, 'to' => $to],
        ]);
    }
}

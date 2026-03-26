<?php

namespace App\Http\Controllers;

use App\Models\B2bClient;
use App\Models\B2bPackagePrice;
use App\Models\B2bTestPrice;
use App\Models\Test;
use App\Models\TestPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class B2bClientController extends Controller
{
    public function index(Request $request): Response
    {
        $clients = B2bClient::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/B2bClients', [
            'clients' => $clients,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        B2bClient::create($validated);

        return redirect()->route('admin.b2b-clients.index')
            ->with('success', 'B2B client created successfully.');
    }

    public function update(Request $request, B2bClient $b2bClient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|string|max:255',
        ]);

        $b2bClient->update($validated);

        return redirect()->route('admin.b2b-clients.index')
            ->with('success', 'B2B client updated successfully.');
    }

    public function managePricing(B2bClient $b2bClient): Response
    {
        $customPrices = $b2bClient->testPrices()
            ->pluck('price', 'test_id')
            ->toArray();

        $tests = Test::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(function ($test) use ($customPrices) {
                $test->customPrice = $customPrices[$test->id] ?? null;
                return $test;
            });

        $customPackagePrices = $b2bClient->packagePrices()
            ->pluck('price', 'test_package_id')
            ->toArray();

        $packages = TestPackage::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($package) use ($customPackagePrices) {
                $package->customPrice = $customPackagePrices[$package->id] ?? null;
                return $package;
            });

        return Inertia::render('Admin/B2bPricing', [
            'client' => $b2bClient,
            'tests' => $tests,
            'packages' => $packages,
        ]);
    }

    public function updatePricing(Request $request, B2bClient $b2bClient): RedirectResponse
    {
        $validated = $request->validate([
            'prices' => 'required|array',
            'prices.*.test_id' => 'required|exists:tests,id',
            'prices.*.price' => 'required|numeric|min:0',
            'package_prices' => 'nullable|array',
            'package_prices.*.package_id' => 'exists:test_packages,id',
            'package_prices.*.price' => 'nullable|numeric|min:0',
        ]);

        // Remove existing custom prices for this client
        $b2bClient->testPrices()->delete();

        // Insert new custom prices
        foreach ($validated['prices'] as $priceData) {
            B2bTestPrice::create([
                'b2b_client_id' => $b2bClient->id,
                'test_id' => $priceData['test_id'],
                'price' => $priceData['price'],
            ]);
        }

        // Handle package prices
        if (!empty($validated['package_prices'])) {
            foreach ($validated['package_prices'] as $pkgData) {
                if ($pkgData['price'] !== null && $pkgData['price'] !== '') {
                    B2bPackagePrice::updateOrCreate(
                        [
                            'b2b_client_id' => $b2bClient->id,
                            'test_package_id' => $pkgData['package_id'],
                        ],
                        [
                            'price' => $pkgData['price'],
                        ]
                    );
                } else {
                    B2bPackagePrice::where('b2b_client_id', $b2bClient->id)
                        ->where('test_package_id', $pkgData['package_id'])
                        ->delete();
                }
            }
        }

        return redirect()->route('admin.b2b-clients.index')
            ->with('success', 'B2B pricing updated successfully.');
    }

    public function destroy(B2bClient $b2bClient): RedirectResponse
    {
        $b2bClient->delete();
        return redirect()->route('admin.b2b-clients.index')
            ->with('success', 'B2B client deleted successfully.');
    }

    public function toggleStatus(B2bClient $b2bClient): RedirectResponse
    {
        $b2bClient->update(['is_active' => !$b2bClient->is_active]);

        return redirect()->route('admin.b2b-clients.index')
            ->with('success', 'Client status updated.');
    }
}

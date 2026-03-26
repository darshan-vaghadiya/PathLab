<?php

namespace App\Http\Controllers;

use App\Models\Test;
use App\Models\TestPackage;
use App\Models\TestPackageItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TestPackageController extends Controller
{
    public function index(): Response
    {
        $packages = TestPackage::with('tests')->get();
        $tests = Test::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Admin/Packages', [
            'packages' => $packages,
            'allTests' => $tests,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'test_ids' => 'required|array|min:1',
            'test_ids.*' => 'exists:tests,id',
        ]);

        DB::transaction(function () use ($validated) {
            $package = TestPackage::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
            ]);

            foreach ($validated['test_ids'] as $testId) {
                TestPackageItem::create([
                    'test_package_id' => $package->id,
                    'test_id' => $testId,
                ]);
            }
        });

        return redirect()->route('admin.packages.index')
            ->with('success', 'Test package created successfully.');
    }

    public function update(Request $request, TestPackage $testPackage): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'test_ids' => 'required|array|min:1',
            'test_ids.*' => 'exists:tests,id',
        ]);

        DB::transaction(function () use ($validated, $testPackage) {
            $testPackage->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'price' => $validated['price'],
            ]);

            // Sync test items
            $testPackage->items()->delete();

            foreach ($validated['test_ids'] as $testId) {
                TestPackageItem::create([
                    'test_package_id' => $testPackage->id,
                    'test_id' => $testId,
                ]);
            }
        });

        return redirect()->route('admin.packages.index')
            ->with('success', 'Test package updated successfully.');
    }

    public function destroy(TestPackage $testPackage): RedirectResponse
    {
        DB::transaction(function () use ($testPackage) {
            $testPackage->items()->delete();
            $testPackage->delete();
        });

        return redirect()->route('admin.packages.index')
            ->with('success', 'Test package deleted successfully.');
    }
}

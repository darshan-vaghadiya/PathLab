<?php

namespace App\Http\Controllers;

use App\Models\ParameterRange;
use App\Models\ResultTemplate;
use App\Models\Test;
use App\Models\TestCategory;
use App\Models\TestParameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TestManagementController extends Controller
{
    // ── Categories ────────────────────────────────────────

    public function categoriesIndex(): Response
    {
        $categories = TestCategory::withCount('tests')->orderBy('name')->get();

        return Inertia::render('Admin/TestCategories', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:test_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        TestCategory::create($validated);

        return redirect()->route('admin.testCategories.index')
            ->with('success', 'Test category created successfully.');
    }

    public function updateCategory(Request $request, TestCategory $testCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:test_categories,name,' . $testCategory->id,
            'description' => 'nullable|string|max:500',
        ]);

        $testCategory->update($validated);

        return redirect()->route('admin.testCategories.index')
            ->with('success', 'Test category updated successfully.');
    }

    public function deleteCategory(TestCategory $testCategory): RedirectResponse
    {
        if ($testCategory->tests()->exists()) {
            return redirect()->route('admin.testCategories.index')
                ->with('error', 'Cannot delete category that has tests assigned to it.');
        }

        $testCategory->delete();

        return redirect()->route('admin.testCategories.index')
            ->with('success', 'Test category deleted successfully.');
    }

    // ── Tests ────────────────────────────────────────

    public function index(Request $request): Response
    {
        $query = Test::with('category')
            ->withCount('parameters');

        if ($request->filled('category')) {
            $query->where('test_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%");
            });
        }

        $tests = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = TestCategory::orderBy('name')->get();

        return Inertia::render('Admin/Tests', [
            'tests' => $tests,
            'categories' => $categories,
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function create(): Response
    {
        $categories = TestCategory::orderBy('name')->get();

        return Inertia::render('Admin/TestCreate', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_category_id' => 'required|exists:test_categories,id',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'report_type' => 'required|string|in:parametric,text,mixed',
            'price' => 'required|numeric|min:0',
            'method' => 'nullable|string|max:255',
            'sample_type' => 'nullable|string|max:100',
            'parameters' => 'nullable|array',
            'parameters.*.name' => 'required|string|max:255',
            'parameters.*.field_type' => 'required|string|max:50',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.options' => 'nullable|array',
            'parameters.*.sort_order' => 'nullable|integer|min:0',
            'parameters.*.ranges' => 'nullable|array',
            'parameters.*.ranges.*.range_group' => 'nullable|string|max:50',
            'parameters.*.ranges.*.age_min' => 'nullable|integer|min:0',
            'parameters.*.ranges.*.age_max' => 'nullable|integer|min:0',
            'parameters.*.ranges.*.min_value' => 'nullable|numeric',
            'parameters.*.ranges.*.max_value' => 'nullable|numeric',
            'parameters.*.ranges.*.text_value' => 'nullable|string|max:255',
        ]);

        $test = DB::transaction(function () use ($validated) {
            $test = Test::create(collect($validated)->except('parameters')->toArray());

            if (!empty($validated['parameters'])) {
                foreach ($validated['parameters'] as $paramData) {
                    $ranges = $paramData['ranges'] ?? [];
                    unset($paramData['ranges']);

                    $parameter = $test->parameters()->create($paramData);

                    foreach ($ranges as $rangeData) {
                        $parameter->ranges()->create($rangeData);
                    }
                }
            }

            return $test;
        });

        return redirect()->route('admin.tests.edit', $test)
            ->with('success', 'Test created successfully.');
    }

    public function edit(Test $test): Response
    {
        $test->load(['category', 'parameters' => function ($q) {
            $q->orderBy('sort_order');
        }, 'parameters.ranges', 'parameters.templates']);

        $categories = TestCategory::orderBy('name')->get();
        $allTests = Test::where('id', '!=', $test->id)->withCount('parameters')->orderBy('name')->get();

        return Inertia::render('Admin/TestEdit', [
            'test' => $test,
            'categories' => $categories,
            'allTests' => $allTests,
        ]);
    }

    public function update(Request $request, Test $test): RedirectResponse
    {
        $validated = $request->validate([
            'test_category_id' => 'required|exists:test_categories,id',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'report_type' => 'required|string|in:parametric,text,mixed',
            'price' => 'required|numeric|min:0',
            'method' => 'nullable|string|max:255',
            'sample_type' => 'nullable|string|max:100',
        ]);

        $test->update($validated);

        return redirect()->route('admin.tests.edit', $test)
            ->with('success', 'Test updated successfully.');
    }

    public function destroy(Test $test): RedirectResponse
    {
        $test->delete();

        return redirect()->route('admin.tests.index')
            ->with('success', 'Test deleted successfully.');
    }

    public function toggleTestStatus(Test $test): RedirectResponse
    {
        $test->update(['is_active' => !$test->is_active]);

        return redirect()->back()
            ->with('success', 'Test status updated.');
    }

    // ── Copy Parameters ────────────────────────────────────────

    public function copyParameters(Request $request, Test $test): RedirectResponse
    {
        $validated = $request->validate([
            'source_test_id' => 'required|exists:tests,id',
        ]);

        $sourceTest = Test::with('parameters.ranges')->find($validated['source_test_id']);

        DB::transaction(function () use ($test, $sourceTest) {
            foreach ($sourceTest->parameters as $param) {
                $newParam = $test->parameters()->create([
                    'name' => $param->name,
                    'field_type' => $param->field_type,
                    'unit' => $param->unit,
                    'options' => $param->options,
                    'sort_order' => $param->sort_order,
                ]);
                foreach ($param->ranges as $range) {
                    $newParam->ranges()->create([
                        'range_group' => $range->range_group,
                        'age_min' => $range->age_min,
                        'age_max' => $range->age_max,
                        'min_value' => $range->min_value,
                        'max_value' => $range->max_value,
                        'text_value' => $range->text_value,
                    ]);
                }
            }
        });

        return redirect()->route('admin.tests.edit', $test)
            ->with('success', 'Parameters copied from ' . $sourceTest->name);
    }

    // ── Parameters ────────────────────────────────────────

    public function reorderParameter(Request $request, TestParameter $parameter): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $test = $parameter->test;
        $parameters = $test->parameters()->orderBy('sort_order')->get();
        $currentIndex = $parameters->search(fn($p) => $p->id === $parameter->id);

        if ($validated['direction'] === 'up' && $currentIndex > 0) {
            $swapWith = $parameters[$currentIndex - 1];
        } elseif ($validated['direction'] === 'down' && $currentIndex < $parameters->count() - 1) {
            $swapWith = $parameters[$currentIndex + 1];
        } else {
            return redirect()->back();
        }

        $tempOrder = $parameter->sort_order;
        $parameter->update(['sort_order' => $swapWith->sort_order]);
        $swapWith->update(['sort_order' => $tempOrder]);

        return redirect()->route('admin.tests.edit', $test)
            ->with('success', 'Parameter reordered.');
    }

    public function storeParameter(Request $request, Test $test): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'unit' => 'nullable|string|max:50',
            'options' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $test->parameters()->create($validated);

        return redirect()->route('admin.tests.edit', $test)
            ->with('success', 'Parameter added successfully.');
    }

    public function updateParameter(Request $request, TestParameter $parameter): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'unit' => 'nullable|string|max:50',
            'options' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $parameter->update($validated);

        return redirect()->route('admin.tests.edit', $parameter->test_id)
            ->with('success', 'Parameter updated successfully.');
    }

    public function deleteParameter(TestParameter $parameter): RedirectResponse
    {
        $testId = $parameter->test_id;
        $parameter->delete();

        return redirect()->route('admin.tests.edit', $testId)
            ->with('success', 'Parameter deleted successfully.');
    }

    // ── Ranges ────────────────────────────────────────

    public function storeRange(Request $request, TestParameter $parameter): RedirectResponse
    {
        $validated = $request->validate([
            'range_group' => 'nullable|string|max:50',
            'age_min' => 'nullable|integer|min:0',
            'age_max' => 'nullable|integer|min:0',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'text_value' => 'nullable|string|max:255',
        ]);

        $parameter->ranges()->create($validated);

        return redirect()->route('admin.tests.edit', $parameter->test_id)
            ->with('success', 'Range added successfully.');
    }

    public function updateRange(Request $request, ParameterRange $range): RedirectResponse
    {
        $validated = $request->validate([
            'range_group' => 'nullable|string|max:50',
            'age_min' => 'nullable|integer|min:0',
            'age_max' => 'nullable|integer|min:0',
            'min_value' => 'nullable|numeric',
            'max_value' => 'nullable|numeric',
            'text_value' => 'nullable|string|max:255',
        ]);

        $range->update($validated);

        return redirect()->route('admin.tests.edit', $range->parameter->test_id)
            ->with('success', 'Range updated successfully.');
    }

    public function deleteRange(ParameterRange $range): RedirectResponse
    {
        $testId = $range->parameter->test_id;
        $range->delete();

        return redirect()->route('admin.tests.edit', $testId)
            ->with('success', 'Range deleted successfully.');
    }

    // ── Templates ────────────────────────────────────────

    public function storeTemplate(Request $request, TestParameter $parameter): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $parameter->templates()->create($validated);

        return redirect()->route('admin.tests.edit', $parameter->test_id)
            ->with('success', 'Template added.');
    }

    public function deleteTemplate(ResultTemplate $template): RedirectResponse
    {
        $testId = $template->parameter->test_id;
        $template->delete();

        return redirect()->route('admin.tests.edit', $testId)
            ->with('success', 'Template deleted.');
    }
}

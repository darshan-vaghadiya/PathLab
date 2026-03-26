<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\TestManagementController;
use App\Http\Controllers\TestPackageController;
use App\Http\Controllers\ReferringDoctorController;
use App\Http\Controllers\B2bClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\LabSettingsController;
use App\Http\Controllers\PublicReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $labSettings = [
        'lab_name' => \App\Models\LabSetting::get('lab_name', 'PathLab'),
        'lab_phone' => \App\Models\LabSetting::get('lab_phone', ''),
        'lab_email' => \App\Models\LabSetting::get('lab_email', ''),
        'lab_address' => \App\Models\LabSetting::get('lab_address', ''),
        'lab_logo_url' => \App\Models\LabSetting::get('lab_logo')
            ? \Illuminate\Support\Facades\Storage::url(\App\Models\LabSetting::get('lab_logo'))
            : null,
    ];

    $testsByCategory = \App\Models\Test::where('is_active', true)
        ->with('category')
        ->orderBy('sort_order')
        ->get()
        ->groupBy(fn($t) => $t->category->name ?? 'Other')
        ->map(fn($tests) => $tests->map(fn($t) => [
            'name' => $t->name,
            'short_name' => $t->short_name,
            'price' => $t->price,
            'sample_type' => $t->sample_type,
        ]));

    $packages = \App\Models\TestPackage::where('is_active', true)
        ->with('tests')
        ->get()
        ->map(fn($p) => [
            'name' => $p->name,
            'description' => $p->description,
            'price' => $p->price,
            'test_count' => $p->tests->count(),
        ]);

    return \Inertia\Inertia::render('Landing', [
        'labSettings' => $labSettings,
        'testsByCategory' => $testsByCategory,
        'packages' => $packages,
    ]);
})->name('landing');

// All authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (always accessible)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Patients
    Route::middleware('permission:manage_patients')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::get('/patients-search', [PatientController::class, 'search'])->name('patients.search');
    });

    // Orders - create/manage
    Route::middleware('permission:create_orders')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
        Route::patch('/orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('orders.updatePayment');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/orders/{order}/receipt', [OrderController::class, 'printReceipt'])->name('orders.receipt');
    });

    // Orders - view
    Route::middleware('permission:view_orders')->group(function () {
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    // Lab - sample collection
    Route::middleware('permission:collect_samples')->prefix('lab')->name('lab.')->group(function () {
        Route::get('/pending-samples', [LabController::class, 'pendingSamples'])->name('pendingSamples');
        Route::get('/collect-samples/{order}', [LabController::class, 'collectSamplesForOrder'])->name('collectSamples');
        Route::post('/collect-sample/{orderTest}', [LabController::class, 'collectSample'])->name('collectSample');
        Route::post('/batch-collect/{order}', [LabController::class, 'batchCollect'])->name('batchCollect');
    });

    // Lab - result entry
    Route::middleware('permission:enter_results')->prefix('lab')->name('lab.')->group(function () {
        Route::get('/pending-results', [LabController::class, 'pendingResults'])->name('pendingResults');
        Route::get('/enter-results/{order}', [LabController::class, 'enterResultsForOrder'])->name('enterResults');
        Route::post('/enter-result/{orderTest}', [LabController::class, 'enterResult'])->name('enterResult');
    });

    // Doctor
    Route::middleware('permission:approve_reports')->prefix('doctor')->name('doctor.')->group(function () {
        Route::get('/pending-approvals', [DoctorController::class, 'pendingApprovals'])->name('pendingApprovals');
        Route::get('/review/{order}', [DoctorController::class, 'reviewOrder'])->name('reviewOrder');
        Route::post('/approve-test/{orderTest}', [DoctorController::class, 'approveTest'])->name('approveTest');
        Route::post('/approve-all/{order}', [DoctorController::class, 'approveAllTests'])->name('approveAll');
        Route::post('/reject-test/{orderTest}', [DoctorController::class, 'rejectTest'])->name('rejectTest');
        Route::post('/reject-all/{order}', [DoctorController::class, 'rejectAllTests'])->name('rejectAll');
    });

    // Reports
    Route::middleware('permission:view_reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::post('/reports/{report}/send-sms', [ReportController::class, 'sendSms'])->name('reports.sendSms');
    });
    Route::middleware('permission:download_reports')->group(function () {
        Route::get('/reports/{report}/pdf', [ReportController::class, 'generatePdf'])->name('reports.pdf');
        Route::get('/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {

        // Test Categories
        Route::middleware('permission:manage_test_categories')->group(function () {
            Route::get('/test-categories', [TestManagementController::class, 'categoriesIndex'])->name('testCategories.index');
            Route::post('/test-categories', [TestManagementController::class, 'storeCategory'])->name('testCategories.store');
            Route::put('/test-categories/{testCategory}', [TestManagementController::class, 'updateCategory'])->name('testCategories.update');
            Route::delete('/test-categories/{testCategory}', [TestManagementController::class, 'deleteCategory'])->name('testCategories.destroy');
        });

        // Tests
        Route::middleware('permission:manage_tests')->group(function () {
            Route::get('/tests', [TestManagementController::class, 'index'])->name('tests.index');
            Route::get('/tests/create', [TestManagementController::class, 'create'])->name('tests.create');
            Route::post('/tests', [TestManagementController::class, 'store'])->name('tests.store');
            Route::get('/tests/{test}/edit', [TestManagementController::class, 'edit'])->name('tests.edit');
            Route::put('/tests/{test}', [TestManagementController::class, 'update'])->name('tests.update');
            Route::delete('/tests/{test}', [TestManagementController::class, 'destroy'])->name('tests.destroy');
            Route::patch('/tests/{test}/toggle', [TestManagementController::class, 'toggleTestStatus'])->name('tests.toggleStatus');
            Route::post('/tests/{test}/copy-parameters', [TestManagementController::class, 'copyParameters'])->name('tests.copyParameters');
            Route::post('/tests/{test}/parameters', [TestManagementController::class, 'storeParameter'])->name('tests.storeParameter');
            Route::post('/parameters/{parameter}/reorder', [TestManagementController::class, 'reorderParameter'])->name('tests.reorderParameter');
            Route::put('/parameters/{parameter}', [TestManagementController::class, 'updateParameter'])->name('tests.updateParameter');
            Route::delete('/parameters/{parameter}', [TestManagementController::class, 'deleteParameter'])->name('tests.deleteParameter');
            Route::post('/parameters/{parameter}/ranges', [TestManagementController::class, 'storeRange'])->name('tests.storeRange');
            Route::put('/ranges/{range}', [TestManagementController::class, 'updateRange'])->name('tests.updateRange');
            Route::delete('/ranges/{range}', [TestManagementController::class, 'deleteRange'])->name('tests.deleteRange');
            Route::post('/parameters/{parameter}/templates', [TestManagementController::class, 'storeTemplate'])->name('tests.storeTemplate');
            Route::delete('/templates/{template}', [TestManagementController::class, 'deleteTemplate'])->name('tests.deleteTemplate');
        });

        // Packages
        Route::middleware('permission:manage_packages')->group(function () {
            Route::resource('packages', TestPackageController::class)->except(['show', 'edit']);
        });

        // Referring Doctors
        Route::middleware('permission:manage_referring_doctors')->group(function () {
            Route::resource('referring-doctors', ReferringDoctorController::class)->except(['show', 'edit', 'create']);
            Route::patch('/referring-doctors/{referring_doctor}/toggle', [ReferringDoctorController::class, 'toggleStatus'])->name('referring-doctors.toggleStatus');
        });

        // Commission Report
        Route::middleware('permission:view_commission_report')->group(function () {
            Route::get('/commission-report', [ReferringDoctorController::class, 'commissionReport'])->name('referring-doctors.commissionReport');
        });

        // B2B Clients
        Route::middleware('permission:manage_b2b_clients')->group(function () {
            Route::resource('b2b-clients', B2bClientController::class)->except(['show', 'edit', 'create']);
            Route::get('/b2b-clients/{b2bClient}/pricing', [B2bClientController::class, 'managePricing'])->name('b2b-clients.pricing');
            Route::put('/b2b-clients/{b2bClient}/pricing', [B2bClientController::class, 'updatePricing'])->name('b2b-clients.updatePricing');
            Route::patch('/b2b-clients/{b2bClient}/toggle', [B2bClientController::class, 'toggleStatus'])->name('b2b-clients.toggleStatus');
        });

        // Users
        Route::middleware('permission:manage_users')->group(function () {
            Route::resource('users', UserController::class)->except(['show', 'edit', 'create']);
            Route::patch('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
        });

        // Permissions
        Route::middleware('permission:manage_permissions')->group(function () {
            Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::put('/permissions', [PermissionController::class, 'update'])->name('permissions.update');
        });

        // Lab Settings
        Route::middleware('permission:manage_permissions')->group(function () {
            Route::get('/settings', [LabSettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [LabSettingsController::class, 'update'])->name('settings.update');
            Route::get('/settings/preview-pdf', [LabSettingsController::class, 'previewPdf'])->name('settings.previewPdf');
        });
    });
});

// Public report routes (no auth required)
Route::get('/report/lookup', [PublicReportController::class, 'lookup'])->name('reports.lookup');
Route::get('/report/{token}', [PublicReportController::class, 'show'])->name('reports.public');
Route::get('/report/{token}/print', [PublicReportController::class, 'print'])->name('reports.public.print');
Route::get('/report/{token}/download', [PublicReportController::class, 'download'])->name('reports.public.download');

require __DIR__.'/auth.php';

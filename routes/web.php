<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\CompanySwitchController;
use Illuminate\Support\Facades\Route;

// ── Public ─────────────────────────────────────
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// ── Locale Switch (accessible without auth) ────
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

// ── Authenticated Routes ───────────────────────
Route::middleware(['auth', 'verified', 'company'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Company Switcher
    Route::post('/company/switch/{companyId}', [CompanySwitchController::class, 'switch'])
        ->name('company.switch');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Master Data (All authenticated users can access based on permissions) ────
    Route::resource('companies', \App\Http\Controllers\CompanyController::class);
    Route::resource('branches', \App\Http\Controllers\BranchController::class);
    Route::resource('projects', \App\Http\Controllers\ProjectController::class);
    Route::get('clients/export', [\App\Http\Controllers\ClientController::class, 'export'])->name('clients.export');
    Route::get('clients/template', [\App\Http\Controllers\ClientController::class, 'downloadTemplate'])->name('clients.template');
    Route::post('clients/import', [\App\Http\Controllers\ClientController::class, 'import'])->name('clients.import');
    Route::resource('clients', \App\Http\Controllers\ClientController::class);

    Route::get('vendors/export', [\App\Http\Controllers\VendorController::class, 'export'])->name('vendors.export');
    Route::get('vendors/template', [\App\Http\Controllers\VendorController::class, 'downloadTemplate'])->name('vendors.template');
    Route::post('vendors/import', [\App\Http\Controllers\VendorController::class, 'import'])->name('vendors.import');
    Route::resource('vendors', \App\Http\Controllers\VendorController::class);
    Route::resource('coa', \App\Http\Controllers\ChartOfAccountController::class)->parameters([
        'coa' => 'coa'
    ]);

    // ── Accounting Core ────────────────────────────
    Route::resource('journals', \App\Http\Controllers\JournalEntryController::class);
    Route::post('journals/{journal}/submit', [\App\Http\Controllers\JournalEntryController::class, 'submit'])->name('journals.submit');
    Route::post('journals/{journal}/review', [\App\Http\Controllers\JournalEntryController::class, 'review'])->name('journals.review');
    Route::post('journals/{journal}/approve', [\App\Http\Controllers\JournalEntryController::class, 'approve'])->name('journals.approve');
    Route::post('journals/{journal}/reject', [\App\Http\Controllers\JournalEntryController::class, 'reject'])->name('journals.reject');

    // ── Taxation Core ──────────────────────────────
    Route::get('taxes/draft-faktur', [\App\Http\Controllers\DraftFakturController::class, 'index'])->name('taxes.draft_faktur.index');
    Route::post('taxes/draft-faktur/upload', [\App\Http\Controllers\DraftFakturController::class, 'upload'])->name('taxes.draft_faktur.upload');
    Route::post('taxes/draft-faktur/{id}/verify', [\App\Http\Controllers\DraftFakturController::class, 'verify'])->name('taxes.draft_faktur.verify');
    Route::delete('taxes/draft-faktur/{id}', [\App\Http\Controllers\DraftFakturController::class, 'destroy'])->name('taxes.draft_faktur.destroy');

    Route::get('employees/export', [\App\Http\Controllers\EmployeeController::class, 'export'])->name('employees.export');
    Route::get('employees/template', [\App\Http\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.template');
    Route::post('employees/import', [\App\Http\Controllers\EmployeeController::class, 'import'])->name('employees.import');
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    
    Route::get('taxes/pph21', [\App\Http\Controllers\Pph21Controller::class, 'index'])->name('taxes.pph21.index');
    Route::post('taxes/pph21/calculate', [\App\Http\Controllers\Pph21Controller::class, 'calculate'])->name('taxes.pph21.calculate');
    Route::post('taxes/pph21/journal', [\App\Http\Controllers\Pph21Controller::class, 'generateJournal'])->name('taxes.pph21.journal');

    Route::get('taxes/ppn', [\App\Http\Controllers\TaxTransactionController::class, 'indexPpn'])->name('taxes.ppn.index');
    Route::get('taxes/ppn/create', [\App\Http\Controllers\TaxTransactionController::class, 'createPpn'])->name('taxes.ppn.create');
    Route::get('taxes/unifikasi', [\App\Http\Controllers\TaxTransactionController::class, 'indexUnifikasi'])->name('taxes.unifikasi.index');
    Route::get('taxes/unifikasi/create', [\App\Http\Controllers\TaxTransactionController::class, 'createUnifikasi'])->name('taxes.unifikasi.create');
    Route::post('taxes/transactions', [\App\Http\Controllers\TaxTransactionController::class, 'store'])->name('taxes.transactions.store');
    Route::post('taxes/transactions/{transaction}/journal', [\App\Http\Controllers\TaxTransactionController::class, 'generateJournal'])->name('taxes.transactions.journal');

    // ── Reports ────────────────────────────────────
    Route::get('reports/general-ledger', [\App\Http\Controllers\ReportController::class, 'generalLedger'])->name('reports.general_ledger');
    Route::get('reports/trial-balance', [\App\Http\Controllers\ReportController::class, 'trialBalance'])->name('reports.trial_balance');
    Route::get('reports/financial-statements', [\App\Http\Controllers\ReportController::class, 'financialStatements'])->name('reports.financial_statements');

    // ── Tax Control ────────────────────────────────
    Route::resource('tax-audits', \App\Http\Controllers\TaxAuditController::class);

    // ── Manager Only Routes ────────────────────
    Route::middleware('role:manager')->group(function () {
        // User management
        Route::resource('users', \App\Http\Controllers\UserController::class);
    });

    // ── Manager + Supervisor Routes ────────────
    Route::middleware('role:manager,supervisor')->group(function () {
        // Approval routes — will be added later
    });
});

require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Pph21Monthly;
use App\Models\TerRate;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Pph21Controller extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $employees = Employee::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Load existing records for the period
        $records = Pph21Monthly::whereIn('employee_id', $employees->pluck('id'))
            ->where('month', $month)
            ->where('year', $year)
            ->with('terRate')
            ->get()
            ->keyBy('employee_id');

        return view('taxes.pph21.index', compact('employees', 'records', 'month', 'year'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'employees' => 'required|array',
            'employees.*.id' => 'required|exists:employees,id',
            'employees.*.gross_income' => 'required|numeric|min:0',
        ]);

        $company = $request->user()->currentCompany;

        DB::transaction(function () use ($validated, $company) {
            foreach ($validated['employees'] as $empData) {
                if ($empData['gross_income'] <= 0) continue;

                $employee = Employee::find($empData['id']);
                
                // Security check
                if ($employee->company_id !== $company->id) continue;

                $gross = $empData['gross_income'];
                $category = $employee->ter_category;

                // Find applicable TER rate
                $rate = TerRate::where('category', $category)
                    ->where('min_bruto', '<=', $gross)
                    ->where(function($q) use ($gross) {
                        $q->where('max_bruto', '>=', $gross)
                          ->orWhereNull('max_bruto');
                    })
                    ->first();

                $taxAmount = 0;
                $terRateId = null;

                if ($rate) {
                    $taxAmount = $gross * ($rate->rate_percentage / 100);
                    $terRateId = $rate->id;
                }

                Pph21Monthly::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'month' => $validated['month'],
                        'year' => $validated['year'],
                    ],
                    [
                        'gross_income' => $gross,
                        'ter_rate_id' => $terRateId,
                        'tax_amount' => $taxAmount,
                        'status' => 'draft',
                    ]
                );
            }
        });

        return back()->with('success', 'PPh 21 calculated successfully.');
    }

    public function generateJournal(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $company = $request->user()->currentCompany;

        // Get all draft records for the period
        $records = Pph21Monthly::whereHas('employee', function($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->where('status', 'draft')
            ->where('tax_amount', '>', 0)
            ->get();

        if ($records->isEmpty()) {
            return back()->with('error', 'No draft PPh 21 records found to journalize.');
        }

        $totalTax = $records->sum('tax_amount');
        
        // Find default accounts (in a real app, these should be mapped in settings)
        // Here we just pick the first expense account and first tax liability account
        $expenseAccount = ChartOfAccount::where('company_id', $company->id)->where('account_type', 'expense')->first();
        $taxLiabilityAccount = ChartOfAccount::where('company_id', $company->id)->where('account_type', 'liability')->first();

        if (!$expenseAccount || !$taxLiabilityAccount) {
            return back()->with('error', 'Please set up Chart of Accounts first.');
        }

        DB::transaction(function () use ($records, $company, $validated, $totalTax, $expenseAccount, $taxLiabilityAccount) {
            
            // Auto-generate voucher number
            $prefix = 'JV-' . now()->format('Ym') . '-';
            $lastJournal = JournalEntry::where('company_id', $company->id)
                ->where('voucher_number', 'like', $prefix . '%')
                ->orderBy('voucher_number', 'desc')
                ->first();
                
            $nextNumber = $lastJournal ? ((int) substr($lastJournal->voucher_number, -4)) + 1 : 1;
            $voucherNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $journal = JournalEntry::create([
                'company_id' => $company->id,
                'voucher_number' => $voucherNumber,
                'date' => \Carbon\Carbon::createFromDate($validated['year'], $validated['month'], 1)->endOfMonth(),
                'reference' => 'PPH21-' . $validated['year'] . str_pad($validated['month'], 2, '0', STR_PAD_LEFT),
                'description' => 'Accrual PPh 21 Masa ' . $validated['month'] . '/' . $validated['year'],
                'status' => 'draft',
                'source_module' => 'tax_pph21',
                'created_by' => auth()->id(),
            ]);

            // Assuming Salary/Wages Expense is bearing the tax or it's a tax expense.
            // Dr. Expense
            $journal->lines()->create([
                'chart_of_account_id' => $expenseAccount->id,
                'description' => 'PPh 21 Expense',
                'debit' => $totalTax,
                'credit' => 0,
            ]);

            // Cr. PPh 21 Payable
            $journal->lines()->create([
                'chart_of_account_id' => $taxLiabilityAccount->id,
                'description' => 'Hutang PPh 21',
                'debit' => 0,
                'credit' => $totalTax,
            ]);

            // Update records
            foreach ($records as $record) {
                $record->update([
                    'status' => 'approved',
                    'journal_entry_id' => $journal->id
                ]);
            }
        });

        return back()->with('success', 'Journal generated successfully.');
    }
}

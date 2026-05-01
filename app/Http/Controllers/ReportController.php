<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function generalLedger(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $accountId = $request->input('account_id');
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $lines = collect();
        $openingBalance = 0;
        $selectedAccount = null;

        if ($accountId) {
            $selectedAccount = ChartOfAccount::find($accountId);
            
            if ($selectedAccount && $selectedAccount->company_id === $company->id) {
                // Calculate opening balance before start date
                $openingLines = JournalLine::where('chart_of_account_id', $accountId)
                    ->whereHas('journalEntry', function($q) use ($startDate, $company) {
                        $q->where('company_id', $company->id)
                          ->where('status', 'approved')
                          ->where('date', '<', $startDate);
                    })->get();

                $openingDebit = $openingLines->sum('debit');
                $openingCredit = $openingLines->sum('credit');
                
                if ($selectedAccount->normal_balance === 'debit') {
                    $openingBalance = $openingDebit - $openingCredit;
                } else {
                    $openingBalance = $openingCredit - $openingDebit;
                }

                // Get transactions within range
                $lines = JournalLine::where('chart_of_account_id', $accountId)
                    ->whereHas('journalEntry', function($q) use ($startDate, $endDate, $company) {
                        $q->where('company_id', $company->id)
                          ->where('status', 'approved')
                          ->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->with('journalEntry')
                    ->get()
                    ->sortBy(function($line) {
                        return $line->journalEntry->date;
                    });
            }
        }

        return view('reports.general_ledger', compact(
            'accounts', 'lines', 'openingBalance', 'selectedAccount', 'startDate', 'endDate'
        ));
    }

    public function trialBalance(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $trialBalance = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // DB Aggregation
        $balances = \Illuminate\Support\Facades\DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->selectRaw('chart_of_account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'approved')
            ->where('journal_entries.date', '<=', $endDate)
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        foreach ($accounts as $account) {
            $agg = $balances->get($account->id);
            $debit = $agg ? $agg->total_debit : 0;
            $credit = $agg ? $agg->total_credit : 0;
            
            $balance = 0;
            if ($account->normal_balance === 'debit') {
                $balance = $debit - $credit;
                if ($balance > 0) {
                    $totalDebit += $balance;
                } else {
                    $totalCredit += abs($balance);
                }
            } else {
                $balance = $credit - $debit;
                if ($balance > 0) {
                    $totalCredit += $balance;
                } else {
                    $totalDebit += abs($balance);
                }
            }

            if ($debit > 0 || $credit > 0 || $balance != 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'debit' => $account->normal_balance === 'debit' ? ($balance > 0 ? $balance : 0) : ($balance < 0 ? abs($balance) : 0),
                    'credit' => $account->normal_balance === 'credit' ? ($balance > 0 ? $balance : 0) : ($balance < 0 ? abs($balance) : 0),
                ];
            }
        }

        return view('reports.trial_balance', compact('trialBalance', 'totalDebit', 'totalCredit', 'startDate', 'endDate'));
    }

    public function financialStatements(Request $request)
    {
        $company = $request->user()->currentCompany;
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->where('is_header', false)
            ->orderBy('account_code')
            ->get();

        $revenueAccounts = [];
        $expenseAccounts = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        $assetAccounts = [];
        $liabilityAccounts = [];
        $equityAccounts = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        $totalRetainedEarnings = 0;

        // DB Aggregation for P&L (Period)
        $plBalances = \Illuminate\Support\Facades\DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->selectRaw('chart_of_account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'approved')
            ->whereBetween('journal_entries.date', [$startDate, $endDate])
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        // DB Aggregation for Balance Sheet (As of End Date)
        $bsBalances = \Illuminate\Support\Facades\DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->selectRaw('chart_of_account_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'approved')
            ->where('journal_entries.date', '<=', $endDate)
            ->groupBy('chart_of_account_id')
            ->get()
            ->keyBy('chart_of_account_id');

        foreach ($accounts as $account) {
            $plAgg = $plBalances->get($account->id);
            $plDebit = $plAgg ? $plAgg->total_debit : 0;
            $plCredit = $plAgg ? $plAgg->total_credit : 0;
            $plBalance = ($account->normal_balance === 'debit') ? ($plDebit - $plCredit) : ($plCredit - $plDebit);

            $bsAgg = $bsBalances->get($account->id);
            $bsDebit = $bsAgg ? $bsAgg->total_debit : 0;
            $bsCredit = $bsAgg ? $bsAgg->total_credit : 0;
            $bsBalance = ($account->normal_balance === 'debit') ? ($bsDebit - $bsCredit) : ($bsCredit - $bsDebit);

            if ($account->account_type === 'revenue') {
                if ($plBalance != 0) {
                    $revenueAccounts[] = ['account' => $account, 'balance' => $plBalance];
                    $totalRevenue += $plBalance;
                }
                $totalRetainedEarnings += $bsBalance;
            } elseif ($account->account_type === 'expense') {
                if ($plBalance != 0) {
                    $expenseAccounts[] = ['account' => $account, 'balance' => $plBalance];
                    $totalExpense += $plBalance;
                }
                $totalRetainedEarnings -= $bsBalance;
            } elseif ($account->account_type === 'asset') {
                if ($bsBalance != 0) {
                    $assetAccounts[] = ['account' => $account, 'balance' => $bsBalance];
                    $totalAssets += $bsBalance;
                }
            } elseif ($account->account_type === 'liability') {
                if ($bsBalance != 0) {
                    $liabilityAccounts[] = ['account' => $account, 'balance' => $bsBalance];
                    $totalLiabilities += $bsBalance;
                }
            } elseif ($account->account_type === 'equity') {
                if ($bsBalance != 0) {
                    $equityAccounts[] = ['account' => $account, 'balance' => $bsBalance];
                    $totalEquity += $bsBalance;
                }
            }
        }

        $netIncome = $totalRevenue - $totalExpense;
        $totalEquityIncludingRE = $totalEquity + $totalRetainedEarnings;

        return view('reports.financial_statements', compact(
            'startDate', 'endDate',
            'revenueAccounts', 'expenseAccounts', 'totalRevenue', 'totalExpense', 'netIncome',
            'assetAccounts', 'liabilityAccounts', 'equityAccounts', 
            'totalAssets', 'totalLiabilities', 'totalEquity', 'totalRetainedEarnings', 'totalEquityIncludingRE'
        ));
    }
}

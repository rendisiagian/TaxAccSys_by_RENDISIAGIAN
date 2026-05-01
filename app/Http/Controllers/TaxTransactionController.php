<?php

namespace App\Http\Controllers;

use App\Models\TaxTransaction;
use App\Models\Branch;
use App\Models\Project;
use App\Models\JournalEntry;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxTransactionController extends Controller
{
    // Using this controller for both PPN and Unifikasi to keep it DRY, 
    // but in real world they might be separated.
    
    public function indexPpn(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $transactions = TaxTransaction::where('company_id', $company->id)
            ->whereIn('tax_type', ['ppn_in', 'ppn_out'])
            ->with(['branch', 'project'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        return view('taxes.ppn.index', compact('transactions'));
    }

    public function createPpn(Request $request)
    {
        $company = $request->user()->currentCompany;
        $branches = \App\Models\Branch::where('company_id', $company->id)->get();
        $projects = \App\Models\Project::where('company_id', $company->id)->get();
        $clients = \App\Models\Client::where('company_id', $company->id)->get();
        $vendors = \App\Models\Vendor::where('company_id', $company->id)->get();

        return view('taxes.ppn.form', [
            'transaction' => new TaxTransaction(['tax_type' => 'ppn_out', 'transaction_date' => now()]),
            'branches' => $branches,
            'projects' => $projects,
            'clients' => $clients,
            'vendors' => $vendors,
        ]);
    }

    public function indexUnifikasi(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $transactions = TaxTransaction::where('company_id', $company->id)
            ->whereIn('tax_type', ['pph_22', 'pph_23', 'pph_4_2'])
            ->with(['branch', 'project'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        return view('taxes.unifikasi.index', compact('transactions'));
    }

    public function createUnifikasi(Request $request)
    {
        $company = $request->user()->currentCompany;
        $branches = \App\Models\Branch::where('company_id', $company->id)->get();
        $projects = \App\Models\Project::where('company_id', $company->id)->get();
        $clients = \App\Models\Client::where('company_id', $company->id)->get();
        $vendors = \App\Models\Vendor::where('company_id', $company->id)->get();

        return view('taxes.unifikasi.form', [
            'transaction' => new TaxTransaction(['tax_type' => 'pph_23', 'transaction_date' => now()]),
            'branches' => $branches,
            'projects' => $projects,
            'clients' => $clients,
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'tax_type' => 'required|in:ppn_in,ppn_out,pph_22,pph_23,pph_4_2',
            'faktur_code' => 'nullable|string|max:3',
            'transaction_date' => 'required|date',
            'document_number' => 'required|string|max:255',
            'counterpart_name' => 'required|string|max:255',
            'counterpart_tin' => 'required|string|max:255',
            'transaction_value' => 'nullable|numeric|min:0',
            'tax_base' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $validated['company_id'] = $company->id;
        $validated['status'] = 'draft';

        // Ensure transaction_value is populated even for unifikasi or normal where it might equal tax_base
        if (empty($validated['transaction_value'])) {
            $validated['transaction_value'] = $validated['tax_base'];
        }

        $trx = TaxTransaction::create($validated);

        if (in_array($trx->tax_type, ['ppn_in', 'ppn_out'])) {
            return redirect()->route('taxes.ppn.index')->with('success', 'PPN Transaction saved.');
        } else {
            return redirect()->route('taxes.unifikasi.index')->with('success', 'Unifikasi Transaction saved.');
        }
    }

    public function generateJournal(Request $request, TaxTransaction $transaction)
    {
        $company = $request->user()->currentCompany;
        abort_if($transaction->company_id !== $company->id, 403);
        
        if ($transaction->journal_entry_id) {
            return back()->with('error', 'Journal already generated.');
        }

        // Logic for PPN Out Example: Dr. Account Receivable, Cr. Revenue, Cr. PPN Out
        // Simplified for prototype: finding specific accounts by name or type
        
        $assetAcc = ChartOfAccount::where('company_id', $company->id)->where('account_type', 'asset')->first();
        $revenueAcc = ChartOfAccount::where('company_id', $company->id)->where('account_type', 'revenue')->first();
        $liabilityAcc = ChartOfAccount::where('company_id', $company->id)->where('account_type', 'liability')->first();

        if (!$assetAcc || !$revenueAcc || !$liabilityAcc) {
            return back()->with('error', 'Please ensure Asset, Revenue, and Liability accounts exist.');
        }

        DB::transaction(function () use ($transaction, $company, $assetAcc, $revenueAcc, $liabilityAcc) {
            $prefix = 'JV-' . now()->format('Ym') . '-';
            $lastJournal = JournalEntry::where('company_id', $company->id)
                ->where('voucher_number', 'like', $prefix . '%')
                ->orderBy('voucher_number', 'desc')
                ->first();
                
            $nextNumber = $lastJournal ? ((int) substr($lastJournal->voucher_number, -4)) + 1 : 1;
            $voucherNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $journal = JournalEntry::create([
                'company_id' => $company->id,
                'branch_id' => $transaction->branch_id,
                'project_id' => $transaction->project_id,
                'voucher_number' => $voucherNumber,
                'date' => $transaction->transaction_date,
                'reference' => $transaction->document_number,
                'description' => 'Auto-Journal from ' . strtoupper(str_replace('_', ' ', $transaction->tax_type)) . ' - ' . $transaction->counterpart_name,
                'status' => 'draft',
                'source_module' => 'tax_' . str_replace('_', '', $transaction->tax_type),
                'created_by' => auth()->id(),
            ]);

            if ($transaction->tax_type === 'ppn_out') {
                $nilaiJual = $transaction->transaction_value;
                $dpp = $transaction->tax_base;
                $ppn = $transaction->tax_amount;

                if ($transaction->faktur_code === '030') {
                    // WAPU (030): AR is only Nilai Jual. PPN Out is recorded but then offset by PPN WAPU.
                    // Simplified: Dr. AR (Nilai Jual), Dr. PPN Dipungut WAPU (PPN), Cr. Revenue (Nilai Jual), Cr. PPN Out (PPN)
                    $journal->lines()->create(['chart_of_account_id' => $assetAcc->id, 'description' => 'Piutang Usaha', 'debit' => $nilaiJual, 'credit' => 0]);
                    $journal->lines()->create(['chart_of_account_id' => $assetAcc->id, 'description' => 'PPN Dipungut WAPU', 'debit' => $ppn, 'credit' => 0]);
                    $journal->lines()->create(['chart_of_account_id' => $revenueAcc->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => $nilaiJual]);
                    $journal->lines()->create(['chart_of_account_id' => $liabilityAcc->id, 'description' => 'PPN Keluaran', 'debit' => 0, 'credit' => $ppn]);
                } else {
                    // Normal / Nilai Lain: AR = Nilai Jual + PPN
                    $journal->lines()->create(['chart_of_account_id' => $assetAcc->id, 'description' => 'Piutang Usaha', 'debit' => $nilaiJual + $ppn, 'credit' => 0]);
                    $journal->lines()->create(['chart_of_account_id' => $revenueAcc->id, 'description' => 'Pendapatan', 'debit' => 0, 'credit' => $nilaiJual]);
                    $journal->lines()->create(['chart_of_account_id' => $liabilityAcc->id, 'description' => 'PPN Keluaran', 'debit' => 0, 'credit' => $ppn]);
                }
            } else {
                // Generic fallback for prototype
                $journal->lines()->create(['chart_of_account_id' => $assetAcc->id, 'description' => 'Debit Account', 'debit' => $transaction->tax_amount, 'credit' => 0]);
                $journal->lines()->create(['chart_of_account_id' => $liabilityAcc->id, 'description' => 'Credit Account', 'debit' => 0, 'credit' => $transaction->tax_amount]);
            }

            $transaction->update([
                'status' => 'approved',
                'journal_entry_id' => $journal->id
            ]);
        });

        return back()->with('success', 'Journal Generated Successfully.');
    }
}

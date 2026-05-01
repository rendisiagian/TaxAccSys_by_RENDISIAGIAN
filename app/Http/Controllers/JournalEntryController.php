<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Branch;
use App\Models\Project;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    private ApprovalService $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $journals = JournalEntry::where('company_id', $company->id)
            ->with(['branch', 'project'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('journals.index', compact('journals'));
    }

    public function create(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        // Auto-generate voucher number (JV-YYYYMM-XXXX)
        $prefix = 'JV-' . now()->format('Ym') . '-';
        $lastJournal = JournalEntry::where('company_id', $company->id)
            ->where('voucher_number', 'like', $prefix . '%')
            ->orderBy('voucher_number', 'desc')
            ->first();
            
        $nextNumber = 1;
        if ($lastJournal) {
            $lastSequence = (int) substr($lastJournal->voucher_number, -4);
            $nextNumber = $lastSequence + 1;
        }
        $voucherNumber = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('journals.form', [
            'journal' => new JournalEntry(['voucher_number' => $voucherNumber, 'date' => now()]),
            'branches' => Branch::where('company_id', $company->id)->get(),
            'projects' => Project::where('company_id', $company->id)->get(),
            'accounts' => ChartOfAccount::where('company_id', $company->id)->where('is_header', false)->orderBy('account_code')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'voucher_number' => 'required|string|unique:journal_entries,voucher_number,NULL,id,company_id,' . $company->id,
            'date'           => 'required|date',
            'reference'      => 'nullable|string',
            'description'    => 'required|string',
            'branch_id'      => 'nullable|exists:branches,id',
            'project_id'     => 'nullable|exists:projects,id',
            'lines'          => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit'  => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        // Validate Balance
        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Journal is not balanced. Total Debit must equal Total Credit.');
        }

        DB::transaction(function () use ($validated, $company, $request) {
            $journal = JournalEntry::create([
                'company_id'     => $company->id,
                'branch_id'      => $validated['branch_id'],
                'project_id'     => $validated['project_id'],
                'voucher_number' => $validated['voucher_number'],
                'date'           => $validated['date'],
                'reference'      => $validated['reference'],
                'description'    => $validated['description'],
                'created_by'     => auth()->id(),
                'status'         => 'draft',
            ]);

            foreach ($validated['lines'] as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    $journal->lines()->create([
                        'chart_of_account_id' => $line['account_id'],
                        'description'         => $line['description'],
                        'debit'               => $line['debit'],
                        'credit'              => $line['credit'],
                    ]);
                }
            }

            // As per requirement: admin can directly submit
            if ($request->has('action') && $request->action === 'submit') {
                $this->approvalService->submit($journal);
            }
        });

        return redirect()->route('journals.index')->with('success', 'Journal entry created successfully.');
    }

    public function show(Request $request, JournalEntry $journal)
    {
        abort_if($journal->company_id !== $request->user()->current_company_id, 403);
        
        $journal->load(['lines.account', 'approvalLogs.user', 'createdBy', 'reviewedBy', 'approvedBy', 'branch', 'project']);
        
        return view('journals.show', compact('journal'));
    }

    // Action Methods
    public function submit(Request $request, JournalEntry $journal)
    {
        abort_if($journal->company_id !== $request->user()->current_company_id, 403);
        
        $this->approvalService->submit($journal);
        return back()->with('success', 'Journal submitted for review.');
    }

    public function review(Request $request, JournalEntry $journal)
    {
        abort_if($journal->company_id !== $request->user()->current_company_id, 403);
        // Authorization would go here
        
        $this->approvalService->review($journal);
        return back()->with('success', 'Journal reviewed successfully.');
    }

    public function approve(Request $request, JournalEntry $journal)
    {
        abort_if($journal->company_id !== $request->user()->current_company_id, 403);
        // Authorization would go here
        
        $this->approvalService->approve($journal);
        return back()->with('success', 'Journal approved successfully.');
    }

    public function reject(Request $request, JournalEntry $journal)
    {
        abort_if($journal->company_id !== $request->user()->current_company_id, 403);
        
        $request->validate(['notes' => 'required|string']);
        
        $this->approvalService->reject($journal, $request->notes);
        return back()->with('success', 'Journal rejected.');
    }

    public function destroy(Request $request, JournalEntry $journal)
    {
        abort_if($journal->company_id !== $request->user()->current_company_id, 403);
        
        if ($journal->status === 'approved') {
            return back()->with('error', 'Cannot delete an approved journal.');
        }

        $journal->delete();
        return redirect()->route('journals.index')->with('success', 'Journal deleted.');
    }
}

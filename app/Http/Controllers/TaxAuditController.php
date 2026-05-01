<?php

namespace App\Http\Controllers;

use App\Models\TaxAudit;
use App\Models\Branch;
use Illuminate\Http\Request;

class TaxAuditController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $audits = TaxAudit::where('company_id', $company->id)
            ->with('branch')
            ->orderBy('document_date', 'desc')
            ->paginate(15);

        return view('tax_audits.index', compact('audits'));
    }

    public function create(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        return view('tax_audits.form', [
            'audit' => new TaxAudit(['document_date' => now(), 'tax_period_year' => now()->year, 'status' => 'received']),
            'branches' => Branch::where('company_id', $company->id)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'document_type' => 'required|in:SP2DK,STP,SKPKB,SKPLB,SKPN',
            'document_number' => 'required|string|max:255',
            'document_date' => 'required|date',
            'tax_period_year' => 'required|integer|min:2000|max:2100',
            'principal_amount' => 'required|numeric|min:0',
            'penalty_amount' => 'required|numeric|min:0',
            'status' => 'required|in:received,responded,closed',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['company_id'] = $company->id;

        TaxAudit::create($validated);

        return redirect()->route('tax-audits.index')->with('success', 'Tax Document recorded successfully.');
    }

    public function edit(Request $request, TaxAudit $tax_audit)
    {
        abort_if($tax_audit->company_id !== $request->user()->current_company_id, 403);
        
        $company = $request->user()->currentCompany;
        
        return view('tax_audits.form', [
            'audit' => $tax_audit,
            'branches' => Branch::where('company_id', $company->id)->get(),
        ]);
    }

    public function update(Request $request, TaxAudit $tax_audit)
    {
        abort_if($tax_audit->company_id !== $request->user()->current_company_id, 403);

        $validated = $request->validate([
            'document_type' => 'required|in:SP2DK,STP,SKPKB,SKPLB,SKPN',
            'document_number' => 'required|string|max:255',
            'document_date' => 'required|date',
            'tax_period_year' => 'required|integer|min:2000|max:2100',
            'principal_amount' => 'required|numeric|min:0',
            'penalty_amount' => 'required|numeric|min:0',
            'status' => 'required|in:received,responded,closed',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $tax_audit->update($validated);

        return redirect()->route('tax-audits.index')->with('success', 'Tax Document updated successfully.');
    }

    public function destroy(Request $request, TaxAudit $tax_audit)
    {
        abort_if($tax_audit->company_id !== $request->user()->current_company_id, 403);
        
        $tax_audit->delete();
        
        return redirect()->route('tax-audits.index')->with('success', 'Tax Document deleted.');
    }
}

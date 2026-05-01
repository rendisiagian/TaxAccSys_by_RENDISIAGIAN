<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->whereNull('parent_id')
            ->with('descendants')
            ->orderBy('account_code')
            ->get();

        $flatAccounts = ChartOfAccount::where('company_id', $company->id)
            ->orderBy('account_code')
            ->get();

        return view('coa.index', compact('accounts', 'flatAccounts', 'company'));
    }

    public function create(Request $request)
    {
        $company = $request->user()->currentCompany;
        $parents = ChartOfAccount::where('company_id', $company->id)
            ->where('is_header', true)
            ->orderBy('account_code')
            ->get();

        return view('coa.form', [
            'account' => new ChartOfAccount(),
            'parents' => $parents,
            'company' => $company,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'account_code'   => 'required|string|max:20|unique:chart_of_accounts,account_code,NULL,id,company_id,' . $company->id,
            'account_name'   => 'required|string|max:255',
            'account_type'   => 'required|in:asset,liability,equity,revenue,cogs,expense,other_income,tax',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:chart_of_accounts,id',
            'is_header'      => 'boolean',
            'description'    => 'nullable|string',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_header'] = $request->boolean('is_header');
        $validated['level'] = 1;

        if ($validated['parent_id']) {
            $parent = ChartOfAccount::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        }

        ChartOfAccount::create($validated);

        return redirect()->route('coa.index')
            ->with('success', __('Data saved successfully.'));
    }

    public function edit(Request $request, ChartOfAccount $coa)
    {
        abort_if($coa->company_id !== $request->user()->current_company_id, 403);

        $company = $request->user()->currentCompany;
        $parents = ChartOfAccount::where('company_id', $company->id)
            ->where('is_header', true)
            ->where('id', '!=', $coa->id)
            ->orderBy('account_code')
            ->get();

        return view('coa.form', [
            'account' => $coa,
            'parents' => $parents,
            'company' => $company,
        ]);
    }

    public function update(Request $request, ChartOfAccount $coa)
    {
        $company = $request->user()->currentCompany;
        abort_if($coa->company_id !== $company->id, 403);

        $validated = $request->validate([
            'account_code'   => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $coa->id . ',id,company_id,' . $company->id,
            'account_name'   => 'required|string|max:255',
            'account_type'   => 'required|in:asset,liability,equity,revenue,cogs,expense,other_income,tax',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id'      => 'nullable|exists:chart_of_accounts,id',
            'is_header'      => 'boolean',
            'description'    => 'nullable|string',
        ]);

        $validated['is_header'] = $request->boolean('is_header');
        $validated['level'] = 1;

        if ($validated['parent_id']) {
            $parent = ChartOfAccount::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        }

        $coa->update($validated);

        return redirect()->route('coa.index')
            ->with('success', __('Data updated successfully.'));
    }

    public function destroy(Request $request, ChartOfAccount $coa)
    {
        abort_if($coa->company_id !== $request->user()->current_company_id, 403);

        if ($coa->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }

        if ($coa->children()->count() > 0) {
            return back()->with('error', 'Cannot delete account with sub-accounts.');
        }

        $coa->delete();

        return redirect()->route('coa.index')
            ->with('success', __('Data deleted successfully.'));
    }
}

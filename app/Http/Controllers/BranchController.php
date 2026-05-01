<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $branches = Branch::where('company_id', $company->id)
            ->orderBy('is_head_office', 'desc')
            ->orderBy('name')
            ->paginate(15);

        return view('branches.index', compact('branches', 'company'));
    }

    public function create(Request $request)
    {
        return view('branches.form', [
            'branch'  => new Branch(),
            'company' => $request->user()->currentCompany,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:10|unique:branches,code,NULL,id,company_id,' . $company->id,
            'nitku'          => 'nullable|string|max:22',
            'address'        => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:255',
            'province'       => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'is_head_office' => 'boolean',
        ]);

        $validated['company_id'] = $company->id;
        $validated['is_head_office'] = $request->boolean('is_head_office');

        if ($validated['is_head_office']) {
            Branch::where('company_id', $company->id)->update(['is_head_office' => false]);
        }

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', __('Data saved successfully.'));
    }

    public function edit(Request $request, Branch $branch)
    {
        abort_if($branch->company_id !== $request->user()->current_company_id, 403);
        return view('branches.form', [
            'branch'  => $branch,
            'company' => $request->user()->currentCompany,
        ]);
    }

    public function update(Request $request, Branch $branch)
    {
        $company = $request->user()->currentCompany;
        abort_if($branch->company_id !== $company->id, 403);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => 'required|string|max:10|unique:branches,code,' . $branch->id . ',id,company_id,' . $company->id,
            'nitku'          => 'nullable|string|max:22',
            'address'        => 'nullable|string|max:255',
            'city'           => 'nullable|string|max:255',
            'province'       => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'is_head_office' => 'boolean',
        ]);

        $validated['is_head_office'] = $request->boolean('is_head_office');

        if ($validated['is_head_office']) {
            Branch::where('company_id', $company->id)->where('id', '!=', $branch->id)->update(['is_head_office' => false]);
        }

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', __('Data updated successfully.'));
    }

    public function destroy(Request $request, Branch $branch)
    {
        abort_if($branch->company_id !== $request->user()->current_company_id, 403);
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', __('Data deleted successfully.'));
    }
}

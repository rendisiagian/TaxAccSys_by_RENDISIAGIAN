<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $companies = $request->user()->companies()
            ->orderBy('name')
            ->paginate(15);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.form', ['company' => new Company()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'npwp'          => 'nullable|string|max:20',
            'nitku'         => 'nullable|string|max:22',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'province'      => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:10',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'business_type' => 'nullable|string|max:255',
            'klu_code'      => 'nullable|string|max:10',
            'tax_office'    => 'nullable|string|max:255',
            'company_type'  => 'required|in:regular,construction',
            'notes'         => 'nullable|string',
        ]);

        $company = Company::create($validated);
        $request->user()->companies()->attach($company->id);

        return redirect()->route('companies.index')
            ->with('success', __('Data saved successfully.'));
    }

    public function edit(Company $company)
    {
        $this->authorizeCompanyAccess($company);
        return view('companies.form', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorizeCompanyAccess($company);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'npwp'          => 'nullable|string|max:20',
            'nitku'         => 'nullable|string|max:22',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'province'      => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:10',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'business_type' => 'nullable|string|max:255',
            'klu_code'      => 'nullable|string|max:10',
            'tax_office'    => 'nullable|string|max:255',
            'company_type'  => 'required|in:regular,construction',
            'notes'         => 'nullable|string',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', __('Data updated successfully.'));
    }

    public function destroy(Company $company)
    {
        $this->authorizeCompanyAccess($company);
        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', __('Data deleted successfully.'));
    }

    private function authorizeCompanyAccess(Company $company): void
    {
        if (!auth()->user()->hasAccessToCompany($company->id)) {
            abort(403);
        }
    }
}

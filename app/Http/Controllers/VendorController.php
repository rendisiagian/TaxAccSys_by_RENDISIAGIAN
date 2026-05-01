<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VendorExport;
use App\Exports\VendorTemplateExport;
use App\Imports\VendorImport;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $vendors = Vendor::where('company_id', $company->id)->paginate(15);
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendors.form', ['vendor' => new Vendor()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tin' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $validated['company_id'] = $request->user()->current_company_id;
        
        Vendor::create($validated);
        
        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function edit(Request $request, Vendor $vendor)
    {
        abort_if($vendor->company_id !== $request->user()->current_company_id, 403);
        return view('vendors.form', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        abort_if($vendor->company_id !== $request->user()->current_company_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tin' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
        ]);

        $vendor->update($validated);
        
        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Request $request, Vendor $vendor)
    {
        abort_if($vendor->company_id !== $request->user()->current_company_id, 403);
        $vendor->delete();
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted.');
    }

    public function export(Request $request)
    {
        return Excel::download(new VendorExport($request->user()->current_company_id), 'vendors.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new VendorTemplateExport(), 'vendors_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        Excel::import(new VendorImport($request->user()->current_company_id), $request->file('file'));

        return redirect()->route('vendors.index')->with('success', 'Vendors imported successfully.');
    }
}

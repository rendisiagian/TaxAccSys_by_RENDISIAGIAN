<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeExport;
use App\Exports\EmployeeTemplateExport;
use App\Imports\EmployeeImport;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        $employees = Employee::where('company_id', $company->id)
            ->with('branch')
            ->orderBy('name')
            ->paginate(15);

        return view('employees.index', compact('employees'));
    }

    public function create(Request $request)
    {
        $company = $request->user()->currentCompany;
        
        return view('employees.form', [
            'employee' => new Employee(['is_active' => true]),
            'branches' => Branch::where('company_id', $company->id)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'nik' => 'nullable|string|max:16',
            'npwp' => 'nullable|string|max:20',
            'nitku' => 'nullable|string|max:22',
            'name' => 'required|string|max:255',
            'employee_type' => 'required|in:tetap,tidak_tetap,bukan_pegawai',
            'ptkp_status' => 'required|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $company->id;

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Request $request, Employee $employee)
    {
        abort_if($employee->company_id !== $request->user()->current_company_id, 403);
        
        $company = $request->user()->currentCompany;
        
        return view('employees.form', [
            'employee' => $employee,
            'branches' => Branch::where('company_id', $company->id)->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        abort_if($employee->company_id !== $request->user()->current_company_id, 403);

        $validated = $request->validate([
            'nik' => 'nullable|string|max:16',
            'npwp' => 'nullable|string|max:20',
            'nitku' => 'nullable|string|max:22',
            'name' => 'required|string|max:255',
            'employee_type' => 'required|in:tetap,tidak_tetap,bukan_pegawai',
            'ptkp_status' => 'required|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Request $request, Employee $employee)
    {
        abort_if($employee->company_id !== $request->user()->current_company_id, 403);
        
        $employee->delete();
        
        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }

    public function export(Request $request)
    {
        return Excel::download(new EmployeeExport($request->user()->current_company_id), 'employees.xlsx');
    }

    public function downloadTemplate()
    {
        return Excel::download(new EmployeeTemplateExport(), 'employees_template.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        Excel::import(new EmployeeImport($request->user()->current_company_id), $request->file('file'));

        return redirect()->route('employees.index')->with('success', 'Employees imported successfully.');
    }
}

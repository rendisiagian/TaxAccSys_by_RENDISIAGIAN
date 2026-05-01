<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Branch;
use App\Models\Project;
use App\Models\FiscalYear;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $company = $user->currentCompany;

        // Base foundation stats
        $stats = [
            'branches'     => Branch::where('company_id', $company->id)->count(),
            'projects'     => Project::where('company_id', $company->id)->count(),
            'accounts'     => ChartOfAccount::where('company_id', $company->id)->where('is_header', false)->count(),
            'fiscal_year'  => FiscalYear::where('company_id', $company->id)->where('is_current', true)->first(),
        ];

        // PPN Metrics
        $ppnIn = \App\Models\TaxTransaction::where('company_id', $company->id)->where('tax_type', 'ppn_in')->sum('tax_amount');
        $ppnOut = \App\Models\TaxTransaction::where('company_id', $company->id)->where('tax_type', 'ppn_out')->sum('tax_amount');
        
        // PPh 21 Metrics
        $pph21 = \App\Models\Pph21Monthly::whereHas('employee', function($q) use ($company) {
            $q->where('company_id', $company->id);
        })->sum('tax_amount');

        // Unresolved Tax Audits
        $unresolvedAudits = \App\Models\TaxAudit::where('company_id', $company->id)
            ->whereIn('status', ['received', 'responded'])
            ->orderBy('document_date', 'desc')
            ->get();

        return view('dashboard', compact('company', 'stats', 'ppnIn', 'ppnOut', 'pph21', 'unresolvedAudits'));
    }
}

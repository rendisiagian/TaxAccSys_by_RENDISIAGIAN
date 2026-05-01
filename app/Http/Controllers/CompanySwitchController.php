<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanySwitchController extends Controller
{
    public function switch(Request $request, int $companyId)
    {
        $user = $request->user();

        if (!$user->hasAccessToCompany($companyId)) {
            abort(403, 'Anda tidak memiliki akses ke perusahaan ini.');
        }

        $user->update(['current_company_id' => $companyId]);

        return back()->with('success', __('Company switched successfully.'));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    /**
     * Ensure user has selected and has access to a company.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // If user has no current company, assign the first one they have access to
        if (!$user->current_company_id) {
            $firstCompany = $user->companies()->first();
            if ($firstCompany) {
                $user->update(['current_company_id' => $firstCompany->id]);
            } else {
                abort(403, 'Anda tidak memiliki akses ke perusahaan manapun.');
            }
        }

        // Share current company with all views
        $currentCompany = $user->currentCompany;
        view()->share('currentCompany', $currentCompany);
        view()->share('userCompanies', $user->companies()->get());

        return $next($request);
    }
}

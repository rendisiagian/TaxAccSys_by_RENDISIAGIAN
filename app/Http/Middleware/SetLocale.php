<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Set app locale from user preference or session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'id'; // Default

        if ($request->user()) {
            $locale = $request->user()->locale ?? 'id';
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        if (in_array($locale, ['id', 'en'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}

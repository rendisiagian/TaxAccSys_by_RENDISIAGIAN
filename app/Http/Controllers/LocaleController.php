<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['id', 'en'])) {
            abort(400);
        }

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        return back();
    }
}

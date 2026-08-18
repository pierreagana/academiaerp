<?php

namespace App\Modules\SuperAdmin\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        if (in_array($locale, ['en', 'fr'])) {
            Session::put('app_locale', $locale);
            App::setLocale($locale);
        }
        
        return back();
    }
}

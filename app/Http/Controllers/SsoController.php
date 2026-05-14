<?php

namespace App\Http\Controllers;

use App\Services\Sso\SsoLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    public function consume(Request $request, string $token, SsoLinkService $ssoLinks): RedirectResponse
    {
        $ssoToken = $ssoLinks->consume($token);

        Auth::login($ssoToken->user);
        $request->session()->regenerate();

        return redirect($ssoToken->redirect_path ?: route('dashboard', [], false));
    }
}

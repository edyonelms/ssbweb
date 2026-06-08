<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'mobile'   => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'password' => ['required', 'string', 'min:4'],
        ], [
            'mobile.required'  => 'Please enter your mobile number.',
            'mobile.regex'     => 'Please enter a valid 10-digit mobile number.',
            'password.required'=> 'Please enter your password.',
            'password.min'     => 'Password must be at least 4 characters.',
        ]);

        $user = User::where('mobile', $credentials['mobile'])->first();

        if (! $user) {
            // If a soft-deleted row with this mobile exists, tell the
            // user explicitly — otherwise they hit the same generic
            // "invalid credentials" wall and assume they mistyped.
            $trashed = User::onlyTrashed()->where('mobile', $credentials['mobile'])->first();
            if ($trashed) {
                throw ValidationException::withMessages([
                    'mobile' => 'This account has been removed by the administrator. Please contact support.',
                ]);
            }

            throw ValidationException::withMessages([
                'mobile' => 'Invalid mobile number or password.',
            ]);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'mobile' => 'Invalid mobile number or password.',
            ]);
        }

        if (! $user->active) {
            throw ValidationException::withMessages([
                'mobile' => 'Your account is inactive. Please contact your administrator.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

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

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'mobile' => 'Invalid mobile number or password.',
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

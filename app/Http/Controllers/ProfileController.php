<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile.index', [
            'settings' => Settings::current(),
        ]);
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'platform_name'       => ['nullable', 'string', 'max:255'],
            'platform_email'      => ['nullable', 'email', 'max:255'],
            'platform_mobile'     => ['nullable', 'string', 'max:20'],
            'platform_alt_mobile' => ['nullable', 'string', 'max:20'],
            'website'             => ['nullable', 'string', 'max:255'],
            'owner'               => ['nullable', 'string', 'max:255'],
            'address'             => ['nullable', 'string', 'max:1000'],
            'bank_name'           => ['nullable', 'string', 'max:255'],
            'bank_branch'         => ['nullable', 'string', 'max:255'],
            'bank_ifsc'           => ['nullable', 'string', 'max:20'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_holder_name'    => ['nullable', 'string', 'max:255'],
        ]);

        Settings::current()->update($data);

        return redirect()
            ->route('profile.index', ['tab' => 'edit'])
            ->with('status', 'Profile details updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => [
                'required',
                'string',
                'confirmed',
                'regex:/^(?=.*[A-Za-z])(?=.*[^A-Za-z0-9]).{8,16}$/',
            ],
        ], [
            'password.regex'     => 'Password must be 8-16 characters and include at least one letter and one special character.',
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $user->update(['password' => $data['password']]);

        return redirect()
            ->route('profile.index', ['tab' => 'password'])
            ->with('status', 'Password updated successfully.');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $settings = Settings::current();

        if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            Storage::disk('public')->delete($settings->logo_path);
        }

        $path = $request->file('logo')->store('uploads', 'public');
        $settings->update(['logo_path' => $path]);

        return redirect()
            ->route('profile.index')
            ->with('status', 'Logo updated successfully.');
    }
}

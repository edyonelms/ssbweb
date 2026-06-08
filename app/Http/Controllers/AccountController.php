<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('account.index');
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'mobile'              => ['required', 'string', 'regex:/^[0-9]{10,15}$/', Rule::unique('users', 'mobile')->ignore($user->id)],
            'address'             => ['nullable', 'string', 'max:1000'],
            'bank_name'           => ['nullable', 'string', 'max:255'],
            'bank_branch'         => ['nullable', 'string', 'max:255'],
            'bank_ifsc'           => ['nullable', 'string', 'max:20'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_holder_name'    => ['nullable', 'string', 'max:255'],
        ], [
            'mobile.regex'  => 'Mobile must be 10–15 digits.',
            'mobile.unique' => 'This mobile number is already in use.',
            'email.unique'  => 'This email is already in use.',
        ]);

        $user->update($data);

        return redirect()
            ->route('account.index')
            ->with('status', 'Your details have been updated.');
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
            ->route('account.index', ['tab' => 'password'])
            ->with('status', 'Password updated successfully.');
    }
}

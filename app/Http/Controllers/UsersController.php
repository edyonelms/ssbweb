<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $users = User::where('role', User::ROLE_SUBADMIN)
            ->orderByDesc('id')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);

        $data['role'] = User::ROLE_SUBADMIN;
        $data['active'] = $request->boolean('active');

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        abort_if($user->isAdmin(), 403);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        $data = $this->validateUser($request, $user->id);
        $data['active'] = $request->boolean('active');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'User deleted successfully.');
    }

    private function validateUser(Request $request, ?int $ignoreId = null): array
    {
        $isUpdate = $ignoreId !== null;

        return $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'mobile'   => ['required', 'string', 'regex:/^[0-9]{10,15}$/', Rule::unique('users', 'mobile')->ignore($ignoreId)],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:4', 'max:50'],
            'address'  => ['nullable', 'string', 'max:1000'],
        ], [
            'mobile.regex'   => 'Mobile must be 10–15 digits.',
            'mobile.unique'  => 'This mobile number is already in use.',
            'email.unique'   => 'This email is already in use.',
        ]);
    }
}

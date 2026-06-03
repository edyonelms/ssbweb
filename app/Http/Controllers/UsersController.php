<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $users = User::where('role', User::ROLE_SUBADMIN)
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total'   => $users->count(),
            'active'  => $users->where('active', true)->count(),
            'pending' => $users->where('active', false)->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);

        $data['role'] = User::ROLE_SUBADMIN;
        $data['active'] = $request->boolean('active');

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        $data = $this->validateUser($request, $user->id);
        $data['active'] = $request->boolean('active');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

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
            'avatar'   => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'mobile.regex'  => 'Mobile must be 10–15 digits.',
            'mobile.unique' => 'This mobile number is already in use.',
            'email.unique'  => 'This email is already in use.',
            'avatar.image'  => 'Avatar must be an image file.',
            'avatar.mimes'  => 'Avatar must be a PNG, JPG, JPEG, or WEBP file.',
            'avatar.max'    => 'Avatar must be less than 2MB.',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsersController extends Controller
{
    private const STATUS_OPTIONS = ['all', 'active', 'pending'];

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), self::STATUS_OPTIONS, true)
            ? $request->query('status')
            : 'all';

        $search = trim((string) $request->query('q', ''));

        $query = User::where('role', User::ROLE_SUBADMIN)->orderByDesc('id');

        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'pending') {
            $query->where('active', false);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('mobile', 'like', $like)
                  ->orWhere('email', 'like', $like);
            });
        }

        $users = $query->get();

        // Stats stay scoped to the role only (not the active chip), so they
        // stay stable as the user toggles between filters.
        $allUsers = User::where('role', User::ROLE_SUBADMIN)->get(['id', 'active']);
        $stats = [
            'total'   => $allUsers->count(),
            'active'  => $allUsers->where('active', true)->count(),
            'pending' => $allUsers->where('active', false)->count(),
        ];

        return view('users.index', [
            'users'  => $users,
            'stats'  => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request);

        $data['role'] = User::ROLE_SUBADMIN;
        $data['active'] = $request->boolean('active');

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        $user = User::create($data);

        ActivityLog::record(
            'user.created',
            'Added sub-admin '.$user->name,
            $user
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'User created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        $data = $this->validateUser($request, $user->id);
        $data['active'] = $request->boolean('active');

        // Pull password out of the mass-assigned data so an empty input
        // can never clobber the existing hash. A non-empty value gets
        // re-assigned explicitly below — the User model's `hashed` cast
        // bcrypts it on set.
        $newPassword = $data['password'] ?? null;
        unset($data['password']);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        $user->fill($data);

        $passwordChanged = false;
        if (is_string($newPassword) && $newPassword !== '') {
            $user->password = $newPassword;
            $passwordChanged = true;
        }

        $user->save();

        // When admin resets the sub-admin's password, blow away every
        // active session row for that user so any device they were
        // still signed in on is bounced back to the login screen and
        // must enter the new password.
        if ($passwordChanged && Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        ActivityLog::record(
            'user.updated',
            'Updated sub-admin '.$user->name.($passwordChanged ? ' (password reset)' : ''),
            $user,
            ['password_changed' => $passwordChanged]
        );

        return redirect()
            ->route('users.index')
            ->with('status', $passwordChanged
                ? 'User updated successfully. New password is now active.'
                : 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 403);

        $name = $user->name;

        // Soft-delete only — preserves the row so the login screen can
        // tell deleted accounts apart from "wrong mobile" attempts and
        // surface the right message. Wipe any live sessions so the user
        // is kicked out of every device they were still signed in on.
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        $user->delete();

        ActivityLog::record(
            'user.deleted',
            'Removed sub-admin '.$name
        );

        return redirect()
            ->route('users.index')
            ->with('status', 'User deleted successfully.');
    }

    private function validateUser(Request $request, ?int $ignoreId = null): array
    {
        $isUpdate = $ignoreId !== null;

        return $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'mobile'               => ['required', 'string', 'regex:/^[0-9]{10,15}$/', Rule::unique('users', 'mobile')->ignore($ignoreId)],
            'password'             => [$isUpdate ? 'nullable' : 'required', 'string', 'min:4', 'max:50'],
            'address'              => ['nullable', 'string', 'max:1000'],
            'organization_name'    => ['nullable', 'string', 'max:255'],
            'organization_details' => ['nullable', 'string', 'max:2000'],
            'avatar'               => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
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

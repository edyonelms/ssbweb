@extends('layouts.admin')

@section('title', 'Edit User - SSB Education')

@section('admin')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="text-lg font-extrabold text-slate-800">Edit User</h2>
        <p class="text-sm text-slate-500 mt-0.5">Update the user's profile or reset their password.</p>
    </div>

    <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')
        @include('users._form', ['user' => $user, 'submitLabel' => 'Save Changes'])
    </form>
</div>
@endsection

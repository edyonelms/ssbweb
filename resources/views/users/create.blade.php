@extends('layouts.admin')

@section('title', 'Add User - SSB Education')

@section('admin')
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="px-6 py-5 border-b border-slate-100">
        <h2 class="text-lg font-extrabold text-slate-800">Add User</h2>
        <p class="text-sm text-slate-500 mt-0.5">Create a sub-admin account. They will login at the same URL using their mobile and password.</p>
    </div>

    <form method="POST" action="{{ route('users.store') }}" class="p-6 sm:p-8 space-y-6">
        @csrf
        @include('users._form', ['submitLabel' => 'Create User'])
    </form>
</div>
@endsection

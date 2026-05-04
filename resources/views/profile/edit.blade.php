@extends('layouts.app')

@section('title', 'User Profile')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">Profile Settings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-10">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Account Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your profile information and account security.</p>
    </div>

    {{-- Profile Information --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Profile Information</h2>
                <p class="text-xs text-gray-500">Update your account's profile information and email address.</p>
            </div>
        </div>
        <div class="p-6 lg:p-8">
            <div class="max-w-2xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    {{-- Update Password --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden transition-all hover:shadow-md">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Update Password</h2>
                <p class="text-xs text-gray-500">Ensure your account is using a long, random password to stay secure.</p>
            </div>
        </div>
        <div class="p-6 lg:p-8">
            <div class="max-w-2xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    {{-- Delete Account (Optional or restricted) --}}
    @if(auth()->user()->role !== 'super-admin' && auth()->user()->role !== 'developer')
    <div class="bg-white rounded-2xl border border-red-100 shadow-sm overflow-hidden transition-all hover:shadow-md">
        <div class="px-6 py-5 border-b border-red-50 flex items-center gap-3 bg-red-50/30">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-red-900">Delete Account</h2>
                <p class="text-xs text-red-500">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            </div>
        </div>
        <div class="p-6 lg:p-8">
            <div class="max-w-2xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

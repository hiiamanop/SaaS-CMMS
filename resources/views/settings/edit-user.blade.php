@extends('layouts.app')

@section('title', 'Edit User')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li><a href="{{ route('settings.index') }}" class="hover:text-gray-700">Settings</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">Edit User</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-lg mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>

    <form action="{{ route('settings.users.update', $user) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg border border-gray-200 p-6 space-y-5">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
            @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
            @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
            <select name="role" required class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                @foreach($roles as $role)
                <option value="{{ $role->name }}" {{ old('role', $user->role) === $role->name ? 'selected' : '' }}>{{ $role->label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telpon</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Foto Profil</label>
            @if($user->avatar)
                <div class="mb-2">
                    <img src="{{ asset('storage/'.$user->avatar) }}" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                </div>
            @endif
            <input type="file" name="avatar" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-brand hover:file:bg-gray-200">
            @error('avatar') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300">
            <label for="is_active" class="text-sm text-gray-700">Akun Aktif</label>
        </div>
        <div class="border-t border-gray-100 pt-4">
            <p class="text-xs text-gray-500 mb-3">Kosongkan jika tidak ingin mengubah password</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                    <input type="password" name="password" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                </div>
            </div>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 bg-gray-900 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-gray-700 transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ url()->previous() }}" class="flex-1 text-center bg-white border border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

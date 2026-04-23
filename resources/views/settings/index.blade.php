@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">Settings</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'users') }}', editRole: null }">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola pengguna dan konfigurasi sistem</p>
        </div>
        <div x-show="tab === 'users'">
            <a href="{{ route('settings.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                Tambah User
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Tab nav --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex gap-6">
            <button @click="tab='users'"
                :class="tab==='users' ? 'border-b-2 border-gray-900 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="py-3 text-sm transition-colors">
                Pengguna
            </button>
            <button @click="tab='roles'"
                :class="tab==='roles' ? 'border-b-2 border-gray-900 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="py-3 text-sm transition-colors">
                Role
            </button>
            <button @click="tab='locations'"
                :class="tab==='locations' ? 'border-b-2 border-gray-900 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="py-3 text-sm transition-colors">
                Lokasi PLTS
            </button>
        </nav>
    </div>

    {{-- ── Tab: Users ──────────────────────────────────────────────────── --}}
    <div x-show="tab==='users'" x-transition>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Manajemen Pengguna</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Nama</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Email</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Role</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Status</th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-sm font-semibold uppercase">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                        <td class="px-5 py-3">
                            @php $roleLabel = $roles->firstWhere('name', $user->role)?->label ?? ucfirst($user->role); @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($user->role === 'admin') bg-purple-100 text-purple-800
                                @elseif($user->role === 'supervisor') bg-blue-100 text-blue-800
                                @elseif($user->role === 'technician') bg-green-100 text-green-800
                                @else bg-orange-100 text-orange-800 @endif">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($user->is_active ?? true)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('settings.users.edit', $user) }}"
                                   class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                <button @click="$dispatch('open-delete', {action: '{{ route('settings.users.destroy', $user) }}', message: 'Hapus user {{ addslashes($user->name) }}?'})"
                                        class="text-sm text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50">
                                    Hapus
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm14 0l-3-3m0 0l-3 3m3-3v8"/></svg>
                            Tidak ada pengguna.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Tab: Roles ──────────────────────────────────────────────────── --}}
    <div x-show="tab==='roles'" x-transition>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Manajemen Role</h2>
                <p class="text-xs text-gray-400 mt-0.5">Role ditandai <span class="text-orange-500 font-medium">terkunci</span> tidak dapat diubah nama slug-nya atau dihapus.</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Slug</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Label</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Deskripsi</th>
                        <th class="px-5 py-3 text-center font-medium text-gray-600">Users</th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($roles as $role)
                    <tr class="hover:bg-gray-50" x-data="{ editing: false }">
                        {{-- View row --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3">
                                <code class="text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">{{ $role->name }}</code>
                                @if($role->isProtected())
                                <span class="ml-1 text-xs text-orange-500">&#128274;</span>
                                @endif
                            </td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3">
                                @if($role->isProtected())
                                <code class="text-xs bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded">{{ $role->name }}</code>
                                @else
                                <input form="form-role-{{ $role->id }}" name="name" value="{{ $role->name }}" required
                                    class="w-32 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-brand">
                                @endif
                            </td>
                        </template>

                        {{-- Label --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $role->label }}</td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3">
                                <input form="form-role-{{ $role->id }}" name="label" value="{{ $role->label }}" required
                                    class="w-40 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-brand">
                            </td>
                        </template>

                        {{-- Description --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3 text-gray-500 text-xs">{{ $role->description ?: '—' }}</td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3">
                                <input form="form-role-{{ $role->id }}" name="description" value="{{ $role->description }}"
                                    placeholder="Deskripsi..."
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-brand">
                            </td>
                        </template>

                        {{-- User count --}}
                        <td class="px-5 py-3 text-center text-gray-500">
                            {{ $users->where('role', $role->name)->count() }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right">
                            <form id="form-role-{{ $role->id }}" action="{{ route('settings.roles.update', $role) }}" method="POST" x-show="editing" class="inline">
                                @csrf @method('PUT')
                                <div class="flex items-center justify-end gap-2">
                                    <button type="submit" class="text-sm text-green-700 hover:text-green-900 px-3 py-1.5 rounded-lg border border-green-200 hover:bg-green-50">Simpan</button>
                                    <button type="button" @click="editing=false" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Batal</button>
                                </div>
                            </form>
                            <div class="flex items-center justify-end gap-2" x-show="!editing">
                                <button type="button" @click="editing=true"
                                        class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">
                                    Edit
                                </button>
                                @if(!$role->isProtected())
                                <button @click="$dispatch('open-delete', {action: '{{ route('settings.roles.destroy', $role) }}', message: 'Hapus role {{ addslashes($role->label) }}?'})"
                                        class="text-sm text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50">
                                    Hapus
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada role.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add new role form --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 mt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Role Baru</h3>
            <form action="{{ route('settings.roles.store') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Slug <span class="text-red-500">*</span></label>
                    <input name="name" value="{{ old('name') }}" required placeholder="cth: viewer" maxlength="50"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Label <span class="text-red-500">*</span></label>
                    <input name="label" value="{{ old('label') }}" required placeholder="cth: Viewer" maxlength="100"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('label') border-red-400 @enderror">
                    @error('label')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                    <input name="description" value="{{ old('description') }}" placeholder="Opsional" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                    Tambah
                </button>
            </form>
        </div>
    </div>

    {{-- ── Tab: Lokasi PLTS ──────────────────────────────────────────────────── --}}
    <div x-show="tab==='locations'" x-transition>
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Manajemen Lokasi PLTS</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Nama Lokasi</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Kode</th>
                        <th class="px-5 py-3 text-left font-medium text-gray-600">Kapasitas (kWp)</th>
                        <th class="px-5 py-3 text-center font-medium text-gray-600">Status</th>
                        <th class="px-5 py-3 text-right font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($locations as $loc)
                    <tr class="hover:bg-gray-50" x-data="{ editing: false }">
                        {{-- Name --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $loc->name }}</td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3">
                                <input form="form-loc-{{ $loc->id }}" name="name" value="{{ $loc->name }}" required
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-brand">
                            </td>
                        </template>

                        {{-- Code --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3 text-gray-600">{{ $loc->code ?: '—' }}</td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3">
                                <input form="form-loc-{{ $loc->id }}" name="code" value="{{ $loc->code }}"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-brand">
                            </td>
                        </template>

                        {{-- Capacity --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3 text-gray-600">{{ $loc->capacity_kwp ? number_format((float)$loc->capacity_kwp, 2) : '—' }}</td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3">
                                <input type="number" step="0.01" form="form-loc-{{ $loc->id }}" name="capacity_kwp" value="{{ $loc->capacity_kwp }}"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-brand">
                            </td>
                        </template>

                        {{-- Status --}}
                        <template x-if="!editing">
                            <td class="px-5 py-3 text-center">
                                @if($loc->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Nonaktif</span>
                                @endif
                            </td>
                        </template>
                        <template x-if="editing">
                            <td class="px-5 py-3 text-center">
                                <select form="form-loc-{{ $loc->id }}" name="is_active" class="px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none">
                                    <option value="1" {{ $loc->is_active ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ !$loc->is_active ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </td>
                        </template>

                        {{-- Actions --}}
                        <td class="px-5 py-3 text-right">
                            <form id="form-loc-{{ $loc->id }}" action="{{ route('settings.locations.update', $loc) }}" method="POST" x-show="editing" class="inline">
                                @csrf @method('PUT')
                                <div class="flex items-center justify-end gap-2">
                                    <button type="submit" class="text-sm text-green-700 hover:text-green-900 px-3 py-1.5 rounded-lg border border-green-200 hover:bg-green-50">Simpan</button>
                                    <button type="button" @click="editing=false" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">Batal</button>
                                </div>
                            </form>
                            <div class="flex items-center justify-end gap-2" x-show="!editing">
                                <button type="button" @click="editing=true"
                                        class="text-sm text-gray-600 hover:text-gray-900 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50">
                                    Edit
                                </button>
                                <button @click="$dispatch('open-delete', {action: '{{ route('settings.locations.destroy', $loc) }}', message: 'Hapus lokasi PLTS {{ addslashes($loc->name) }}?'})"
                                        class="text-sm text-red-600 hover:text-red-800 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50">
                                    Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada lokasi PLTS.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add new location form --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5 mt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Tambah Lokasi PLTS</h3>
            <form action="{{ route('settings.locations.store') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-48">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                    <input name="name" value="{{ old('name') }}" required placeholder="cth: PLTS Atap Gedung A" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kode / ID</label>
                    <input name="code" value="{{ old('code') }}" placeholder="cth: GDA-01" maxlength="50"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kapasitas (kWp)</label>
                    <input type="number" step="0.01" name="capacity_kwp" value="{{ old('capacity_kwp') }}" placeholder="cth: 50.5"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                    Tambah
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

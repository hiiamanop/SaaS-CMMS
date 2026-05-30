@extends('layouts.app')
@section('title', 'Findings')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Findings</span>@endsection
@section('content')
@php
    $statusColors = ['open'=>'bg-red-50 text-red-600','in_progress'=>'bg-amber-50 text-amber-600','resolved'=>'bg-emerald-50 text-emerald-600','closed'=>'bg-gray-100 text-gray-500'];
    $statusLabels = ['open'=>'Open','in_progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed'];
@endphp
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Findings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Anomali dari checksheet dan temuan lapangan</p>
        </div>
        <a href="{{ route('findings.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            Tambah Finding
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Cari judul..." class="flex-1 min-w-[180px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Status</option>
                <option value="open"        {{ request('status')=='open'?'selected':'' }}>Open</option>
                <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
                <option value="resolved"    {{ request('status')=='resolved'?'selected':'' }}>Resolved</option>
                <option value="closed"      {{ request('status')=='closed'?'selected':'' }}>Closed</option>
            </select>
            <select name="source_type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Sumber</option>
                <option value="checksheet" {{ request('source_type')=='checksheet'?'selected':'' }}>Checksheet</option>
                <option value="manual"     {{ request('source_type')=='manual'?'selected':'' }}>Manual</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white font-bold rounded-lg text-sm hover:bg-gray-700">Filter</button>
            <a href="{{ route('findings.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($findings->isEmpty())
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-gray-400 font-medium">Tidak ada findings</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3 text-left">Judul</th>
                    <th class="px-5 py-3 text-left">Lokasi</th>
                    <th class="px-5 py-3 text-left">Sumber</th>
                    <th class="px-5 py-3 text-left">Status</th>
                    <th class="px-5 py-3 text-left">Finding Time</th>
                    <th class="px-5 py-3 text-left">Close Time</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($findings as $finding)
                <tr class="hover:bg-gray-50 transition-all">
                    <td class="px-5 py-4">
                        <a href="{{ route('findings.show', $finding) }}" class="font-bold text-gray-900 hover:text-emerald-600 transition-colors">{{ $finding->title }}</a>
                        @if($finding->description)
                        <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[250px]">{{ $finding->description }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <p class="text-sm font-medium text-gray-700">{{ $finding->location_label ?: '-' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @if($finding->source_type === 'checksheet')
                        <div class="space-y-0.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700">Checksheet</span>
                            <p class="text-xs text-gray-500">{{ $finding->session?->schedule?->title ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $finding->session?->period_label }}</p>
                        </div>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">Manual</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $statusColors[$finding->status] ?? 'bg-gray-100' }}">
                            <span class="w-1 h-1 rounded-full bg-current"></span>
                            {{ $statusLabels[$finding->status] ?? $finding->status }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-500">{{ $finding->finding_time?->format('d M Y, H:i') ?? '-' }}</td>
                    <td class="px-5 py-4 text-xs text-gray-500">{{ $finding->close_time?->format('d M Y, H:i') ?? '-' }}</td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('findings.show', $finding) }}"
                               class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            <a href="{{ route('findings.edit', $finding) }}" class="text-xs font-bold text-gray-500 hover:text-gray-700 transition-colors">Edit</a>
                            <form method="POST" action="{{ route('findings.destroy', $finding) }}" onsubmit="return confirm('Hapus finding ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-400 hover:text-red-600 transition-colors">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-50">{{ $findings->links() }}</div>
        @endif
    </div>
</div>
@endsection

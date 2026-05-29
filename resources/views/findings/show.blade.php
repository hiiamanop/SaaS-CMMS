@extends('layouts.app')
@section('title', $finding->title)
@section('breadcrumb')
<span class="text-gray-400">/</span><a href="{{ route('findings.index') }}" class="hover:text-gray-800">Findings</a>
<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ Str::limit($finding->title, 40) }}</span>
@endsection
@section('content')
@php
    $statusColors = ['open'=>'bg-red-50 text-red-600','in_progress'=>'bg-amber-50 text-amber-600','resolved'=>'bg-emerald-50 text-emerald-600','closed'=>'bg-gray-100 text-gray-500'];
    $statusLabels = ['open'=>'Open','in_progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed'];
    $photos       = $finding->evidence_photos;
@endphp
<div class="max-w-3xl space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $statusColors[$finding->status] ?? 'bg-gray-100' }}">
                        <span class="w-1 h-1 rounded-full bg-current"></span>
                        {{ $statusLabels[$finding->status] ?? $finding->status }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">{{ $finding->title }}</h1>
            </div>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('findings.edit', $finding) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition-all">Edit</a>
                <form method="POST" action="{{ route('findings.destroy', $finding) }}" onsubmit="return confirm('Hapus finding ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-lg text-sm font-bold hover:bg-red-100 transition-all">Hapus</button>
                </form>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Lokasi</p>
                <p class="font-semibold text-gray-800">{{ $finding->location_label ?: '-' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Tanggal Ditemukan</p>
                <p class="font-semibold text-gray-800">{{ $finding->found_date?->format('d M Y') ?? '-' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Tanggal Selesai</p>
                <p class="font-semibold text-gray-800">{{ $finding->resolved_date?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- Sumber --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Sumber Finding</h3>
        @if($finding->source_type === 'checksheet' && $finding->session)
        @php $schedule = $finding->session->schedule; @endphp
        <div class="flex items-start gap-3 bg-purple-50 border border-purple-100 rounded-xl p-4">
            <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-purple-500 uppercase tracking-widest">Dari Checksheet</p>
                <p class="font-bold text-gray-800 mt-0.5">{{ $schedule?->title ?? 'Maintenance Schedule' }}</p>
                <p class="text-sm text-gray-500">{{ $finding->session->period_label }} — {{ $finding->session->plts_location }}</p>
                @if($schedule?->trafo_name)
                <p class="text-xs text-gray-400 mt-0.5">Trafo: {{ $schedule->trafo_name }}</p>
                @endif
                @if($finding->item_name)
                <p class="text-xs text-gray-500 mt-1 font-medium">Item: {{ $finding->item_name }}</p>
                @endif
                <a href="{{ route('checksheet.fill', $finding->session) }}" class="inline-block mt-2 text-xs font-bold text-purple-600 hover:underline">Lihat Checksheet →</a>
            </div>
        </div>
        @else
        <div class="flex items-center gap-3 bg-gray-50 border border-gray-100 rounded-xl p-4">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Input Manual</p>
                <p class="font-bold text-gray-700 mt-0.5">{{ $finding->reportedBy?->name ?? 'Unknown' }}</p>
                <p class="text-xs text-gray-400">{{ $finding->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Detail --}}
    @if($finding->description || $finding->action_taken)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
        @if($finding->description)
        <div>
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Deskripsi Anomali</h3>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $finding->description }}</p>
        </div>
        @endif
        @if($finding->action_taken)
        <div class="{{ $finding->description ? 'border-t border-gray-50 pt-4' : '' }}">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tindakan Penanganan</h3>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $finding->action_taken }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Evidence Photos --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Evidence Foto</h3>
        @if(!empty($photos))
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($photos as $photo)
            <a href="{{ Storage::url($photo) }}" target="_blank" class="block group relative">
                <img src="{{ Storage::url($photo) }}"
                     class="w-full h-40 object-cover rounded-xl border border-gray-200 group-hover:opacity-90 transition-opacity">
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/20 rounded-xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-8 text-center">
            <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-sm text-gray-400 font-medium">Belum ada foto evidence</p>
            @if($finding->source_type === 'checksheet')
            <p class="text-xs text-gray-300 mt-1">Foto akan muncul jika diupload pada checksheet terkait</p>
            @endif
        </div>
        @endif
    </div>

</div>
@endsection

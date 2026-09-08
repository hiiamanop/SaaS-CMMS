@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            <li><a href="{{ route('dashboard') }}" class="hover:text-emerald-600 transition-colors">Home</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 font-medium">Dashboard Overview</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="space-y-8 pb-10" x-data="{ showActiveTools: false }">
{{-- Header / Welcome Section (Link / DESIGN.md Aesthetic) --}}
    <div class="relative overflow-hidden bg-[#171717] rounded-[24px] p-8 lg:p-12 text-white border border-[#E5E5E5]/10 shadow-[0_4px_24px_rgba(23,23,23,0.06)]">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold text-white/90 mb-4 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-[#00C767] animate-pulse"></span>
                    <span>Sistem Operasi PLTS Aktif</span>
                </div>
                <h1 class="text-3xl lg:text-4xl font-bold tracking-tight text-white">Selamat Datang, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h1>
                <p class="text-gray-300 mt-2 text-base lg:text-lg max-w-xl font-normal leading-relaxed">
                    Sistem Manajemen Aset PLTS Aruna Hijau Power berjalan dengan optimal. Berikut ringkasan performa operasional hari ini.
                </p>
                <div class="flex flex-wrap gap-3 mt-6">
                    <a href="{{ route('work-orders.create') }}" class="inline-flex items-center justify-center gap-2 bg-[#00C767] hover:bg-[#00B05B] text-[#011E0F] px-6 py-3 rounded-[10px] font-semibold text-sm transition-all shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Buat Work Order</span>
                    </a>
                    <a href="{{ route('checksheet.index') }}" class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/15 text-white px-6 py-3 rounded-[10px] font-medium text-sm transition-all border border-white/15">
                        <span>Cek Jadwal</span>
                    </a>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="w-32 h-32 rounded-[20px] bg-white shadow-xl flex items-center justify-center p-2 border border-white/20">
                    <img src="{{ asset('logo.jpeg') }}" alt="Logo" class="w-full h-full object-contain rounded-[14px]">
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
        @php
            $stats = [
                ['label' => 'Aset Terdaftar', 'value' => $totalAssets, 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'emerald', 'route' => route('assets.index')],
                ['label' => 'Work Order Aktif', 'value' => $openWorkOrders, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'amber', 'route' => route('work-orders.index', ['status'=>'active'])],
                ['label' => 'Tugas Terlambat', 'value' => $overdueWorkOrders, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'red', 'route' => route('work-orders.index', ['filter'=>'overdue'])],
                ['label' => 'Suku Cadang Minim', 'value' => $lowStockCount, 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'orange', 'route' => route('spare-parts.index', ['filter'=>'low_stock'])],
                ['label' => 'Tools Terpakai', 'value' => $activeToolUsages->sum('qty'), 'icon' => 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.6-3.6a6 6 0 01-7.7 7.7L6.4 20.6a2 2 0 01-2.8-2.8l7.2-7.2a6 6 0 017.7-7.7l-3.8 3.4z', 'color' => 'blue', 'modal' => true],
            ];
        @endphp

        @foreach($stats as $stat)
        <div @if(!empty($stat['modal'])) @click="showActiveTools = true" role="button" tabindex="0" @endif class="group bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-xl hover:border-emerald-100 transition-all duration-300 @if(!empty($stat['modal'])) cursor-pointer @endif">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-{{ $stat['color'] }}-50 flex items-center justify-center text-{{ $stat['color'] }}-600 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="{{ $stat['icon'] }}"/></svg>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-xs font-bold text-{{ $stat['color'] }}-600 bg-{{ $stat['color'] }}-50 px-2 py-0.5 rounded-full uppercase tracking-wider">
                        {{ $stat['color'] === 'red' ? 'Alert' : 'Live' }}
                    </span>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black text-gray-900">{{ number_format($stat['value']) }}</h3>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">{{ $stat['label'] }}</p>
            @if(!empty($stat['modal']))
            <button type="button" @click.stop="showActiveTools = true" class="mt-4 flex items-center gap-2 text-xs font-bold text-blue-600 hover:text-blue-700 uppercase tracking-widest transition-all">
                Lihat Tools <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
            @else
            <a href="{{ $stat['route'] }}" class="mt-4 flex items-center gap-2 text-xs font-bold text-emerald-600 hover:text-emerald-700 uppercase tracking-widest transition-all">
                Detail Dashboard <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Active tools modal --}}
    <div x-show="showActiveTools" x-cloak @keydown.escape.window="showActiveTools = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" style="display:none">
        <div @click.outside="showActiveTools = false" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl max-h-[80vh] overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Tools Sedang Terpakai</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar tools yang sedang digunakan pada Work Order aktif.</p>
                </div>
                <button type="button" @click="showActiveTools = false" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 overflow-y-auto max-h-[calc(80vh-90px)]">
                @if($activeToolUsages->isEmpty())
                <div class="py-12 text-center text-sm text-gray-400">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.6-3.6a6 6 0 01-7.7 7.7L6.4 20.6a2 2 0 01-2.8-2.8l7.2-7.2a6 6 0 017.7-7.7l-3.8 3.4z"/></svg>
                    Belum ada tool yang sedang digunakan pada Work Order aktif.
                </div>
                @else
                <div class="overflow-x-auto border border-gray-100 rounded-xl">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-3 text-left">Nama Tool</th>
                                <th class="px-4 py-3 text-left">Qty</th>
                                <th class="px-4 py-3 text-left">Work Order</th>
                                <th class="px-4 py-3 text-left">Status WO</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        @foreach($activeToolUsages as $usage)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-900">{{ $usage['tool_name'] }}</div>
                                <div class="text-[10px] font-mono text-gray-400">{{ $usage['tool_code'] }}</div>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-700">{{ $usage['qty'] }} unit</td>
                            <td class="px-4 py-3">
                                <a href="{{ $usage['wo_url'] }}" class="font-bold text-blue-700 hover:underline font-mono text-xs">{{ $usage['wo_number'] }}</a>
                                <span class="block text-xs text-gray-500 truncate max-w-[260px]">{{ $usage['wo_title'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold uppercase">{{ str_replace('_', ' ', $usage['wo_status']) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ $usage['wo_url'] }}" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                                    <span>Buka WO</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Middle Section: Charts & Upcoming --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        {{-- Performance Analytics --}}
        <div class="xl:col-span-2 bg-white rounded-[18px] border border-[#E5E5E5] shadow-[0_2px_8px_rgba(23,23,23,0.03)] overflow-hidden">
            <div class="px-6 py-5 border-b border-[#E5E5E5]/70 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Analisis Performa Pekerjaan</h2>
                    <p class="text-xs text-gray-500">Trend pembuatan vs penyelesaian WO (6 Bulan Terakhir)</p>
                </div>
                <div class="flex gap-2">
                    <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2.5 h-2.5 rounded-full bg-[#00C767]"></span> Selesai</span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2.5 h-2.5 rounded-full bg-[#C7F7DE]"></span> Open</span>
                </div>
            </div>
            <div class="p-6">
                <div class="h-[300px]"><canvas id="woChart"></canvas></div>
            </div>
        </div>

        {{-- Upcoming Maintenance --}}
        <div class="bg-white rounded-[18px] border border-[#E5E5E5] shadow-[0_2px_8px_rgba(23,23,23,0.03)] overflow-hidden">
            <div class="px-6 py-5 border-b border-[#E5E5E5]/70">
                <h2 class="text-lg font-bold text-gray-900">Jadwal Terdekat</h2>
                <p class="text-xs text-gray-500">7 Hari ke depan</p>
            </div>
            <div class="p-4 space-y-3">
                @forelse($upcomingSchedules as $session)
                <div class="group bg-emerald-50/40 hover:bg-emerald-50 rounded-2xl p-4 transition-all border border-transparent hover:border-emerald-100">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-emerald-600 flex-shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black text-emerald-700 uppercase tracking-widest">{{ $session->period_label }}</p>
                            <p class="text-sm font-bold text-gray-900 truncate mt-0.5">{{ $session->schedule->equipment_name ?? $session->equipment_location }}</p>
                            <div class="flex items-center gap-1.5 mt-1">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/></svg>
                                <p class="text-[10px] text-gray-500 font-medium">{{ $session->schedule->location->name ?? 'Lokasi PLTS' }}</p>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('checksheet.fill', $session) }}" class="mt-4 block w-full bg-white hover:bg-emerald-600 hover:text-white text-emerald-700 border border-emerald-200 py-2 rounded-xl text-xs font-bold text-center transition-all shadow-sm">
                        Mulai Inspeksi
                    </a>
                </div>
                @empty
                <div class="py-12 text-center">
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Tidak ada jadwal mendesak</p>
                </div>
                @endforelse
                <a href="{{ route('maintenance-schedules.index') }}" class="block text-center text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] hover:text-emerald-600 transition-all pt-2">
                    Lihat Semua Jadwal
                </a>
            </div>
        </div>
    </div>

    {{-- Bottom Section: Recent Activities & Spare Parts --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        {{-- Recent Work Orders --}}
        <div class="bg-white rounded-[18px] border border-[#E5E5E5] shadow-[0_2px_8px_rgba(23,23,23,0.03)] overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Aktivitas Terakhir</h2>
                <a href="{{ route('work-orders.index') }}" class="text-xs font-bold text-[#00C767] hover:underline">Semua WO</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest text-left">
                            <th class="px-6 py-3">No. WO</th>
                            <th class="px-6 py-3">Aset / Klien</th>
                            <th class="px-6 py-3 text-center">Prioritas</th>
                            <th class="px-6 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentWorkOrders as $wo)
                        <tr class="hover:bg-emerald-50/30 transition-all group">
                            <td class="px-6 py-4">
                                <a href="{{ route('work-orders.show', $wo) }}" class="font-mono text-xs font-bold text-emerald-600 group-hover:underline">{{ $wo->wo_number }}</a>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $wo->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900">{{ $wo->asset->name ?? ($wo->client_name ?: 'External') }}</p>
                                <p class="text-[10px] text-gray-500 truncate max-w-[180px]">{{ $wo->title }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php 
                                    $pColors = [
                                        'low' => 'bg-gray-100 text-gray-600',
                                        'medium' => 'bg-emerald-100 text-emerald-700',
                                        'high' => 'bg-amber-100 text-amber-700',
                                        'critical' => 'bg-red-100 text-red-700'
                                    ];
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-tighter {{ $pColors[$wo->priority] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $wo->priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php 
                                    $sColors = [
                                        'open' => 'bg-blue-50 text-blue-600',
                                        'in_progress' => 'bg-amber-50 text-amber-600',
                                        'pending_review' => 'bg-purple-50 text-purple-600',
                                        'closed' => 'bg-emerald-50 text-emerald-600'
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold {{ $sColors[$wo->status] ?? 'bg-gray-100' }}">
                                    <span class="w-1 h-1 rounded-full bg-current"></span>
                                    {{ strtoupper($wo->status_label) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Inventory Alert --}}
        <div class="bg-white rounded-[18px] border border-[#E5E5E5] shadow-[0_2px_8px_rgba(23,23,23,0.03)] overflow-hidden">
            <div class="px-6 py-5 border-b border-[#E5E5E5]/70 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Stok Suku Cadang</h2>
                    <p class="text-xs text-red-500 font-medium">Membutuhkan pengisian ulang segera</p>
                </div>
                <a href="{{ route('spare-parts.index') }}" class="text-xs font-bold text-[#00C767] hover:underline">Kelola Stok</a>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    @forelse($lowStockParts as $part)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $part->name }}</p>
                                    <p class="text-[10px] text-gray-500 font-medium uppercase tracking-widest">MIN: {{ $part->qty_minimum }} {{ $part->unit }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-black {{ $part->qty_actual <= ($part->qty_minimum / 2) ? 'text-red-600' : 'text-amber-600' }}">
                                {{ $part->qty_actual }} {{ $part->unit }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 {{ $part->qty_actual <= ($part->qty_minimum / 2) ? 'bg-red-500' : 'bg-amber-500' }}" 
                                 style="width: {{ min(100, ($part->qty_actual / max(1, $part->qty_minimum)) * 100) }}%">
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="py-10 text-center">
                        <svg class="w-12 h-12 text-emerald-100 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium text-gray-400">Semua stok terpantau aman.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- PV Module Map --}}
    @if($pvMapData->isNotEmpty())
    @php
        $firstBlock    = $pvMapData->keys()->first() ?? '';
        $allBlocks     = $pvMapData->keys()->values()->toArray();
        $blockLocNames = $blockLocations ?? [];
    @endphp
    <div id="peta-pv" class="bg-white rounded-[20px] border border-[#E5E5E5] shadow-[0_2px_8px_rgba(23,23,23,0.03)] overflow-hidden"
         x-data="{
             activeBlock: '{{ $firstBlock }}',
             blocks: {{ json_encode($allBlocks) }},
             blockNames: {{ json_encode($blockLocNames) }},
             blockLocationIds: {{ json_encode($blockLocationIds) }},
             showBlockDropdown: false,
             editDataMode: false,
             editMode: false,
             detailModal: {
                 show: false, id: null, code: '', name: '', status: '', block: '', inverter: '', string: '',
                 row: 1, col: 1, brand: '', model: '', serial: '', desc: '', showUrl: '', woUrl: '',
                 workOrders: [], loadingWo: false, woCount: 0, openWoCount: 0
             },
             openDetailModal(item) {
                 this.detailModal = {
                     show: true,
                     id: item.id,
                     code: item.code,
                     name: item.name,
                     status: item.status,
                     block: item.block,
                     inverter: 'INV' + String(item.string_number).padStart(2, '0'),
                     string: 'S' + String(item.module_slot).padStart(2, '0'),
                     row: item.row,
                     col: item.col,
                     brand: item.brand || 'Jinko Solar',
                     model: item.model || 'JKM550M-72HL4-V',
                     serial: item.serial || ('SN-' + item.code),
                     desc: item.desc || '',
                     showUrl: '/assets/' + item.id,
                     woUrl: '{{ route('work-orders.create') }}?asset_id=' + item.id + '&from_asset=' + item.id,
                     workOrders: [],
                     loadingWo: true,
                     woCount: 0,
                     openWoCount: 0,
                 };
                 fetch('/assets/' + item.id + '/work-orders')
                     .then(r => r.json())
                     .then(data => {
                         if (this.detailModal.id === item.id) {
                             this.detailModal.workOrders = data.work_orders || [];
                             this.detailModal.woCount = data.count || 0;
                             this.detailModal.openWoCount = data.open_count || 0;
                             this.detailModal.loadingWo = false;
                         }
                     })
                     .catch(() => {
                         if (this.detailModal.id === item.id) {
                             this.detailModal.loadingWo = false;
                         }
                     });
             },
             closeDetailModal() {
                 this.detailModal.show = false;
             },
             modal: { show: false, mode: '', assetId: null, row: 1, col: 1, x: 0, y: 0 },
             assetList: [], assetSearch: '', selectedCategory: '', selectedAssetId: null, selectedAsset: null, showDropdown: false,
             get activeIndex() { return this.blocks.indexOf(this.activeBlock); },
             prev() { this.activeBlock = this.blocks[Math.max(0, this.activeIndex - 1)]; },
             next() { this.activeBlock = this.blocks[Math.min(this.blocks.length - 1, this.activeIndex + 1)]; },
             get filteredAssets() {
                 let list = this.assetList;
                 if (this.selectedCategory) list = list.filter(a => a.category === this.selectedCategory);
                 const q = this.assetSearch.toLowerCase();
                 if (q) list = list.filter(a => a.name.toLowerCase().includes(q) || a.asset_code.toLowerCase().includes(q));
                 return list;
             },
             async openModal(event, row, col, assetId, category) {
                 const rect = event.currentTarget.getBoundingClientRect();
                 let x = rect.right + 8, y = rect.top;
                 if (x + 272 > window.innerWidth) x = rect.left - 280;
                 if (y + 300 > window.innerHeight) y = window.innerHeight - 310;
                 this.modal = { show: true, mode: assetId ? 'edit' : 'add', assetId: assetId || null, row, col, x, y };
                 this.assetSearch = '';
                 this.showDropdown = false;
                 this.selectedAssetId = assetId || null;
                 this.selectedAsset = null;
                 this.selectedCategory = category || '';
                 const locationId = this.blockLocationIds[this.activeBlock];
                 if (locationId) {
                     const r = await fetch(`{{ route('assets.by-location') }}?location_id=${locationId}`);
                     this.assetList = await r.json();
                     if (assetId) this.selectedAsset = this.assetList.find(a => a.id === assetId) || null;
                 }
             },
             selectAsset(asset) { this.selectedAssetId = asset.id; this.selectedAsset = asset; this.showDropdown = false; this.assetSearch = ''; },
             closeModal() { this.modal.show = false; this.assetList = []; this.assetSearch = ''; this.showDropdown = false; },
             async submitModal() {
                 if (!this.selectedAssetId) return;
                 const csrf = document.querySelector('meta[name=csrf-token]').content;
                 const body = new URLSearchParams({
                     _token: csrf, asset_id: this.selectedAssetId,
                     transformer_block: this.activeBlock,
                     visual_row: this.modal.row, visual_col: this.modal.col,
                 });
                 const r = await fetch('{{ route('assets.quick-save-pv') }}', { method: 'POST', body });
                 if ((await r.json()).ok) { this.closeModal(); window.location.href = window.location.pathname + '#peta-pv'; window.location.reload(); }
             },
             async deleteAsset(assetId) {
                 if (!confirm('Hapus modul ini dari peta?')) return;
                 const csrf = document.querySelector('meta[name=csrf-token]').content;
                 await fetch(`/assets/${assetId}/pv`, { method: 'DELETE', headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'} });
                 this.closeModal(); window.location.href = window.location.pathname + '#peta-pv'; window.location.reload();
             },
             init() {
                 this.$watch('activeBlock', b => {
                     this.$nextTick(() => { if (typeof pvFitView === 'function') pvFitView(b); });
                 });
                 this.$nextTick(() => { if (typeof pvFitView === 'function') pvFitView(this.activeBlock); });
             }
         }">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-50 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Peta Susunan PV Module</h2>
                <p class="text-xs text-gray-500">Status kondisi setiap modul secara visual — klik sel untuk detail aset</p>
            </div>
            <div class="flex items-center gap-1.5">
                {{-- Prev --}}
                <button @click="prev()" :disabled="activeIndex === 0"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                </button>

                {{-- Badge + Dropdown --}}
                <div class="relative">
                    <button @click="showBlockDropdown = !showBlockDropdown"
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md shadow-emerald-200/50 transition-all">
                        <span class="font-mono" x-text="activeBlock"></span>
                        <span class="text-emerald-200 font-normal text-xs" x-text="blockNames[activeBlock] ? '— ' + blockNames[activeBlock] : ''"></span>
                        <svg class="w-3.5 h-3.5 text-emerald-300 transition-transform duration-200" :class="showBlockDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="showBlockDropdown"
                         @click.outside="showBlockDropdown = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-1.5 bg-white rounded-xl shadow-xl border border-gray-100 py-1.5 min-w-[200px] z-50">
                        @foreach($pvMapData->keys() as $block)
                        <button @click="activeBlock = '{{ $block }}'; showBlockDropdown = false"
                                class="w-full text-left px-4 py-2.5 text-sm transition-all flex items-center justify-between gap-4"
                                :class="activeBlock === '{{ $block }}' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-50'">
                            <span class="font-mono font-bold">{{ $block }}</span>
                            <span class="text-xs text-gray-400 truncate">{{ $blockLocations[$block] ?? '' }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Next --}}
                <button @click="next()" :disabled="activeIndex === blocks.length - 1"
                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        {{-- Legend --}}
        <div class="px-6 pt-4 pb-2 flex items-center gap-5 flex-wrap">
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded bg-emerald-400 inline-block"></span>Active</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded bg-amber-400 inline-block"></span>Replaced</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded bg-gray-300 inline-block"></span>Inactive</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded bg-red-400 inline-block"></span>Retired</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded border-2 border-dashed border-gray-300 inline-block"></span>Belum Input</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded bg-blue-500 inline-block"></span>Inverter</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-600 font-medium"><span class="w-3.5 h-3.5 rounded bg-violet-500 inline-block"></span>Transformer</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-700 font-medium"><span class="w-3 h-3 rounded-full bg-amber-500 border border-white inline-block"></span>Ada WO Aktif</span>
        </div>

        {{-- Grid per block --}}
        @foreach($pvMapData as $block => $modules)
        @php
            $useVisual = $modules->whereNotNull('visual_row')->whereNotNull('visual_col')->count() > 0;
            $statusColors = [
                'active'            => 'bg-emerald-500 hover:bg-emerald-600 text-white ring-emerald-300 shadow-sm shadow-emerald-500/20',
                'replaced'          => 'bg-amber-500 hover:bg-amber-600 text-white ring-amber-300 shadow-sm shadow-amber-500/20',
                'inactive'          => 'bg-gray-300 hover:bg-gray-400 text-gray-700 ring-gray-200',
                'retired'           => 'bg-rose-500 hover:bg-rose-600 text-white ring-rose-300 shadow-sm shadow-rose-500/20',
            ];

            if ($useVisual) {
                $maxRow = $modules->max('visual_row');
                $maxCol = $modules->max('visual_col');
                // Build lookup: [row][col] => asset
                $grid = [];
                foreach ($modules->whereNotNull('visual_row') as $m) {
                    $grid[$m->visual_row][$m->visual_col] = $m;
                }
                // Detect column gaps (section dividers — gaps ≥ 2 between consecutive used cols)
                $usedCols = $modules->whereNotNull('visual_col')->pluck('visual_col')->unique()->sort()->values();
                $dividerCols = [];
                for ($i = 1; $i < $usedCols->count(); $i++) {
                    if ($usedCols[$i] - $usedCols[$i - 1] >= 2) {
                        $dividerCols[] = $usedCols[$i - 1]; // gap after this col
                    }
                }
            } else {
                $maxRow = $modules->max('string_number');
                $maxCol = $modules->max('module_slot');
                $grid = [];
                foreach ($modules as $m) {
                    $grid[$m->string_number][$m->module_slot] = $m;
                }
                $dividerCols = [];
            }

            // Supporting assets (Inverter, Transformer, Metering) with visual positions
            $supportingGrid = [];
            if (isset($supportingAssets[$block])) {
                foreach ($supportingAssets[$block] as $sa) {
                    if ($sa->visual_row && $sa->visual_col) {
                        $supportingGrid[$sa->visual_row][$sa->visual_col] = $sa;
                        $maxRow = max($maxRow, $sa->visual_row);
                    }
                }
            }
        @endphp
        <div x-show="activeBlock === '{{ $block }}'" x-cloak
             class="px-6 pb-6">

            @if(!$useVisual)
            <p class="text-[10px] text-amber-500 font-medium mb-2 pt-2">
                ⚠ Posisi visual belum dikonfigurasi — menampilkan grid sederhana (string × slot).
                Import layout via: <code class="bg-gray-100 px-1 rounded">php artisan cmms:import-pv-layout layout.csv</code>
            </p>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-2 mb-2 pt-1">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-semibold border border-emerald-100 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>{{ $modules->count() }} String Terpasang</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-medium">
                        <span>Grid: {{ $maxRow }} Baris &times; {{ $maxCol }} Kolom</span>
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Edit Data button --}}
                    <template x-if="!editDataMode && !editMode">
                        <button @click="editDataMode = true; closeModal()"
                                class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all">
                            ✎ Edit Data
                        </button>
                    </template>
                    <template x-if="editDataMode">
                        <button @click="editDataMode = false; closeModal()"
                                class="px-3 py-1 rounded-lg text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 transition-all">
                            ✕ Selesai Edit
                        </button>
                    </template>

                    {{-- Atur Posisi buttons --}}
                    <template x-if="!editMode && !editDataMode">
                        <button @click="editMode = true; window.pvEditMode = true; pvInitPositions()"
                                class="px-3 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition-all">
                            ✎ Atur Posisi
                        </button>
                    </template>
                    <template x-if="editMode">
                        <div class="flex gap-2">
                            <button @click="pvSave().then(() => { editMode = false; window.pvEditMode = false; })"
                                    class="px-3 py-1 rounded-lg text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 transition-all">
                                ✓ Simpan
                            </button>
                            <button @click="editMode = false; window.pvEditMode = false; window.location.href = window.location.pathname + '#peta-pv'; window.location.reload()"
                                    class="px-3 py-1 rounded-lg text-xs font-bold bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all">
                                ✕ Batal
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Hover Modal --}}
            <div x-show="modal.show" x-cloak @click.outside="closeModal()"
                 :style="`position:fixed;top:${modal.y}px;left:${modal.x}px;z-index:9999;width:272px`"
                 class="bg-white rounded-xl border border-gray-200 shadow-2xl p-4">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs font-black text-gray-700"
                           x-text="modal.mode === 'add' ? 'Tambah ke Peta' : 'Ganti / Hapus dari Peta'"></p>
                        <p class="text-[10px] text-gray-400 mt-0.5">
                            Baris <span x-text="modal.row"></span>, Kolom <span x-text="modal.col"></span>
                        </p>
                    </div>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Kategori filter --}}
                <div class="mb-2">
                    <select x-model="selectedCategory" @change="selectedAssetId = null; selectedAsset = null"
                            class="w-full px-2.5 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">Semua Kategori</option>
                        <option value="PV Module">PV Module</option>
                        <option value="Inverter">Inverter</option>
                        <option value="Transformer">Transformer</option>
                        <option value="Metering">Metering</option>
                    </select>
                </div>

                {{-- Selected asset + dropdown trigger --}}
                <div class="relative mb-3">
                    <button @click="showDropdown = !showDropdown"
                            class="w-full text-left px-2.5 py-2 border border-gray-300 rounded-lg text-xs flex items-center justify-between hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all">
                        <span x-show="!selectedAsset" class="text-gray-400">— Pilih asset —</span>
                        <span x-show="selectedAsset" class="text-gray-800">
                            <span class="font-bold" x-text="selectedAsset?.name"></span>
                            <span class="text-gray-400 ml-1" x-text="selectedAsset?.asset_code"></span>
                        </span>
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 ml-1 transition-transform" :class="showDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    {{-- Dropdown panel --}}
                    <div x-show="showDropdown" x-cloak @click.outside="showDropdown = false"
                         class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-10">
                        <div class="p-2 border-b border-gray-100">
                            <input x-model="assetSearch" x-ref="searchInput" @keydown.escape="showDropdown = false"
                                   type="text" placeholder="Cari nama / kode..."
                                   class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div class="max-h-40 overflow-y-auto">
                            <template x-if="filteredAssets.length === 0">
                                <p class="text-center text-xs text-gray-400 py-3">Tidak ada hasil</p>
                            </template>
                            <template x-for="asset in filteredAssets" :key="asset.id">
                                <button @click="selectAsset(asset)"
                                        :class="selectedAssetId === asset.id ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'"
                                        class="w-full text-left px-3 py-2 text-xs transition-all border-b border-gray-50 last:border-0">
                                    <p class="font-semibold leading-tight" x-text="asset.name"></p>
                                    <p class="text-[10px] text-gray-400 mt-0.5" x-text="asset.asset_code + ' · ' + asset.category"></p>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <button @click="submitModal()"
                            :disabled="!selectedAssetId"
                            :class="selectedAssetId ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-200 cursor-not-allowed'"
                            class="flex-1 px-3 py-1.5 text-white rounded-lg text-xs font-bold transition-all">
                        Simpan
                    </button>
                    <template x-if="modal.mode === 'edit' && modal.assetId">
                        <button @click="deleteAsset(modal.assetId)"
                                class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-100">
                            Hapus
                        </button>
                    </template>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-gray-50/50 mt-1 cursor-grab"
                 style="height: 540px;"
                 data-pv-viewport="{{ $block }}">

                <div class="absolute top-3 right-3 z-20 flex flex-col gap-1 shadow-sm">
                    <button type="button" onclick="pvZoomBy('{{ $block }}', 'in')" title="Zoom In"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white shadow border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold text-base leading-none transition-transform active:scale-95">+</button>
                    <button type="button" onclick="pvZoomBy('{{ $block }}', 'out')" title="Zoom Out"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white shadow border border-gray-200 text-gray-700 hover:bg-gray-50 font-bold text-base leading-none transition-transform active:scale-95">&minus;</button>
                    <button type="button" onclick="pvFitView('{{ $block }}')" title="Pas ke Layar (Fit)"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 shadow border border-emerald-200 hover:bg-emerald-100 text-[9px] font-black tracking-tight transition-transform active:scale-95">FIT</button>
                    <button type="button" onclick="pvResetView('{{ $block }}')" title="Reset 100%"
                            class="w-8 h-8 flex items-center justify-center rounded-lg bg-white shadow border border-gray-200 text-gray-500 hover:bg-gray-50 text-[9px] font-bold transition-transform active:scale-95">RST</button>
                </div>

                <div class="flex justify-center" data-pv-canvas="{{ $block }}"
                     style="width: max-content; padding-top: 4px; transform-origin: 0 0;">
                    <table class="border-separate" style="border-spacing:3px;" id="pvGrid_{{ $block }}">
                    @if(!$useVisual)
                    <thead>
                        <tr>
                            <th class="w-8"></th>
                            @for($col = 1; $col <= $maxCol; $col++)
                            <th class="w-[22px] text-[7px] font-bold text-gray-300 text-center pb-0.5">{{ $col }}</th>
                            @endfor
                        </tr>
                    </thead>
                    @endif
                    <tbody>
                    @for($row = 1; $row <= $maxRow; $row++)
                    <tr>
                        @if(!$useVisual)
                        <td class="text-right pr-1 text-[8px] font-bold text-gray-400 w-8 align-middle">
                            N{{ str_pad($row, 2, '0', STR_PAD_LEFT) }}
                        </td>
                        @endif
                        @for($col = 1; $col <= $maxCol; $col++)
                        @php
                            $asset           = $grid[$row][$col] ?? null;
                            $supportingAsset = $supportingGrid[$row][$col] ?? null;
                            $isDivider       = in_array($col, $dividerCols);
                        @endphp
                        <td class="p-0.5 align-middle {{ $isDivider ? 'border-r-2 border-gray-300' : '' }}"
                            style="width:56px;height:34px;"
                            data-row="{{ $row }}" data-col="{{ $col }}">
                            @if($asset)
                            @php
                                $hierarchyCode = $asset->transformer_block . '-INV' . str_pad($asset->string_number, 2, '0', STR_PAD_LEFT) . '-S' . str_pad($asset->module_slot, 2, '0', STR_PAD_LEFT);
                                $colorClass    = $statusColors[$asset->status] ?? 'bg-emerald-500 text-white';
                                $hasWo         = isset($assetWoMap[$asset->id]);
                                $woInfo        = $hasWo ? $assetWoMap[$asset->id] : null;
                            @endphp
                            <a href="{{ route('assets.show', $asset->id) }}"
                               draggable="true"
                               data-asset-id="{{ $asset->id }}"
                               data-tip-code="{{ $hierarchyCode }}"
                               data-tip-name="{{ addslashes($asset->name) }}"
                               data-tip-status="{{ $asset->status }}"
                               data-tip-wo="{{ $hasWo ? $woInfo['latest_wo'] . ': ' . addslashes($woInfo['latest_title']) : '' }}"
                               @click.prevent="if(editDataMode){ openModal($event, {{ $row }}, {{ $col }}, {{ $asset->id }}, 'PV Module') } else { openDetailModal({ id: {{ $asset->id }}, code: '{{ $hierarchyCode }}', name: '{{ addslashes($asset->name) }}', status: '{{ $asset->status }}', block: '{{ $asset->transformer_block }}', string_number: {{ $asset->string_number }}, module_slot: {{ $asset->module_slot }}, row: {{ $row }}, col: {{ $col }}, brand: '{{ addslashes($asset->brand ?? 'Jinko Solar') }}', model: '{{ addslashes($asset->model ?? 'JKM550M-72HL4-V') }}', serial: '{{ addslashes($asset->serial_number ?? '') }}', desc: '{{ addslashes($asset->description ?? '') }}' }) }"
                               :class="editDataMode ? 'cursor-pointer ring-2 ring-blue-400 ring-offset-1' : 'hover:scale-[1.18] hover:z-20'"
                               class="pv-asset group flex flex-col items-center justify-center w-[54px] h-[32px] rounded-[5px] transition-all duration-150 relative shadow-sm border border-black/10 hover:ring-2 {{ $colorClass }}">
                                @if($hasWo)
                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5 z-10" title="Ada Work Order Aktif">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500 border border-white"></span>
                                </span>
                                @endif
                                <span class="text-[6.5px] font-bold opacity-80 leading-none select-none tracking-tight">
                                    INV{{ str_pad($asset->string_number, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="text-[8px] font-black leading-tight select-none tracking-wide">
                                    S{{ str_pad($asset->module_slot, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </a>
                            @elseif($supportingAsset)
                            @php
                                $saColorMap = [
                                    'Inverter'    => 'bg-blue-500 ring-blue-300',
                                    'Transformer' => 'bg-violet-500 ring-violet-300',
                                    'Metering'    => 'bg-indigo-500 ring-indigo-300',
                                ];
                                $saColor = $saColorMap[$supportingAsset->category] ?? 'bg-slate-500 ring-slate-300';
                                $saLabel = $supportingAsset->category === 'Transformer'
                                    ? 'TRAFO'
                                    : collect(explode('-', $supportingAsset->asset_code))->last();
                                $hasSaWo = isset($assetWoMap[$supportingAsset->id]);
                                $saWoInfo = $hasSaWo ? $assetWoMap[$supportingAsset->id] : null;
                            @endphp
                            <a href="{{ route('assets.show', $supportingAsset->id) }}"
                               draggable="true"
                               data-asset-id="{{ $supportingAsset->id }}"
                               data-tip-code="{{ $supportingAsset->asset_code }}"
                               data-tip-name="{{ addslashes($supportingAsset->name) }}"
                               data-tip-status="{{ $supportingAsset->status }}"
                               data-tip-wo="{{ $hasSaWo ? $saWoInfo['latest_wo'] . ': ' . addslashes($saWoInfo['latest_title']) : '' }}"
                               @click="if(editDataMode){ $event.preventDefault(); openModal($event, {{ $row }}, {{ $col }}, {{ $supportingAsset->id }}, '{{ $supportingAsset->category }}') }"
                               :class="editDataMode ? 'cursor-pointer ring-2 ring-blue-300' : 'hover:scale-[1.2] hover:z-10'"
                               class="pv-asset flex items-center justify-center w-[52px] h-[30px] rounded-[3px] transition-all duration-100 relative hover:ring-2 {{ $saColor }}">
                                @if($hasSaWo)
                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5 z-10" title="Ada Work Order Aktif">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500 border border-white"></span>
                                </span>
                                @endif
                                <span class="text-[7px] font-bold text-white leading-none select-none pointer-events-none">{{ $saLabel }}</span>
                            </a>
                            @else
                            <div @click="if(editDataMode) openModal($event, {{ $row }}, {{ $col }}, null, '')"
                                 :class="editDataMode ? 'cursor-pointer border-blue-300 bg-blue-50 hover:bg-blue-100' : 'border-gray-200 bg-gray-50'"
                                 class="w-[52px] h-[30px] rounded-[3px] border border-dashed transition-all"></div>
                            @endif
                        </td>
                        @endfor
                    </tr>
                    @endfor
                    </tbody>
                </table>
                </div>
            </div>

            {{-- Summary strip --}}
            <div class="mt-4 pt-3 border-t border-gray-50 flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-500">
                @if($useVisual)
                <span class="text-emerald-600 font-medium">✓ Layout fisik aktif</span>
                <span>Grid: <b class="text-gray-700">{{ $maxRow }} baris × {{ $maxCol }} kol</b></span>
                @else
                <span>{{ $modules->max('string_number') }} string × {{ $modules->max('module_slot') }} slot</span>
                @endif
                <span>Terdaftar: <b class="text-gray-800">{{ $modules->count() }}</b></span>
                <span class="text-emerald-600 font-medium">Active: {{ $modules->where('status','active')->count() }}</span>
                @if($modules->where('status','replaced')->count())
                <span class="text-amber-600 font-medium">Replaced: {{ $modules->where('status','replaced')->count() }}</span>
                @endif
                @if($modules->where('status','inactive')->count())
                <span class="text-gray-500 font-medium">Inactive: {{ $modules->where('status','inactive')->count() }}</span>
                @endif
                @if($modules->where('status','retired')->count())
                <span class="text-red-500 font-medium">Retired: {{ $modules->where('status','retired')->count() }}</span>
                @endif
            </div>

        </div>
        @endforeach

        {{-- Detail Modal for Selected PV Module --}}
        <div x-show="detailModal.show" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             @keydown.escape.window="closeDetailModal()">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="closeDetailModal()"></div>

            <div class="min-h-full flex items-center justify-center p-4 text-center">
                <div @click.stop class="relative bg-white rounded-2xl max-w-lg w-full p-6 text-left shadow-2xl border border-gray-100 transform transition-all">
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm">
                                PV
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span x-text="detailModal.code"></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800': detailModal.status === 'active',
                                              'bg-amber-100 text-amber-800': detailModal.status === 'replaced',
                                              'bg-gray-100 text-gray-800': detailModal.status === 'inactive',
                                              'bg-rose-100 text-rose-800': detailModal.status === 'retired',
                                          }"
                                          x-text="detailModal.status">
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="detailModal.name"></p>
                            </div>
                        </div>
                        <button @click="closeDetailModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Body Grid --}}
                    <div class="py-4 space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-bold tracking-wider">Blok Trafo</span>
                                <span class="font-bold text-gray-800 text-sm font-mono" x-text="detailModal.block"></span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-bold tracking-wider">Posisi Grid</span>
                                <span class="font-bold text-gray-800 text-sm font-mono">Row <span x-text="detailModal.row"></span>, Col <span x-text="detailModal.col"></span></span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-bold tracking-wider">Inverter Terhubung</span>
                                <span class="font-bold text-blue-600 text-sm font-mono" x-text="detailModal.inverter"></span>
                            </div>
                            <div class="bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                <span class="text-gray-400 block text-[10px] uppercase font-bold tracking-wider">Nomor String</span>
                                <span class="font-bold text-emerald-600 text-sm font-mono" x-text="detailModal.string"></span>
                            </div>
                        </div>

                        <div class="bg-gray-50/60 p-3 rounded-xl border border-gray-100 space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Merek / Tipe:</span>
                                <span class="font-semibold text-gray-800" x-text="detailModal.brand + ' (' + detailModal.model + ')'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Kapasitas String:</span>
                                <span class="font-semibold text-gray-800">28 Panel Seri &times; 550Wp (15.4 kWp)</span>
                            </div>
                            <div class="flex justify-between" x-show="detailModal.serial">
                                <span class="text-gray-500">Serial Number:</span>
                                <span class="font-mono text-gray-700" x-text="detailModal.serial"></span>
                            </div>
                        </div>

                        {{-- Record Work Order Section --}}
                        <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-xs">
                            <div class="bg-gray-50 px-3.5 py-2 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <span class="text-xs font-bold text-gray-800">Record Work Order Aset</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <template x-if="detailModal.openWoCount > 0">
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 animate-pulse" x-text="detailModal.openWoCount + ' Aktif'"></span>
                                    </template>
                                    <span class="text-[10px] font-semibold text-gray-500 bg-gray-200/70 px-1.5 py-0.5 rounded" x-text="detailModal.woCount + ' Total WO'"></span>
                                </div>
                            </div>
                            <div class="p-2.5 max-h-48 overflow-y-auto space-y-2">
                                <template x-if="detailModal.loadingWo">
                                    <div class="flex items-center justify-center py-4 text-gray-400 gap-2 text-xs">
                                        <svg class="w-4 h-4 animate-spin text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span>Memuat riwayat work order...</span>
                                    </div>
                                </template>
                                <template x-if="!detailModal.loadingWo && detailModal.workOrders.length === 0">
                                    <div class="text-center py-3 text-xs text-gray-400">
                                        <p class="font-medium text-gray-600">Belum ada record work order</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">Modul beroperasi normal tanpa catatan kendala.</p>
                                    </div>
                                </template>
                                <template x-for="wo in detailModal.workOrders" :key="wo.id">
                                    <div class="p-2 bg-gray-50/60 hover:bg-emerald-50/40 rounded-lg border border-gray-100 transition-colors flex items-start justify-between gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                                <span class="font-mono font-bold text-gray-900 text-xs" x-text="wo.wo_number"></span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider"
                                                      :class="{
                                                          'bg-blue-100 text-blue-800': wo.status === 'open',
                                                          'bg-amber-100 text-amber-800': wo.status === 'in_progress',
                                                          'bg-purple-100 text-purple-800': wo.status === 'pending_review',
                                                          'bg-emerald-100 text-emerald-800': wo.status === 'closed'
                                                      }" x-text="wo.status_label || wo.status"></span>
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase"
                                                      :class="{
                                                          'bg-red-100 text-red-700': wo.priority === 'critical',
                                                          'bg-orange-100 text-orange-700': wo.priority === 'high',
                                                          'bg-blue-100 text-blue-700': wo.priority === 'medium',
                                                          'bg-gray-100 text-gray-600': wo.priority === 'low'
                                                      }" x-text="wo.priority"></span>
                                            </div>
                                            <p class="text-gray-800 font-medium text-xs leading-snug line-clamp-1" x-text="wo.title"></p>
                                            <p class="text-[10px] text-gray-500 mt-0.5 flex items-center gap-1">
                                                <span>Batas: <strong class="text-gray-700" x-text="wo.due_date"></strong></span>
                                                <span>&bull;</span>
                                                <span class="truncate" x-text="wo.assignees"></span>
                                            </p>
                                        </div>
                                        <a :href="wo.url" class="shrink-0 text-[11px] font-bold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1 rounded-lg transition-colors flex items-center gap-1">
                                            <span>Detail</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="text-xs text-gray-500 bg-emerald-50/50 p-2.5 rounded-xl border border-emerald-100 flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="detailModal.desc || 'String PV aktif terhubung ke MPPT inverter pendukung di blok gardu.'"></span>
                        </div>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <a :href="detailModal.woUrl"
                           class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm shadow-amber-500/20">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                            Buat Work Order
                        </a>
                        <a :href="detailModal.showUrl"
                           class="px-3.5 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm">
                            Lihat Detail Lengkap
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('woChart');
    const chartLabels = @json(array_column($chartData, 'label'));
    const openData = @json(array_column($chartData, 'open'));
    const closedData = @json(array_column($chartData, 'closed'));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Selesai',
                    data: closedData,
                    backgroundColor: '#00C767',
                    borderRadius: 8,
                    barThickness: 20,
                },
                {
                    label: 'Open',
                    data: openData,
                    backgroundColor: '#C7F7DE',
                    borderRadius: 8,
                    barThickness: 20,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#171717',
                    titleFont: { family: 'Inter', size: 13, weight: 'bold' },
                    bodyFont: { family: 'Inter', size: 12 },
                    padding: 12,
                    cornerRadius: 10,
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 11, weight: 'bold' }, color: '#9ca3af' }
                },
                y: {
                    stacked: true,
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: { font: { family: 'Inter', size: 11 }, color: '#9ca3af', stepSize: 5 }
    let pvLastOver = null;
    window.pvOriginalPositions = {};

    // Tooltip
    const pvTip = document.createElement('div');
    pvTip.style.cssText = 'position:fixed;pointer-events:none;z-index:9999;display:none;';
    pvTip.className = 'bg-gray-900 text-white rounded-xl px-3 py-2.5 text-xs shadow-2xl min-w-[140px]';
    document.body.appendChild(pvTip);

    document.addEventListener('mousemove', e => {
        pvTip.style.top  = (e.clientY + 14) + 'px';
        pvTip.style.left = (e.clientX + 14) + 'px';
    });
    document.addEventListener('mouseover', e => {
        if (window.pvEditMode) return;
        const a = e.target.closest('[data-tip-code]');
        if (!a) { pvTip.style.display = 'none'; return; }
        const sColor = { active:'#34d399', replaced:'#fbbf24', inactive:'#9ca3af', retired:'#f87171' };
        let woBadge = '';
        if (a.dataset.tipWo) {
            woBadge = `<div class="mt-1.5 pt-1.5 border-t border-gray-700 text-amber-300 text-[10px] font-bold flex items-center gap-1">
                <span>🔧 WO:</span> <span class="truncate max-w-[200px]">${a.dataset.tipWo}</span>
            </div>`;
        }
        pvTip.innerHTML = `<p class="font-mono font-black" style="color:${sColor[a.dataset.tipStatus]||'#9ca3af'}">${a.dataset.tipCode}</p>
            <p class="mt-0.5 text-gray-200 font-medium">${a.dataset.tipName}</p>
            <p class="mt-1 text-[10px] uppercase tracking-widest font-bold" style="color:${sColor[a.dataset.tipStatus]||'#9ca3af'}">${(a.dataset.tipStatus||'').replaceAll('_',' ')}</p>${woBadge}`;
        pvTip.style.display = 'block';
    });
    document.addEventListener('mouseout', e => {
        if (e.target.closest('[data-tip-code]')) pvTip.style.display = 'none';
    });

    // Store original positions on demand
    function pvInitPositions() {
        window.pvOriginalPositions = {};
        document.querySelectorAll('[data-asset-id]').forEach(el => {
            const td = el.closest('td[data-row]');
            if (!td) return;
            const id = parseInt(el.dataset.assetId);
            pvOriginalPositions[id] = { row: parseInt(td.dataset.row), col: parseInt(td.dataset.col) };
        });
    }

    // Drag events via event delegation
    document.addEventListener('dragstart', e => {
        if (!window.pvEditMode) { e.preventDefault(); return; }
        const a = e.target.closest('[data-asset-id]');
        if (!a) return;
        const td = a.closest('td[data-row]');
        if (!td) return;
        pvDrag = { assetId: parseInt(a.dataset.assetId), td };
        e.dataTransfer.effectAllowed = 'move';
        a.style.opacity = '0.5';
    });

    document.addEventListener('dragend', e => {
        const a = e.target.closest('[data-asset-id]');
        if (a) a.style.opacity = '';
        if (pvLastOver) { pvLastOver.style.outline = ''; pvLastOver = null; }
        pvDrag = null;
    });

    document.addEventListener('dragover', e => {
        if (!window.pvEditMode || !pvDrag) return;
        const td = e.target.closest('td[data-row]');
        if (!td) return;
        e.preventDefault();
        if (pvLastOver !== td) {
            if (pvLastOver) pvLastOver.style.outline = '';
            td.style.outline = '2px solid #3b82f6';
            pvLastOver = td;
        }
    });

    document.addEventListener('dragleave', e => {
        const td = e.target.closest('td[data-row]');
        if (td && pvLastOver === td && !td.contains(e.relatedTarget)) {
            td.style.outline = '';
            pvLastOver = null;
        }
    });

    document.addEventListener('drop', e => {
        if (!window.pvEditMode || !pvDrag) return;
        const td = e.target.closest('td[data-row]');
        if (!td) return;
        e.preventDefault();
        if (pvLastOver) { pvLastOver.style.outline = ''; pvLastOver = null; }
        if (pvDrag.td === td) { pvDrag = null; return; }

        // Visual swap innerHTML
        const tmp = pvDrag.td.innerHTML;
        pvDrag.td.innerHTML = td.innerHTML;
        td.innerHTML = tmp;

        pvDrag = null;
    });

    // Click prevention in edit mode
    document.addEventListener('click', e => {
        if (!window.pvEditMode) return;
        const a = e.target.closest('.pv-asset');
        if (a) e.preventDefault();
    });

    // Cursor style based on edit mode
    function pvUpdateCursor() {
        document.querySelectorAll('.pv-asset').forEach(el => {
            el.style.cursor = window.pvEditMode ? 'grab' : '';
        });
    }

    // Save all changed positions
    async function pvSave() {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const toUpdate = [];
        document.querySelectorAll('[data-asset-id]').forEach(el => {
            const td = el.closest('td[data-row]');
            if (!td) return;
            const id  = parseInt(el.dataset.assetId);
            const row = parseInt(td.dataset.row);
            const col = parseInt(td.dataset.col);
            const orig = pvOriginalPositions[id];
            if (orig && (orig.row !== row || orig.col !== col)) {
                toUpdate.push({ assetId: id, row, col });
            }
        });
        for (const u of toUpdate) {
            await fetch('{{ route("assets.update-position") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ asset_id: u.assetId, row: u.row, col: u.col, _token: csrf }),
            });
        }
        window.location.href = window.location.pathname + '#peta-pv';
        window.location.reload();
    }

    // PV Pan & Zoom
    window.pvMapView = {}; // { [block]: { x, y, scale } }
    const PV_MIN_SCALE = 0.3;
    const PV_MAX_SCALE = 3;
    const PV_ZOOM_STEP = 1.15;

    function pvGetView(block) {
        if (!window.pvMapView[block]) window.pvMapView[block] = { x: 0, y: 0, scale: 1 };
        return window.pvMapView[block];
    }

    function pvClampScale(scale) {
        return Math.min(PV_MAX_SCALE, Math.max(PV_MIN_SCALE, scale));
    }

    function pvApplyTransform(block) {
        const canvas = document.querySelector(`[data-pv-canvas="${block}"]`);
        if (!canvas) return;
        const v = pvGetView(block);
        canvas.style.transform = `translate(${v.x}px, ${v.y}px) scale(${v.scale})`;
    }

    // Zoom so the viewport-relative point (cx, cy) stays visually fixed.
    function pvZoomAt(block, factor, cx, cy) {
        const v = pvGetView(block);
        const newScale = pvClampScale(v.scale * factor);
        const appliedFactor = newScale / v.scale;
        v.x = cx - (cx - v.x) * appliedFactor;
        v.y = cy - (cy - v.y) * appliedFactor;
        v.scale = newScale;
        pvApplyTransform(block);
    }

    function pvZoomBy(block, direction) {
        const viewport = document.querySelector(`[data-pv-viewport="${block}"]`);
        if (!viewport) return;
        const rect = viewport.getBoundingClientRect();
        const factor = direction === 'in' ? PV_ZOOM_STEP : 1 / PV_ZOOM_STEP;
        pvZoomAt(block, factor, rect.width / 2, rect.height / 2);
    }

    function pvResetView(block) {
        window.pvMapView[block] = { x: 0, y: 0, scale: 1 };
        pvApplyTransform(block);
    }

    function pvFitView(block) {
        const viewport = document.querySelector(`[data-pv-viewport="${block}"]`);
        const canvas = document.querySelector(`[data-pv-canvas="${block}"]`);
        if (!viewport || !canvas) return;
        const vpRect = viewport.getBoundingClientRect();
        const table = canvas.querySelector('table');
        if (!table) return;
        const tWidth = table.offsetWidth;
        const tHeight = table.offsetHeight;
        if (tWidth <= 0 || tHeight <= 0) return;
        const pad = 24;
        const scaleX = (vpRect.width - pad * 2) / tWidth;
        const scaleY = (vpRect.height - pad * 2) / tHeight;
        const fitScale = pvClampScale(Math.min(scaleX, scaleY, 1.0));
        const v = pvGetView(block);
        v.scale = fitScale;
        v.x = Math.max(10, (vpRect.width - tWidth * fitScale) / 2);
        v.y = Math.max(10, (vpRect.height - tHeight * fitScale) / 2);
        pvApplyTransform(block);
    }

    // Apply auto-fit transform to every rendered block's canvas on load.
    document.querySelectorAll('[data-pv-canvas]').forEach(el => {
        pvFitView(el.dataset.pvCanvas);
    });
    setTimeout(() => {
        document.querySelectorAll('[data-pv-canvas]').forEach(el => {
            pvFitView(el.dataset.pvCanvas);
        });
    }, 250);

    // Mouse drag-to-pan (background only — never on a .pv-asset, so the
    // existing Atur Posisi native drag-and-drop and normal click-through
    // to an asset's detail page are unaffected).
    const PV_DRAG_THRESHOLD = 4;
    let pvPan = null; // { block, viewport, startX, startY, origX, origY, moved }
    let pvSuppressClick = false;

    document.addEventListener('mousedown', e => {
        const viewport = e.target.closest('[data-pv-viewport]');
        if (!viewport) return;
        // Only skip panning here in Atur Posisi mode, where mousedown on a
        // module must be left free for native drag-to-reposition. Outside
        // that mode, native drag is already suppressed elsewhere (see the
        // dragstart handler above), so panning over a module is safe and
        // necessary — densely packed blocks have little/no empty background.
        if (window.pvEditMode && e.target.closest('.pv-asset')) return;
        const block = viewport.dataset.pvViewport;
        const v = pvGetView(block);
        pvPan = { block, viewport, startX: e.clientX, startY: e.clientY, origX: v.x, origY: v.y, moved: false };
    });

    document.addEventListener('mousemove', e => {
        if (!pvPan) return;
        const dx = e.clientX - pvPan.startX;
        const dy = e.clientY - pvPan.startY;
        if (!pvPan.moved && Math.hypot(dx, dy) < PV_DRAG_THRESHOLD) return;
        pvPan.moved = true;
        pvPan.viewport.style.cursor = 'grabbing';
        const v = pvGetView(pvPan.block);
        v.x = pvPan.origX + dx;
        v.y = pvPan.origY + dy;
        pvApplyTransform(pvPan.block);
    });

    document.addEventListener('mouseup', () => {
        if (pvPan) {
            pvPan.viewport.style.cursor = '';
            if (pvPan.moved) pvSuppressClick = true;
        }
        pvPan = null;
    });

    // A pan that actually moved would otherwise still fire a native click on
    // mouseup's target (e.g. re-opening the Edit Data modal for whatever
    // empty cell the drag ended over) — swallow exactly that one click.
    document.addEventListener('click', e => {
        if (pvSuppressClick) {
            pvSuppressClick = false;
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    // Touch: one-finger pan, two-finger pinch-zoom.
    let pvTouchPan = null; // same shape as pvPan
    let pvPinch = null; // { block, startDist, startScale, midX, midY }

    function pvTouchDist(t0, t1) {
        return Math.hypot(t1.clientX - t0.clientX, t1.clientY - t0.clientY);
    }

    document.addEventListener('touchstart', e => {
        const viewport = e.target.closest('[data-pv-viewport]');
        if (!viewport) return;
        const block = viewport.dataset.pvViewport;

        if (e.touches.length === 1) {
            if (window.pvEditMode && e.target.closest('.pv-asset')) return;
            const v = pvGetView(block);
            const t = e.touches[0];
            pvTouchPan = { block, viewport, startX: t.clientX, startY: t.clientY, origX: v.x, origY: v.y, moved: false };
        } else if (e.touches.length === 2) {
            pvTouchPan = null;
            const v = pvGetView(block);
            const rect = viewport.getBoundingClientRect();
            const [t0, t1] = e.touches;
            pvPinch = {
                block,
                startDist: pvTouchDist(t0, t1),
                startScale: v.scale,
                midX: (t0.clientX + t1.clientX) / 2 - rect.left,
                midY: (t0.clientY + t1.clientY) / 2 - rect.top,
            };
        }
    }, { passive: true });

    document.addEventListener('touchmove', e => {
        if (pvPinch && e.touches.length === 2) {
            const [t0, t1] = e.touches;
            const dist = pvTouchDist(t0, t1);
            const v = pvGetView(pvPinch.block);
            const targetScale = pvClampScale(pvPinch.startScale * (dist / pvPinch.startDist));
            const factor = targetScale / v.scale;
            pvZoomAt(pvPinch.block, factor, pvPinch.midX, pvPinch.midY);
            e.preventDefault();
            return;
        }
        if (pvTouchPan && e.touches.length === 1) {
            const t = e.touches[0];
            const dx = t.clientX - pvTouchPan.startX;
            const dy = t.clientY - pvTouchPan.startY;
            if (!pvTouchPan.moved && Math.hypot(dx, dy) < PV_DRAG_THRESHOLD) return;
            pvTouchPan.moved = true;
            const v = pvGetView(pvTouchPan.block);
            v.x = pvTouchPan.origX + dx;
            v.y = pvTouchPan.origY + dy;
            pvApplyTransform(pvTouchPan.block);
            e.preventDefault();
        }
    }, { passive: false });

    document.addEventListener('touchend', () => {
        pvTouchPan = null;
        pvPinch = null;
    });
</script>
@endpush

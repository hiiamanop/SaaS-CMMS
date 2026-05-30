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
<div class="space-y-8 pb-10">
    {{-- Header / Welcome Section --}}
    <div class="relative overflow-hidden bg-emerald-900 rounded-3xl p-8 lg:p-12 text-white shadow-2xl shadow-emerald-900/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold tracking-tight">Selamat Datang, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h1>
                <p class="text-emerald-100/80 mt-2 text-lg max-w-xl">
                    Sistem Manajemen Aset PLTS Aruna Hijau Power berjalan dengan optimal. Berikut ringkasan performa hari ini.
                </p>
                <div class="flex flex-wrap gap-3 mt-6">
                    <a href="{{ route('work-orders.create') }}" class="bg-emerald-500 hover:bg-emerald-400 text-emerald-950 px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-lg shadow-emerald-500/20">
                        + Buat Work Order
                    </a>
                    <a href="{{ route('checksheet.index') }}" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all border border-white/10">
                        Cek Jadwal
                    </a>
                </div>
            </div>
            <div class="hidden lg:block">
                <div class="w-32 h-32 rounded-3xl bg-white shadow-2xl flex items-center justify-center p-1">
                    <img src="{{ asset('logo.jpeg') }}" alt="Logo" class="w-full h-full object-contain rounded-2xl">
                </div>
            </div>
        </div>
        
        {{-- Decorative Gradients --}}
        <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-emerald-400/10 rounded-full blur-3xl"></div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $stats = [
                ['label' => 'Aset Terdaftar', 'value' => $totalAssets, 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'emerald', 'route' => route('assets.index')],
                ['label' => 'Work Order Aktif', 'value' => $openWorkOrders, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'amber', 'route' => route('work-orders.index', ['status'=>'open'])],
                ['label' => 'Tugas Terlambat', 'value' => $overdueWorkOrders, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'red', 'route' => route('work-orders.index', ['filter'=>'overdue'])],
                ['label' => 'Suku Cadang Minim', 'value' => $lowStockCount, 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'color' => 'orange', 'route' => route('spare-parts.index', ['filter'=>'low_stock'])],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="group bg-white rounded-2xl border border-gray-100 p-6 shadow-sm hover:shadow-xl hover:border-emerald-100 transition-all duration-300">
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
                <h3 class="text-3xl font-black text-gray-900">{{ $stat['value'] }}</h3>
            </div>
            <p class="text-sm font-medium text-gray-500 mt-1">{{ $stat['label'] }}</p>
            <a href="{{ $stat['route'] }}" class="mt-4 flex items-center gap-2 text-xs font-bold text-emerald-600 hover:text-emerald-700 uppercase tracking-widest transition-all">
                Detail Dashboard <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Middle Section: Charts & Upcoming --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        {{-- Performance Analytics --}}
        <div class="xl:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Analisis Performa Pekerjaan</h2>
                    <p class="text-xs text-gray-500">Trend pembuatan vs penyelesaian WO (6 Bulan Terakhir)</p>
                </div>
                <div class="flex gap-2">
                    <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Selesai</span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2.5 h-2.5 rounded-full bg-emerald-200"></span> Open</span>
                </div>
            </div>
            <div class="p-6">
                <div class="h-[300px]"><canvas id="woChart"></canvas></div>
            </div>
        </div>

        {{-- Upcoming Maintenance --}}
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50">
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
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Aktivitas Terakhir</h2>
                <a href="{{ route('work-orders.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Semua WO</a>
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
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Stok Suku Cadang</h2>
                    <p class="text-xs text-red-500 font-medium">Membutuhkan pengisian ulang segera</p>
                </div>
                <a href="{{ route('spare-parts.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Kelola Stok</a>
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
    <div id="peta-pv" class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden"
         x-data="{
             activeBlock: '{{ $firstBlock }}',
             blocks: {{ json_encode($allBlocks) }},
             blockNames: {{ json_encode($blockLocNames) }},
             blockLocationIds: {{ json_encode($blockLocationIds) }},
             showBlockDropdown: false,
             editDataMode: false,
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
        </div>

        {{-- Grid per block --}}
        @foreach($pvMapData as $block => $modules)
        @php
            $useVisual = $modules->whereNotNull('visual_row')->whereNotNull('visual_col')->count() > 0;
            $statusColors = [
                'active'            => 'bg-emerald-400 hover:bg-emerald-500 ring-emerald-300',
                'replaced'          => 'bg-amber-400 hover:bg-amber-500 ring-amber-300',
                'inactive'          => 'bg-gray-300 hover:bg-gray-400 ring-gray-200',
                'retired'           => 'bg-red-400 hover:bg-red-500 ring-red-300',
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
             x-data="{ editMode: false }"
             class="px-6 pb-6 overflow-x-auto">

            @if(!$useVisual)
            <p class="text-[10px] text-amber-500 font-medium mb-2 pt-2">
                ⚠ Posisi visual belum dikonfigurasi — menampilkan grid sederhana (string × slot).
                Import layout via: <code class="bg-gray-100 px-1 rounded">php artisan cmms:import-pv-layout layout.csv</code>
            </p>
            @endif

            <div class="flex items-center justify-end gap-2 mb-2">
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

            <div class="pt-1 flex justify-center">
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
                        <td class="p-0 align-middle {{ $isDivider ? 'border-r-2 border-gray-300' : '' }}"
                            style="width:52px;height:30px;"
                            data-row="{{ $row }}" data-col="{{ $col }}">
                            @if($asset)
                            @php
                                $hierarchyCode = $asset->transformer_block . '-INV' . str_pad($asset->string_number, 2, '0', STR_PAD_LEFT) . '-S' . str_pad($asset->module_slot, 2, '0', STR_PAD_LEFT);
                                $colorClass    = $statusColors[$asset->status] ?? 'bg-gray-200 ring-gray-200';
                            @endphp
                            <a href="{{ route('assets.show', $asset->id) }}"
                               draggable="true"
                               data-asset-id="{{ $asset->id }}"
                               data-tip-code="{{ $hierarchyCode }}"
                               data-tip-name="{{ addslashes($asset->name) }}"
                               data-tip-status="{{ $asset->status }}"
                               @click="if(editDataMode){ $event.preventDefault(); openModal($event, {{ $row }}, {{ $col }}, {{ $asset->id }}, 'PV Module') }"
                               :class="editDataMode ? 'cursor-pointer ring-2 ring-blue-300' : 'hover:scale-[1.2] hover:z-10'"
                               class="pv-asset flex items-center justify-center w-[52px] h-[30px] rounded-[3px] transition-all duration-100 relative hover:ring-2 {{ $colorClass }}">
                                <span class="text-[7px] font-bold text-gray-800 leading-none select-none pointer-events-none">
                                    INV{{ str_pad($asset->string_number, 2, '0', STR_PAD_LEFT) }}-S{{ str_pad($asset->module_slot, 2, '0', STR_PAD_LEFT) }}
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
                            @endphp
                            <a href="{{ route('assets.show', $supportingAsset->id) }}"
                               draggable="true"
                               data-asset-id="{{ $supportingAsset->id }}"
                               data-tip-code="{{ $supportingAsset->asset_code }}"
                               data-tip-name="{{ addslashes($supportingAsset->name) }}"
                               data-tip-status="{{ $supportingAsset->status }}"
                               @click="if(editDataMode){ $event.preventDefault(); openModal($event, {{ $row }}, {{ $col }}, {{ $supportingAsset->id }}, '{{ $supportingAsset->category }}') }"
                               :class="editDataMode ? 'cursor-pointer ring-2 ring-blue-300' : 'hover:scale-[1.2] hover:z-10'"
                               class="pv-asset flex items-center justify-center w-[52px] h-[30px] rounded-[3px] transition-all duration-100 relative hover:ring-2 {{ $saColor }}">
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
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: 20,
                },
                {
                    label: 'Open',
                    data: openData,
                    backgroundColor: '#d1fae5',
                    borderRadius: 6,
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
                    backgroundColor: '#064e3b',
                    titleFont: { family: 'Outfit', size: 13 },
                    bodyFont: { family: 'Outfit', size: 12 },
                    padding: 12,
                    cornerRadius: 12,
                }
            },
            scales: {
                x: { 
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit', size: 11, weight: 'bold' }, color: '#9ca3af' }
                },
                y: { 
                    stacked: true,
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: { font: { family: 'Outfit', size: 11 }, color: '#9ca3af', stepSize: 5 }
                }
            }
        }
    });

    // PV Drag & Drop — event delegation, visual only until Simpan
    window.pvEditMode = false;
    let pvDrag = null;
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
        pvTip.innerHTML = `<p class="font-mono font-black" style="color:${sColor[a.dataset.tipStatus]||'#9ca3af'}">${a.dataset.tipCode}</p>
            <p class="mt-0.5 text-gray-200 font-medium">${a.dataset.tipName}</p>
            <p class="mt-1 text-[10px] uppercase tracking-widest font-bold" style="color:${sColor[a.dataset.tipStatus]||'#9ca3af'}">${(a.dataset.tipStatus||'').replaceAll('_',' ')}</p>`;
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
</script>
@endpush

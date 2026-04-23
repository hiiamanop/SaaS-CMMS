@extends('layouts.app')
@section('title', $maintenanceSchedule->title)

@section('breadcrumb')
<span class="text-gray-400">/</span>
<a href="{{ route('maintenance-schedules.index') }}" class="hover:text-gray-800">Schedules</a>
<span class="text-gray-400">/</span>
<span class="text-gray-700 font-medium">{{ $maintenanceSchedule->title }}</span>
@endsection

@section('content')
@php 
    $s = $maintenanceSchedule; 
    $statusColors = [
        'active' => 'bg-green-100 text-green-700 border-green-200',
        'inactive' => 'bg-gray-100 text-gray-700 border-gray-200'
    ];
@endphp

<div class="space-y-6 mx-auto pb-10">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('maintenance-schedules.index') }}" class="p-2.5 bg-white border border-gray-200 text-gray-400 hover:text-gray-600 hover:border-gray-300 rounded-xl shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $s->title }}</h1>
                    <span class="px-2.5 py-0.5 border {{ $statusColors[$s->status] ?? 'bg-gray-100' }} text-[10px] uppercase tracking-wider font-bold rounded-md shadow-sm">
                        {{ $s->status }}
                    </span>
                </div>
                <div class="flex items-center gap-2 mt-1 text-sm text-gray-500">
                    <span class="font-medium text-brand bg-blue-50 px-2 py-0.5 rounded">{{ ucfirst($s->type) }}</span>
                    <span>•</span>
                    <span>Dibuat pada {{ $s->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>
        @if(!auth()->user()->isTechnician())
        <div class="flex items-center gap-2">
            <a href="{{ route('maintenance-schedules.edit', $s) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Schedule
            </a>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        {{-- Details --}}
        <div class="space-y-6">
            {{-- Info Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Informasi Umum</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-6 gap-x-4">
                        @php 
                            $details = [
                                ['Lokasi PLTS', $s->location?->name ?? '—', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z', 'blue'],
                                ['Nama Trafo', $s->trafo_name ?? '—', 'M13 10V3L4 14h7v7l9-11h-7z', 'orange'],
                                ['Lokasi Inspeksi', $s->category ?? '—', 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2', 'teal'],
                                ['Frekuensi', ucfirst($s->frequency), 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z', 'purple'],
                                ['Next Due Date', $s->next_due_date ? $s->next_due_date->format('d M Y') : '—', 'M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'red'],
                                ['Teknisi', $s->technician?->name ?? '—', 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z', 'indigo'],
                            ];
                        @endphp
                        @foreach($details as [$label, $value, $icon, $color])
                        <div class="flex gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-{{ $color }}-50 flex items-center justify-center text-{{ $color }}-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="{{ $icon }}"/></svg>
                            </div>
                            <div>
                                <dt class="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{{ $label }}</dt>
                                <dd class="text-sm font-semibold text-gray-900">{{ $value }}</dd>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($s->notes)
                    <div class="mt-8 p-4 bg-yellow-50 border border-yellow-100 rounded-xl">
                        <h3 class="text-xs font-bold text-yellow-800 uppercase mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                            Catatan Tambahan
                        </h3>
                        <p class="text-sm text-yellow-900 leading-relaxed">{{ $s->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Inspection Items Card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Item Inspeksi & Lokasi</h2>
                    <span class="px-2 py-0.5 bg-gray-200 text-gray-700 text-[10px] font-bold rounded-md">{{ count((array)$s->item_pekerjaan) }} Items</span>
                </div>
                <div class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b border-gray-100">
                                    <th class="px-6 py-3 font-bold text-gray-500 text-[10px] uppercase">Lokasi/Item</th>
                                    <th class="px-6 py-3 font-bold text-gray-500 text-[10px] uppercase">Metode</th>
                                    <th class="px-6 py-3 font-bold text-gray-500 text-[10px] uppercase">Standar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @php
                                    $grouped = [];
                                    foreach((array)$s->item_pekerjaan as $item) {
                                        $lok = is_array($item) ? ($item['lokasi_inspeksi'] ?? 'Lainnya') : 'Lainnya';
                                        $grouped[$lok][] = $item;
                                    }
                                @endphp
                                @foreach($grouped as $lokasi => $groupItems)
                                    <tr class="bg-gray-50/30">
                                        <td colspan="3" class="px-6 py-2 text-xs font-bold text-blue-700 bg-blue-50/50 tracking-wide uppercase">{{ $lokasi }}</td>
                                    </tr>
                                    @foreach($groupItems as $item)
                                    @php
                                        $name    = is_array($item) ? ($item['name']    ?? '') : $item;
                                        $metode  = is_array($item) ? ($item['metode']  ?? '—') : '—';
                                        $standar = is_array($item) ? ($item['standar'] ?? '—') : '—';
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-800">{{ $name }}</td>
                                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $metode }}</td>
                                        <td class="px-6 py-4 text-gray-500 text-xs italic">{{ $standar }}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            </div>

            {{-- Checksheet History --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-widest">History Checksheet</h2>
                    <a href="{{ route('checksheet.index') }}" class="text-[10px] text-brand font-bold hover:underline">View All</a>
                </div>
                <div class="p-0 max-h-[400px] overflow-y-auto">
                    @if($s->checksheetSessions->where('status','submitted')->isEmpty())
                        <div class="p-8 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                            </div>
                            <p class="text-xs text-gray-400">Belum ada history checksheet yang selesai</p>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($s->checksheetSessions->where('status','submitted')->sortByDesc('submitted_at')->take(5) as $session)
                            <a href="{{ route('checksheet.show', $session) }}" class="flex items-center gap-3 p-4 hover:bg-gray-50 transition-colors group">
                                <div class="w-8 h-8 rounded-lg bg-teal-50 flex items-center justify-center text-teal-600 group-hover:bg-teal-100 transition-colors flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $session->period_label }}</p>
                                    <p class="text-[10px] text-gray-500 mt-0.5">{{ $session->submitted_at->format('d M Y') }} · {{ $session->submittedBy?->name ?? 'System' }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></svg>
                            </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Recent Work Orders --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-gray-900 uppercase tracking-widest">Recent Work Orders</h2>
                    <span class="px-1.5 py-0.5 bg-brand text-white text-[8px] font-bold rounded">{{ $s->workOrders->count() }}</span>
                </div>
                <div class="p-0">
                    @if($s->workOrders->isEmpty())
                        <div class="p-8 text-center text-xs text-gray-400">Tidak ada Work Order terkait</div>
                    @else
                        <div class="divide-y divide-gray-100 border-b border-gray-100">
                            @foreach($s->workOrders->sortByDesc('created_at')->take(3) as $wo)
                            @php $stc=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700']; @endphp
                            <a href="{{ route('work-orders.show', $wo) }}" class="block p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-[10px] font-bold text-brand">{{ $wo->wo_number }}</span>
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold uppercase {{ $stc[$wo->status] ?? '' }}">{{ $wo->status }}</span>
                                </div>
                                <p class="text-xs font-semibold text-gray-800 line-clamp-1 mb-1">{{ $wo->title }}</p>
                                <div class="flex items-center justify-between text-[10px] text-gray-400">
                                    <span>{{ $wo->due_date->format('d M Y') }}</span>
                                    <span class="truncate">{{ $wo->assignedTo?->name ?? 'Unassigned' }}</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        @if($s->workOrders->count() > 3)
                        <div class="p-2 bg-gray-50">
                            <button class="w-full py-1 text-[10px] font-bold text-gray-500 hover:text-brand transition-colors">View All Work Orders</button>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

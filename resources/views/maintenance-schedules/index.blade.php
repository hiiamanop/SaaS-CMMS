@extends('layouts.app')
@section('title','Maintenance Schedules')
@section('breadcrumb')
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">Maint. Schedule</span>
@endsection
@section('content')
@php
$freqLabels = [
    'weekly'    => ['label' => 'Mingguan',   'class' => 'bg-blue-100 text-blue-700'],
    'monthly'   => ['label' => 'Bulanan',    'class' => 'bg-purple-100 text-purple-700'],
    'quarterly' => ['label' => 'Semesteran', 'class' => 'bg-orange-100 text-orange-700'],
    'annually'  => ['label' => 'Tahunan',    'class' => 'bg-green-100 text-green-700'],
];
@endphp
<div class="space-y-5" x-data="{ view: '{{ request()->has('view') ? request('view') : 'list' }}' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Maintenance Schedule</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                @if($overdueCount > 0)
                    <span class="text-red-500 font-medium">{{ $overdueCount }} jadwal overdue</span>
                @else
                    Semua jadwal berjalan normal
                @endif
                · {{ $schedules->count() }} total jadwal preventive
            </p>
        </div>
        <div class="flex gap-2">
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button @click="view='list'" :class="view==='list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all">List</button>
                <button @click="view='calendar'" :class="view==='calendar' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500'"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all">Kalender</button>
            </div>
            @if(!auth()->user()->isTechnician())
            <a href="{{ route('maintenance-schedules.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Jadwal Baru
            </a>
            @endif
        </div>
    </div>

    {{-- Filter --}}
    <div x-show="view==='list'" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="asset_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Aset</option>
                @foreach($assets as $a)
                <option value="{{ $a->id }}" {{ request('asset_id') == $a->id ? 'selected' : '' }}>{{ $a->name }}</option>
                @endforeach
            </select>
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="filter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua</option>
                <option value="overdue" {{ request('filter') == 'overdue' ? 'selected' : '' }}>Overdue Saja</option>
            </select>
            {{-- Month filter --}}
            <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Bulan</option>
                @foreach(['Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'Mei'=>5,'Jun'=>6,'Jul'=>7,'Agu'=>8,'Sep'=>9,'Okt'=>10,'Nov'=>11,'Des'=>12] as $label => $num)
                <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            {{-- Year filter --}}
            <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Tahun</option>
                @foreach(range(now()->year - 1, now()->year + 2) as $y)
                <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            {{-- Date range --}}
            <div class="flex items-center gap-1.5">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       placeholder="Dari tanggal"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-gray-400 text-sm">—</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       placeholder="Sampai tanggal"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('maintenance-schedules.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- List View --}}
    <div x-show="view==='list'" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($schedules->isEmpty())
        <div class="py-20 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <p class="text-gray-400 font-medium">Belum ada jadwal maintenance</p>
            @if(!auth()->user()->isTechnician())
            <a href="{{ route('maintenance-schedules.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                Buat jadwal pertama
            </a>
            @endif
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-8">No</th>
                        <th class="px-4 py-3 text-left">Nama Alat / Item Pekerjaan</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-left">Aset</th>
                        <th class="px-4 py-3 text-center">Frekuensi</th>
                        <th class="px-4 py-3 text-center">Shutdown</th>
                        <th class="px-4 py-3 text-center">Minggu Terjadwal</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @php $no = 1; $prevCat = null; @endphp
                @foreach($schedules as $s)
                @php
                    $fl      = $freqLabels[$s->frequency] ?? ['label' => ucfirst($s->frequency), 'class' => 'bg-gray-100 text-gray-600'];
                    $weekCount = count($s->planned_weeks ?? []);
                @endphp

                {{-- Separator kategori --}}
                @if($s->category !== $prevCat)
                @php $prevCat = $s->category; @endphp
                <tr class="bg-gray-100">
                    <td colspan="10" class="px-4 py-2 text-xs font-bold text-gray-600 uppercase tracking-wide">
                        {{ $s->category ?: 'Lainnya' }}
                    </td>
                </tr>
                @endif

                <tr class="hover:bg-blue-50/20">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $no++ }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $s->equipment_name ?: $s->title }}</p>
                        @if($s->item_pekerjaan)
                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $s->item_pekerjaan_text }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-sm">{{ $s->category ?: '—' }}</td>
                    <td class="px-4 py-3 text-gray-600 text-sm">{{ $s->asset->name }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $fl['class'] }}">{{ $fl['label'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->shutdown_required)
                            <span class="inline-flex items-center gap-1 text-orange-600 font-medium text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                {{ $s->shutdown_duration_hours }}h
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($weekCount > 0)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600">
                                {{ $weekCount }}x / tahun
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->status === 'active')
                            <span class="inline-block w-2 h-2 rounded-full bg-green-500" title="Active"></span>
                        @else
                            <span class="inline-block w-2 h-2 rounded-full bg-gray-300" title="Inactive"></span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('maintenance-schedules.show', $s) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            @if(!auth()->user()->isTechnician())
                            <a href="{{ route('maintenance-schedules.edit', $s) }}"
                               class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <button @click="$dispatch('open-delete',{action:'{{ route('maintenance-schedules.destroy',$s) }}',message:'Hapus jadwal {{ addslashes($s->equipment_name ?: $s->title) }}?'})"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Calendar View --}}
    <div x-show="view==='calendar'"
         x-effect="if(view==='calendar'){ $nextTick(()=>{ setTimeout(()=>window.schedCalendar&&window.schedCalendar.updateSize(),50); }); }"
         class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div id="scheduleCalendar" style="min-height:540px"></div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calEl = document.getElementById('scheduleCalendar');
    var calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        events: @json($calendarEvents),
        eventClick: function (info) { if (info.event.url) { window.location.href = info.event.url; info.jsEvent.preventDefault(); } },
        contentHeight: 540,
        expandRows: true,
        handleWindowResize: true,
    });
    calendar.render();
    window.schedCalendar = calendar;
});
</script>
@endpush

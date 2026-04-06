@extends('layouts.app')
@section('title','Maintenance Schedules')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Schedules</span>@endsection
@section('content')
<div class="space-y-5" x-data="{view:'list'}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Maintenance Schedules</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $overdueCount > 0 ? $overdueCount.' schedule(s) overdue' : 'All schedules on track' }}</p>
        </div>
        <div class="flex gap-2">
            <div class="flex bg-gray-100 rounded-lg p-1">
                <button @click="view='list'" :class="view==='list'?'bg-white shadow-sm text-gray-900':'text-gray-500'" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all">List</button>
                <button @click="view='calendar'" :class="view==='calendar'?'bg-white shadow-sm text-gray-900':'text-gray-500'" class="px-3 py-1.5 rounded-md text-sm font-medium transition-all">Calendar</button>
            </div>
            @if(!auth()->user()->isTechnician())
            <a href="{{ route('maintenance-schedules.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>New Schedule
            </a>
            @endif
        </div>
    </div>

    {{-- Filters (list view only) --}}
    <div x-show="view==='list'" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="asset_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Aset</option>
                @foreach($assets as $a)<option value="{{ $a->id }}" {{ request('asset_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
            </select>
            <select name="filter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="overdue" {{ request('filter')=='overdue'?'selected':'' }}>Overdue Saja</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('maintenance-schedules.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- List view — format tahunan seperti checksheet mingguan --}}
    @php
    $months     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $grouped    = $schedules->groupBy('category');
    $freqLabels = [
        'weekly'    => ['label'=>'Mingguan',   'class'=>'bg-blue-100 text-blue-700'],
        'monthly'   => ['label'=>'Bulanan',    'class'=>'bg-purple-100 text-purple-700'],
        'quarterly' => ['label'=>'Semesteran', 'class'=>'bg-orange-100 text-orange-700'],
        'annually'  => ['label'=>'Tahunan',    'class'=>'bg-green-100 text-green-700'],
        'daily'     => ['label'=>'Harian',     'class'=>'bg-gray-100 text-gray-700'],
        'custom'    => ['label'=>'Custom',     'class'=>'bg-gray-100 text-gray-700'],
    ];
    @endphp
    <div x-show="view==='list'" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
        @if($schedules->isEmpty())
        <div class="py-16 text-center text-gray-400">Tidak ada jadwal ditemukan</div>
        @else
        <table class="text-xs border-collapse" style="min-width:1200px">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:32px">No</th>
                    <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:140px">Nama Alat/Mesin</th>
                    <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:180px">Item Pekerjaan</th>
                    <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:80px">Frekuensi</th>
                    <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:56px">Shutdown</th>
                    @foreach($months as $m)
                    <th class="border border-gray-300 px-1 py-1 text-center font-bold" colspan="4">{{ $m }}</th>
                    @endforeach
                    <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:64px">Aksi</th>
                </tr>
                <tr class="bg-gray-50">
                    <th class="border border-gray-300" colspan="5"></th>
                    @foreach($months as $m)
                        @foreach(['W1','W2','W3','W4'] as $w)
                        <th class="border border-gray-300 px-0.5 py-1 text-center text-gray-500">{{ $w }}</th>
                        @endforeach
                    @endforeach
                    <th class="border border-gray-300"></th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($grouped as $cat => $items)
                <tr style="background:#fefce8">
                    <td colspan="{{ 6 + 48 }}"
                        class="border border-gray-300 px-2 py-1.5 font-bold text-yellow-900">
                        {{ chr(64 + $no++) }}. {{ strtoupper($cat ?: 'Lainnya') }}
                    </td>
                </tr>
                @php $itemNo = 1; @endphp
                @foreach($items as $s)
                @php
                    $plannedSet = collect($s->planned_weeks ?? [])->map(fn($p) => $p['month'].'-'.$p['week'])->all();
                    $overdue    = $s->next_due_date->isPast() && $s->status === 'active';
                    $fl         = $freqLabels[$s->frequency] ?? ['label'=>ucfirst($s->frequency),'class'=>'bg-gray-100 text-gray-700'];
                @endphp
                <tr class="hover:bg-blue-50/30 {{ $overdue ? 'bg-red-50/20' : '' }}">
                    <td class="border border-gray-300 px-1 py-1 text-center text-gray-500">{{ $itemNo++ }}</td>
                    <td class="border border-gray-300 px-2 py-1 font-medium text-gray-900">
                        {{ $s->equipment_name ?: $s->asset->name }}
                        @if($overdue)<br><span class="text-red-500 text-xs font-normal">Overdue</span>@endif
                    </td>
                    <td class="border border-gray-300 px-2 py-1 text-gray-700">{{ $s->item_pekerjaan ?: $s->title }}</td>
                    <td class="border border-gray-300 px-1 py-1 text-center">
                        <span class="inline-block px-1.5 py-0.5 rounded-full text-xs font-medium {{ $fl['class'] }}">{{ $fl['label'] }}</span>
                    </td>
                    <td class="border border-gray-300 px-1 py-1 text-center {{ $s->shutdown_required ? 'text-orange-600 font-semibold' : 'text-gray-400' }}">
                        {{ $s->shutdown_required ? 'Y' : 'N' }}
                    </td>
                    @foreach(range(1,12) as $month)
                        @foreach(range(1,4) as $week)
                        @php $isPlanned = in_array($month.'-'.$week, $plannedSet); @endphp
                        <td class="border border-gray-300 px-0.5 py-1 text-center">
                            @if($isPlanned)
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-500 mx-auto" title="Direncanakan"></span>
                            @endif
                        </td>
                        @endforeach
                    @endforeach
                    <td class="border border-gray-300 px-1 py-1 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('maintenance-schedules.show',$s) }}"
                               class="p-1 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            @if(!auth()->user()->isTechnician())
                            <a href="{{ route('maintenance-schedules.edit',$s) }}"
                               class="p-1 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>

        {{-- Legenda --}}
        <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap gap-4 text-xs text-gray-600">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span> Minggu Terencana
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-400"></span> Overdue
            </span>
        </div>
        @endif
    </div>

    {{-- Calendar view --}}
    <div x-show="view==='calendar'"
         x-effect="if(view==='calendar') { $nextTick(() => { setTimeout(() => window.schedCalendar && window.schedCalendar.updateSize(), 50); }); }"
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
document.addEventListener('DOMContentLoaded', function() {
    var calEl = document.getElementById('scheduleCalendar');
    var calendar = new FullCalendar.Calendar(calEl, {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        events: @json($calendarEvents),
        eventClick: function(info) { if(info.event.url) { window.location.href = info.event.url; info.jsEvent.preventDefault(); } },
        contentHeight: 540,
        expandRows: true,
        handleWindowResize: true,
    });
    calendar.render();
    window.schedCalendar = calendar;
});
</script>
@endpush

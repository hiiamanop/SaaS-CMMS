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
                <option value="">All Assets</option>
                @foreach($assets as $a)<option value="{{ $a->id }}" {{ request('asset_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
            </select>
            <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="preventive" {{ request('type')=='preventive'?'selected':'' }}>Preventive</option>
                <option value="corrective" {{ request('type')=='corrective'?'selected':'' }}>Corrective</option>
            </select>
            <select name="filter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All</option>
                <option value="overdue" {{ request('filter')=='overdue'?'selected':'' }}>Overdue Only</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('maintenance-schedules.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- List view --}}
    <div x-show="view==='list'" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($schedules->isEmpty())
        <div class="py-16 text-center text-gray-400">No schedules found</div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3 text-left">Title</th><th class="px-5 py-3 text-left">Asset</th><th class="px-5 py-3 text-left">Type</th><th class="px-5 py-3 text-left">Frequency</th><th class="px-5 py-3 text-left">Next Due</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($schedules as $s)
            @php $overdue = $s->next_due_date->isPast() && $s->status==='active'; @endphp
            <tr class="hover:bg-gray-50 {{ $overdue?'bg-red-50/30':'' }}">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('maintenance-schedules.show',$s) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $s->title }}</a>
                        @if($overdue)<span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-medium">Overdue</span>@endif
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $s->asset->name }}</td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $s->type==='preventive'?'bg-blue-100 text-blue-700':'bg-orange-100 text-orange-700' }}">{{ ucfirst($s->type) }}</span></td>
                <td class="px-5 py-3 text-gray-500 capitalize">{{ $s->frequency }}</td>
                <td class="px-5 py-3 {{ $overdue?'text-red-600 font-medium':'text-gray-600' }}">{{ $s->next_due_date->format('M d, Y') }}</td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $s->status==='active'?'bg-green-100 text-green-700':'bg-gray-100 text-gray-500' }}">{{ ucfirst($s->status) }}</span></td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('maintenance-schedules.show',$s) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></a>
                        @if(!auth()->user()->isTechnician())
                        <a href="{{ route('maintenance-schedules.edit',$s) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('maintenance-schedules.destroy',$s) }}',message:'Delete schedule {{ addslashes($s->title) }}?'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $schedules->links() }}</div>
        @endif
    </div>

    {{-- Calendar view --}}
    <div x-show="view==='calendar'" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div id="scheduleCalendar"></div>
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
        height: 600,
    });
    calendar.render();
});
</script>
@endpush

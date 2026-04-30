@extends('layouts.app')
@section('title','Timeline')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<style>
    .fc-day-add-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 24px;
        height: 24px;
        background: #3b82f6;
        color: white;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        z-index: 5;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.1s;
    }
    .fc-day-add-btn:hover {
        transform: scale(1.1);
        background: #2563eb;
    }
    .fc-day:hover .fc-day-add-btn {
        display: flex;
    }
</style>
@endpush

@section('breadcrumb')
<span class="text-gray-400">/</span>
<span class="text-gray-700 font-medium">Timeline</span>
@endsection

@section('content')
<div class="space-y-5" x-data="{ view: 'list' }">

    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Timeline</h1>
            <p class="text-sm text-gray-500 mt-0.5">Chronological feed of all maintenance activities</p>
        </div>
        {{-- View Toggle --}}
        <div class="inline-flex rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <button @click="view='list'"
                :class="view==='list' ? 'bg-brand-dark text-white font-bold' : 'text-gray-600 hover:bg-opacity-90'"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/>
                    <line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/>
                    <line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>
                </svg>
                List
            </button>
            <button @click="view='calendar'; $nextTick(()=>window.dispatchEvent(new Event('fc-render')))"
                :class="view==='calendar' ? 'bg-brand-dark text-white font-bold' : 'text-gray-600 hover:bg-opacity-90'"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition-colors border-l border-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/>
                    <line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                </svg>
                Calendar
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="date_from" type="date" value="{{ request('date_from') }}"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <input name="date_to" type="date" value="{{ request('date_to') }}"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <button type="submit" class="px-4 py-2 bg-brand-dark text-white font-bold rounded-lg text-sm font-medium">Filter</button>
            <a href="{{ route('timeline.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Reset</a>
        </form>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>Preventive</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span>Corrective</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span>Completed / Closed</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-teal-500 inline-block"></span>Checksheet</span>
    </div>

    {{-- LIST VIEW --}}
    <div x-show="view==='list'" x-transition>
        @if($timeline->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center shadow-sm">
            <p class="text-gray-400">No timeline entries found</p>
        </div>
        @else
        <div class="relative">
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200 z-0"></div>
            <div class="space-y-4 pl-16">
            @foreach($timeline as $item)
            @php
            $isPreventive = str_starts_with($item['wo_type'] ?? '', 'preventive');
            $dotColor = match(true) {
                $item['type']==='checksheet' => '#14b8a6',
                in_array($item['status'],['completed','closed']) => '#10b981',
                $isPreventive => '#3b82f6',
                default => '#f97316',
            };
            $borderColor = match(true) {
                $item['type']==='checksheet' => 'border-teal-100',
                in_array($item['status'],['completed','closed']) => 'border-green-100',
                $isPreventive => 'border-blue-100',
                default => 'border-orange-100',
            };
            $pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'];
            @endphp
            <div class="relative">
                <div class="absolute -left-10 top-4 w-3 h-3 rounded-full ring-2 ring-white z-10 {{ $item['deleted'] ? 'opacity-40' : '' }}"
                     style="background-color:{{ $dotColor }}"></div>
                @if($item['url'])
                <a href="{{ $item['url'] }}" class="block bg-white rounded-xl border {{ $borderColor }} shadow-sm p-4 hover:shadow-md transition-shadow">
                @else
                <div class="block bg-white rounded-xl border border-gray-200 shadow-sm p-4 opacity-60">
                @endif
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                    {{ $item['type']==='work_order'?'bg-gray-100 text-gray-600':($item['type']==='checksheet'?'bg-teal-100 text-teal-700':'bg-green-100 text-green-700') }}">
                                    {{ $item['type']==='work_order'?'Work Order':($item['type']==='checksheet'?'Checksheet':'Maint. Record') }}
                                </span>
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $isPreventive?'bg-blue-100 text-blue-700':'bg-orange-100 text-orange-700' }}">
                                    {{ ucfirst(str_replace('_',' ',$item['wo_type'])) }}
                                </span>
                                @if($item['priority'])
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $pColors[$item['priority']]??'' }}">{{ ucfirst($item['priority']) }}</span>
                                @endif
                                @if($item['deleted'])
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-500">Dihapus</span>
                                @endif
                            </div>
                            <p class="font-medium truncate {{ $item['deleted'] ? 'line-through text-gray-400' : 'text-gray-900' }}">{{ $item['title'] }}</p>
                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                <span>{{ $item['asset'] }}</span>
                                <span>{{ $item['person'] }}</span>
                                @if(isset($item['executor']))
                                <span class="bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100 italic">Oleh: {{ $item['executor'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="block text-sm font-bold text-gray-900 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item['date'])->format('M d, Y') }}
                            </span>
                            <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">
                                {{ \Carbon\Carbon::parse($item['date'])->format('H:i') }}
                            </span>
                        </div>
                    </div>
                @if($item['url'])
                </a>
                @else
                </div>
                @endif
            </div>
            @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- CALENDAR VIEW --}}
    <div x-show="view==='calendar'" x-transition style="display:none">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div id="timeline-calendar"></div>
        </div>

        {{-- Event Tooltip Popup --}}
        <div id="fc-tooltip"
            class="hidden fixed z-50 bg-white rounded-xl shadow-xl border border-gray-200 p-4 w-72 pointer-events-none">
            <p id="fc-tooltip-title" class="text-sm font-semibold text-gray-900 mb-2"></p>
            <div class="space-y-1 text-xs text-gray-500">
                <div class="flex gap-1"><span class="font-medium text-gray-700">Asset:</span><span id="fc-tooltip-asset"></span></div>
                <div class="flex gap-1"><span class="font-medium text-gray-700">Person:</span><span id="fc-tooltip-person"></span></div>
                <div class="flex gap-1"><span class="font-medium text-gray-700">Type:</span><span id="fc-tooltip-type" class="capitalize"></span></div>
                <div class="flex gap-1"><span class="font-medium text-gray-700">Status:</span><span id="fc-tooltip-status" class="capitalize"></span></div>
            </div>
            <p class="text-xs text-brand mt-2">Click to open</p>
        </div>
    </div>

    {{-- Selection Menu for Calendar --}}
    <div id="calendar-menu" class="hidden fixed z-[100] bg-white rounded-xl shadow-2xl border border-gray-200 p-2 w-48 overflow-hidden">
        <div class="px-3 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-50 mb-1">Create New Event</div>
        <button onclick="calendarMenuAction('wo')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Work Order
        </button>
        <button onclick="calendarMenuAction('sched')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-brand-50 hover:text-brand rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            Maint. Schedule
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
const calendarEvents = @json($calendarEvents);
const tooltip = document.getElementById('fc-tooltip');

let calendarInitialized = false;

window.addEventListener('fc-render', () => {
    if (calendarInitialized) return;
    calendarInitialized = true;

    const cal = new FullCalendar.Calendar(document.getElementById('timeline-calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        selectable: true,
        dateClick(info) {
            const menu = document.getElementById('calendar-menu');
            menu.style.left = info.jsEvent.clientX + 'px';
            menu.style.top = info.jsEvent.clientY + 'px';
            menu.classList.remove('hidden');
            window.selectedCalendarDate = info.dateStr;
            
            // Close menu on click outside
            const closeMenu = (e) => {
                if (!menu.contains(e.target)) {
                    menu.classList.add('hidden');
                    document.removeEventListener('mousedown', closeMenu);
                }
            };
            setTimeout(() => document.addEventListener('mousedown', closeMenu), 10);
        },
        dayCellDidMount(info) {
            const btn = document.createElement('div');
            btn.className = 'fc-day-add-btn';
            btn.innerHTML = '+';
            info.el.style.position = 'relative';
            info.el.appendChild(btn);
            
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const menu = document.getElementById('calendar-menu');
                menu.style.left = e.clientX + 'px';
                menu.style.top = e.clientY + 'px';
                menu.classList.remove('hidden');
                window.selectedCalendarDate = info.date.toISOString().split('T')[0];
                
                const closeMenu = (ev) => {
                    if (!menu.contains(ev.target)) {
                        menu.classList.add('hidden');
                        document.removeEventListener('mousedown', closeMenu);
                    }
                };
                setTimeout(() => document.addEventListener('mousedown', closeMenu), 10);
            });
        },
        events: calendarEvents,
        eventClick(info) {
            info.jsEvent.preventDefault();
            window.location.href = info.event.url;
        },
        eventMouseEnter(info) {
            const p = info.event.extendedProps;
            document.getElementById('fc-tooltip-title').textContent  = info.event.title;
            document.getElementById('fc-tooltip-asset').textContent  = p.asset;
            document.getElementById('fc-tooltip-person').textContent = p.person;
            document.getElementById('fc-tooltip-type').textContent   = p.wo_type;
            document.getElementById('fc-tooltip-status').textContent = p.status;
            tooltip.classList.remove('hidden');
        },
        eventMouseLeave() {
            tooltip.classList.add('hidden');
        },
        eventDidMount(info) {
            // Position tooltip on mousemove over event
            info.el.addEventListener('mousemove', (e) => {
                tooltip.style.left = (e.clientX + 14) + 'px';
                tooltip.style.top  = (e.clientY + 14) + 'px';
            });
        },
        height: 'auto',
        dayMaxEvents: 4,
        eventDisplay: 'block',
        eventBorderColor: 'transparent',
    });

    cal.render();
});

function calendarMenuAction(type) {
    const date = window.selectedCalendarDate;
    const menu = document.getElementById('calendar-menu');
    menu.classList.add('hidden');
    
    if (type === 'wo') {
        window.location.href = "{{ route('work-orders.create') }}?due_date=" + date;
    } else {
        window.location.href = "{{ route('maintenance-schedules.create') }}?start_date=" + date;
    }
}
</script>
@endpush

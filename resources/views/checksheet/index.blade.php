@extends('layouts.app')

@section('title', 'Checksheet')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Checksheet</h1>
            <p class="text-sm text-gray-500 mt-1">Inspeksi kondisi PLTS berkala berdasarkan jadwal maintenance</p>
        </div>
        @if(!auth()->user()->isTechnician())
        <a href="{{ route('checksheet.templates.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Kelola Template
        </a>
        @endif
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="flex flex-col sm:flex-row gap-4">
            {{-- Schedule filter tabs --}}
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1 flex-shrink-0">
                @foreach(['all'=>'Semua','today'=>'Hari Ini','week'=>'Minggu Ini','month'=>'Bulan Ini','overdue'=>'Terlewat'] as $val => $label)
                <a href="{{ route('checksheet.index', array_merge(request()->except('schedule_filter'), ['schedule_filter' => $val])) }}"
                   class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap
                          {{ $scheduleFilter === $val
                              ? ($val === 'overdue' ? 'bg-red-500 shadow-sm text-white' : 'bg-white shadow-sm text-gray-900')
                              : ($val === 'overdue' ? 'text-red-500 hover:text-red-600' : 'text-gray-500 hover:text-gray-700') }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            {{-- Session month/year filter --}}
            <form method="GET" action="{{ route('checksheet.index') }}" class="flex items-center gap-2 flex-wrap">
                <input type="hidden" name="schedule_filter" value="{{ $scheduleFilter }}">
                <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Bulan</option>
                    @foreach(['Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'Mei'=>5,'Jun'=>6,'Jul'=>7,'Agu'=>8,'Sep'=>9,'Okt'=>10,'Nov'=>11,'Des'=>12] as $lbl => $num)
                    <option value="{{ $num }}" {{ $sessionMonth == $num ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tahun</option>
                    @foreach(range(now()->year - 1, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $sessionYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Terapkan</button>
                <a href="{{ route('checksheet.index') }}" class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Reset</a>
            </form>
        </div>
    </div>

    @if($schedules->isEmpty())
    <div class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
        <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-600 font-medium">
            @if($scheduleFilter !== 'all')
                Tidak ada jadwal yang cocok dengan filter "{{ ['today'=>'Hari Ini','week'=>'Minggu Ini','month'=>'Bulan Ini','overdue'=>'Terlewat'][$scheduleFilter] ?? $scheduleFilter }}"
            @else
                Belum ada checksheet aktif
            @endif
        </p>
        <p class="text-gray-400 text-sm mt-1">
            @if($scheduleFilter !== 'all')
                <a href="{{ route('checksheet.index') }}" class="text-blue-600 hover:underline">Lihat semua jadwal</a>
            @else
                Buat template checksheet terlebih dahulu untuk setiap jadwal maintenance
            @endif
        </p>
    </div>
    @else

    @php
        $freqLabels = ['weekly'=>'Mingguan','monthly'=>'Bulanan','quarterly'=>'Semesteran','annually'=>'Tahunan'];
        $freqColors = ['weekly'=>'blue','monthly'=>'purple','quarterly'=>'orange','annually'=>'green'];
        $grouped = $schedules->groupBy('category');
    @endphp

    @foreach($grouped as $category => $catSchedules)
    <div>
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            {{ $category }}
        </h2>

        <div class="space-y-4">
        @foreach($catSchedules as $schedule)
        @php
            $freq     = $schedule->frequency;
            $color    = $freqColors[$freq] ?? 'gray';
            $sessions = $schedule->checksheetSessions; // already sorted by closest due date from controller
            $plannedWeeksJson = json_encode($schedule->planned_weeks ?? []);
        @endphp

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            {{-- Schedule header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                            @if($color==='blue') bg-blue-100 text-blue-700
                            @elseif($color==='purple') bg-purple-100 text-purple-700
                            @elseif($color==='orange') bg-orange-100 text-orange-700
                            @else bg-green-100 text-green-700 @endif">
                            {{ $freqLabels[$freq] ?? $freq }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-semibold text-gray-900 truncate">{{ $schedule->equipment_name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $schedule->item_pekerjaan_text }}</p>
                    </div>
                    @if($schedule->technician)
                    <div class="hidden sm:flex items-center gap-1.5 text-xs text-gray-400 ml-2 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ $schedule->technician->name }}
                    </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                    @if($schedule->checklistTemplates->count() > 0)
                    <span class="text-xs text-gray-400">{{ $schedule->checklistTemplates->count() }} item</span>
                    @else
                    <span class="text-xs text-orange-400">Belum ada template</span>
                    @endif
                    <button onclick="openNewSession({{ $schedule->id }}, '{{ addslashes($schedule->equipment_name) }}', '{{ $schedule->frequency }}', {{ $plannedWeeksJson }})"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Sesi
                    </button>
                </div>
            </div>

            {{-- Sessions list --}}
            @if($sessions->isEmpty())
            <div class="px-5 py-5 text-sm text-gray-400 text-center">
                @if($sessionMonth || $sessionYear != now()->year)
                    Tidak ada sesi pada periode ini.
                @else
                    Belum ada sesi checksheet. Klik "Buat Sesi" untuk mulai.
                @endif
            </div>
            @else
            @php $totalSess = $sessions->count(); @endphp
            <div x-data="{ expanded: false }">
                <div :class="expanded ? 'max-h-72 overflow-y-auto' : ''" class="divide-y divide-gray-50">
                    @foreach($sessions as $si => $session)
                    @php
                        $dueDate = match($freq) {
                            'weekly'    => \Carbon\Carbon::createFromDate($session->year, $session->month ?? 1, 1)->addWeeks($session->week_number ?? 1)->subDay(),
                            'monthly'   => \Carbon\Carbon::createFromDate($session->year, $session->month ?? 1, 1)->endOfMonth(),
                            'quarterly' => ($session->semester == 1) ? \Carbon\Carbon::createFromDate($session->year, 6, 30) : \Carbon\Carbon::createFromDate($session->year, 12, 31),
                            'annually'  => \Carbon\Carbon::createFromDate($session->year, 12, 31),
                            default     => now(),
                        };
                        $isOverdue = $dueDate->isPast() && $session->status !== 'submitted';
                        $total  = $schedule->checklistTemplates->count();
                        $filled = $session->results()->whereNotNull('result')->count();
                        $pct    = $total > 0 ? round(($filled / $total) * 100) : 0;
                    @endphp
                    <a href="{{ $session->status === 'submitted' ? route('checksheet.show', $session) : route('checksheet.fill', $session) }}"
                       x-show="expanded || {{ $si }} < 5"
                       class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $session->period_label }} {{ $session->year }}</p>
                                <p class="text-xs {{ $isOverdue ? 'text-red-400 font-medium' : 'text-gray-400' }}">
                                    Due: {{ $dueDate->format('d M Y') }}
                                    @if($isOverdue) · Terlambat @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0 ml-3">
                            @if($session->status === 'submitted')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Submitted
                            </span>
                            @elseif($total === 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                Menunggu template
                            </span>
                            @else
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full {{ $pct > 0 ? 'bg-blue-500' : 'bg-gray-300' }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $filled }}/{{ $total }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $isOverdue ? 'bg-red-100 text-red-600' : ($pct > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $isOverdue ? 'Overdue' : ($pct > 0 ? 'Draft' : 'Baru') }}
                                </span>
                            </div>
                            @endif
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                    @endforeach
                </div>

                @if($totalSess > 5)
                <div class="border-t border-gray-50 px-5 py-2.5 text-center">
                    <button @click="expanded = !expanded"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                        <span x-show="!expanded">
                            <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            +{{ $totalSess - 5 }} sesi lainnya
                        </span>
                        <span x-show="expanded" x-cloak>
                            <svg class="w-3.5 h-3.5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            Sembunyikan
                        </span>
                    </button>
                </div>
                @endif
            </div>
            @endif
        </div>
        @endforeach
        </div>
    </div>
    @endforeach

    @endif
</div>

{{-- Modal Buat Sesi Baru --}}
<div id="newSessionModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" style="display:none!important">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="font-semibold text-gray-900" id="modalTitle">Buat Sesi Checksheet</h3>
                <p class="text-xs text-gray-500 mt-0.5" id="modalSubtitle"></p>
            </div>
            <button onclick="closeNewSession()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('checksheet.store') }}" method="POST" class="px-6 py-4 space-y-4">
            @csrf
            <input type="hidden" name="maintenance_schedule_id" id="modalScheduleId">
            <input type="hidden" name="year" id="modalYear" value="{{ $currentYear }}">
            <input type="hidden" name="month" id="modalMonthHidden">
            <input type="hidden" name="week_number" id="modalWeekHidden">

            {{-- Weekly: has planned_weeks → single combo select --}}
            <div id="weeklyPlanned" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Jadwal Minggu</label>
                <select id="plannedWeekSelect" onchange="onPlannedWeekChange(this)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </select>
                <p class="text-xs text-gray-400 mt-1">Hanya minggu yang ada di jadwal yang ditampilkan</p>
            </div>

            {{-- Weekly: no planned_weeks → free pick --}}
            <div id="weeklyFree" style="display:none">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select id="weeklyMonthSel" onchange="document.getElementById('modalMonthHidden').value=this.value"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @foreach(['Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'Mei'=>5,'Jun'=>6,'Jul'=>7,'Agu'=>8,'Sep'=>9,'Okt'=>10,'Nov'=>11,'Des'=>12] as $label=>$num)
                            <option value="{{ $num }}" {{ $currentMonth === $num ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minggu ke-</label>
                        <select id="weeklyWeekSel" onchange="document.getElementById('modalWeekHidden').value=this.value"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="1">Minggu 1</option>
                            <option value="2">Minggu 2</option>
                            <option value="3">Minggu 3</option>
                            <option value="4">Minggu 4</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="monthlyFields" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select id="monthlyMonthSel" onchange="document.getElementById('modalMonthHidden').value=this.value"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach(['Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'Mei'=>5,'Jun'=>6,'Jul'=>7,'Agu'=>8,'Sep'=>9,'Okt'=>10,'Nov'=>11,'Des'=>12] as $label=>$num)
                    <option value="{{ $num }}" {{ $currentMonth === $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div id="semesterFields" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                <select name="semester" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="1">Semester 1 (Jan–Jun)</option>
                    <option value="2">Semester 2 (Jul–Des)</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Buat Sesi & Mulai Isi
                </button>
                <button type="button" onclick="closeNewSession()" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const _monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const _currentMonth = {{ $currentMonth }};
const _currentYear  = {{ $currentYear }};

function openNewSession(scheduleId, name, frequency, plannedWeeks) {
    document.getElementById('modalScheduleId').value = scheduleId;
    document.getElementById('modalSubtitle').textContent = name;
    document.getElementById('modalYear').value = _currentYear;

    // Hide all field groups
    ['weeklyPlanned','weeklyFree','monthlyFields','semesterFields'].forEach(id => {
        document.getElementById(id).style.display = 'none';
    });
    // Reset hidden fields
    document.getElementById('modalMonthHidden').value = '';
    document.getElementById('modalWeekHidden').value  = '';

    if (frequency === 'weekly') {
        if (plannedWeeks && plannedWeeks.length > 0) {
            // Populate combo select with valid planned week+month combos
            const sel = document.getElementById('plannedWeekSelect');
            sel.innerHTML = '';
            plannedWeeks.forEach(pw => {
                const opt = document.createElement('option');
                opt.value = pw.month + '_' + pw.week;
                opt.textContent = _monthNames[pw.month - 1] + ' — Minggu ' + pw.week;
                // Pre-select current month/week if available
                if (pw.month === _currentMonth) opt.selected = true;
                sel.appendChild(opt);
            });
            onPlannedWeekChange(sel);
            document.getElementById('weeklyPlanned').style.display = '';
        } else {
            // Free pick
            document.getElementById('weeklyMonthSel').value = _currentMonth;
            document.getElementById('weeklyWeekSel').value  = '1';
            document.getElementById('modalMonthHidden').value = _currentMonth;
            document.getElementById('modalWeekHidden').value  = '1';
            document.getElementById('weeklyFree').style.display = '';
        }
    } else if (frequency === 'monthly') {
        document.getElementById('monthlyMonthSel').value = _currentMonth;
        document.getElementById('modalMonthHidden').value = _currentMonth;
        document.getElementById('monthlyFields').style.display = '';
    } else if (frequency === 'quarterly') {
        document.getElementById('semesterFields').style.display = '';
    }
    // annually: no extra fields needed

    document.getElementById('newSessionModal').style.removeProperty('display');
}

function onPlannedWeekChange(sel) {
    const parts = sel.value.split('_');
    document.getElementById('modalMonthHidden').value = parts[0];
    document.getElementById('modalWeekHidden').value  = parts[1];
}

function closeNewSession() {
    document.getElementById('newSessionModal').style.setProperty('display','none','important');
}
</script>
@endpush
@endsection

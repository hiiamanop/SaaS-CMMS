@php
$months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$grouped = $schedules->groupBy('category');
$now = \Carbon\Carbon::now();
@endphp

{{-- Penjelasan tabel --}}
<div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
    <p class="font-semibold mb-1">Tentang Tabel Schedule Maintenance</p>
    <p class="text-blue-700 leading-relaxed">
        Tabel ini merupakan rekap tahunan jadwal preventive maintenance seluruh peralatan PLTS dalam format minggu per bulan (W1–W4 × 12 bulan = 48 kolom).
        Setiap alat menampilkan <strong>2 baris</strong>: <strong>Renc.</strong> (Rencana — jadwal yang sudah direncanakan, ditandai
        <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500 align-middle"></span> biru) dan
        <strong>Real.</strong> (Realisasi — hasil aktual:
        <span class="text-green-600 font-bold">✓</span> selesai tepat waktu,
        <span class="text-orange-500 font-bold">✓</span> selesai terlambat,
        <span class="text-red-600 font-bold">✗</span> terlewat/belum dikerjakan).
    </p>
</div>

<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <table class="text-xs border-collapse" style="min-width:1400px;">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:36px">No</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:140px">Nama Alat/Mesin</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:160px">Lokasi Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:68px">Renc./<br>Real.</th>
                <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:60px">Shut<br>down</th>
                @foreach($months as $m)
                    <th class="border border-gray-300 px-1 py-1 text-center font-bold" colspan="4">{{ $m }}</th>
                @endforeach
                <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:90px">Total Durasi<br>Shutdown</th>
                <th class="border border-gray-300 px-2 py-2 text-center" style="min-width:90px">Tanggal<br>Shutdown</th>
            </tr>
            <tr class="bg-gray-50">
                <th class="border border-gray-300 px-1 py-1" colspan="5"></th>
                @foreach($months as $m)
                    @foreach(['W1','W2','W3','W4'] as $w)
                    <th class="border border-gray-300 px-0.5 py-1 text-center text-gray-500">{{ $w }}</th>
                    @endforeach
                @endforeach
                <th class="border border-gray-300 px-1 py-1" colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($grouped as $cat => $items)
            {{-- Baris section header kategori --}}
            <tr class="bg-gray-100">
                <td class="border border-gray-300 px-2 py-1.5 font-bold" colspan="{{ 5 + 48 + 2 }}">
                    {{ chr(64 + $no++) }}. {{ strtoupper($cat) }}
                </td>
            </tr>

            @php $itemNo = 1; @endphp
            @foreach($items as $sched)
            @php
                // Hitung akumulasi shutdown dari sesi yang sudah disubmit
                $submittedSessions  = ($sessions ?? collect())
                    ->where('maintenance_schedule_id', $sched->id)
                    ->where('status', 'submitted');
                $submittedCount     = $submittedSessions->count();
                $actualShutdownHours = $sched->shutdown_required
                    ? $submittedCount * ($sched->shutdown_duration_hours ?? 0)
                    : 0;
                $lastShutdownDate   = $submittedSessions->sortByDesc('submitted_at')->first()?->submitted_at;
            @endphp
            @foreach(['Renc.', 'Real.'] as $rowType)
            <tr class="hover:bg-gray-50">
                @if($loop->first)
                <td rowspan="2" class="border border-gray-300 px-1 py-1 text-center">{{ $itemNo++ }}</td>
                <td rowspan="2" class="border border-gray-300 px-2 py-1">{{ $sched->equipment_name }}</td>
                <td rowspan="2" class="border border-gray-300 px-2 py-1">{{ $sched->lokasi_inspeksi_text }}</td>
                @endif

                <td class="border border-gray-300 px-1 py-1 text-center font-medium {{ $rowType === 'Renc.' ? 'text-blue-700' : 'text-gray-700' }}">
                    {{ $rowType }}
                </td>
                <td class="border border-gray-300 px-1 py-1 text-center {{ $sched->shutdown_required ? 'text-orange-600 font-semibold' : 'text-gray-400' }}">
                    {{ $sched->shutdown_required ? 'Y' : 'N' }}
                </td>

                @php
                    $plannedWeeks = $sched->planned_weeks ?? [];
                    $plannedSet   = collect($plannedWeeks)->map(fn($p) => $p['month'] . '-' . $p['week'])->all();
                @endphp

                @foreach(range(1,12) as $month)
                    @foreach(range(1,4) as $week)
                    @php
                        $key       = $month . '-' . $week;
                        $isPlanned = in_array($key, $plannedSet);
                        // isPast = true only when the ENTIRE week has already ended
                        $weekEnd   = \Carbon\Carbon::createFromDate($year, $month, 1)->addWeeks($week)->subDay()->endOfDay();
                        $isPast    = $weekEnd->isPast();

                        // Check completed WO (by month)
                        $completedWO = $workOrders->where('maintenance_schedule_id', $sched->id)
                            ->filter(fn($wo) =>
                                $wo->completed_at &&
                                $wo->completed_at->month == $month &&
                                $wo->completed_at->year == $year
                            )->first();

                        // Check submitted checksheet session (by month + week for weekly, by month for others)
                        $completedSession = ($sessions ?? collect())->where('maintenance_schedule_id', $sched->id)
                            ->where('status', 'submitted')
                            ->filter(fn($s) =>
                                $s->month == $month &&
                                ($sched->frequency === 'weekly' ? $s->week_number == $week : true)
                            )->first();

                        $isDone    = $completedWO || $completedSession;
                        $doneAt    = $completedWO?->completed_at ?? $completedSession?->submitted_at;
                        $isOnTime  = $doneAt && $doneAt->lte($weekEnd);
                    @endphp
                    <td class="border border-gray-300 px-0.5 py-1 text-center">
                        @if($rowType === 'Renc.')
                            @if($isPlanned)
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-500 mx-auto" title="Direncanakan"></span>
                            @endif
                        @else
                            @if($isPlanned && $isDone && $isOnTime)
                                <span class="text-green-600 font-bold" title="Selesai tepat waktu">✓</span>
                            @elseif($isPlanned && $isDone && !$isOnTime)
                                <span class="text-orange-500 font-bold" title="Selesai terlambat">✓</span>
                            @elseif($isPlanned && !$isDone && $isPast)
                                <span class="text-red-600 font-bold" title="Terlewat / belum dikerjakan">✗</span>
                            @endif
                        @endif
                    </td>
                    @endforeach
                @endforeach

                <td class="border border-gray-300 px-2 py-1 text-center text-gray-600">
                    @if($sched->shutdown_required)
                        @if($rowType === 'Renc.')
                            <span class="text-brand">{{ $sched->shutdown_duration_hours ?? 0 }}h/kali</span>
                        @else
                            @if($actualShutdownHours > 0)
                                <span class="font-semibold text-orange-600">{{ $actualShutdownHours }}h</span>
                                <span class="text-gray-400 text-xs block">({{ $submittedCount }}× kali)</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        @endif
                    @endif
                </td>
                <td class="border border-gray-300 px-2 py-1 text-center text-gray-500 text-xs">
                    @if($rowType === 'Real.' && $lastShutdownDate)
                        {{ $lastShutdownDate->format('d/m/Y') }}
                    @endif
                </td>
            </tr>
            @endforeach
            @endforeach

            @endforeach
        </tbody>
    </table>

    {{-- Legenda --}}
    <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap gap-4 text-xs text-gray-600">
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-blue-500"></span> Rencana
        </span>
        <span class="flex items-center gap-1.5">
            <span class="text-green-600 font-bold text-sm">✓</span> Selesai tepat waktu
        </span>
        <span class="flex items-center gap-1.5">
            <span class="text-orange-500 font-bold text-sm">✓</span> Selesai terlambat
        </span>
        <span class="flex items-center gap-1.5">
            <span class="text-red-600 font-bold text-sm">✗</span> Terlewat / belum dikerjakan
        </span>
    </div>
</div>

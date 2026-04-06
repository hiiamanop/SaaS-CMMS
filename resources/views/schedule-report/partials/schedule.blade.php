@php
$months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$grouped = $schedules->groupBy('category');
$now = \Carbon\Carbon::now();

/*
 * Lebar kolom frozen (harus konsisten antara thead & tbody):
 * No          : 36px  → left: 0
 * Nama Alat   : 140px → left: 36px
 * Item Pekerjaan: 160px → left: 176px
 * Renc./Real. : 68px  → left: 336px
 * Shutdown    : 60px  → left: 404px
 * ─────────────────────────────────
 * Total frozen : 464px
 * Kolom bulan berikutnya mulai di left: 464px
 */
$colW = [
    'no'      => 36,
    'nama'    => 140,
    'item'    => 160,
    'renc'    => 68,
    'shut'    => 60,
];
$left = [
    'no'   => 0,
    'nama' => 36,
    'item' => 176,
    'renc' => 336,
    'shut' => 404,
];
// CSS untuk setiap kolom frozen
$stickyBase  = 'position:sticky; z-index:10; background:inherit;';
$frozenBorder = 'box-shadow: 2px 0 0 0 #d1d5db;'; // shadow kanan pada kolom terakhir frozen
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
        Kolom <em>No, Nama Alat, Item Pekerjaan, Renc./Real., Shutdown</em> di-<strong>freeze</strong> sehingga tetap terlihat saat scroll ke kanan.
    </p>
</div>

<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto" style="max-width:100%;">
    <table class="text-xs border-collapse" style="min-width:1400px; table-layout:fixed;">
        <colgroup>
            <col style="width:{{ $colW['no'] }}px">
            <col style="width:{{ $colW['nama'] }}px">
            <col style="width:{{ $colW['item'] }}px">
            <col style="width:{{ $colW['renc'] }}px">
            <col style="width:{{ $colW['shut'] }}px">
            @foreach(range(1,48) as $c)<col style="width:28px">@endforeach
            <col style="width:90px">
            <col style="width:90px">
        </colgroup>
        <thead>
            {{-- Baris 1: header kolom frozen + header bulan --}}
            <tr class="bg-gray-100 border-b border-gray-300">
                <th rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['no'] }}px; width:{{ $colW['no'] }}px;"
                    class="border border-gray-300 px-1 py-2 text-center bg-gray-100">No</th>

                <th rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['nama'] }}px; width:{{ $colW['nama'] }}px;"
                    class="border border-gray-300 px-2 py-2 text-left bg-gray-100">Nama Alat/Mesin</th>

                <th rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['item'] }}px; width:{{ $colW['item'] }}px;"
                    class="border border-gray-300 px-2 py-2 text-left bg-gray-100">Item Pekerjaan</th>

                <th rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['renc'] }}px; width:{{ $colW['renc'] }}px;"
                    class="border border-gray-300 px-1 py-2 text-center bg-gray-100">Renc./<br>Real.</th>

                <th rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['shut'] }}px; width:{{ $colW['shut'] }}px; {{ $frozenBorder }}"
                    class="border border-gray-300 px-1 py-2 text-center bg-gray-100">Shut<br>down</th>

                @foreach($months as $m)
                <th class="border border-gray-300 px-1 py-1 text-center font-bold" colspan="4">{{ $m }}</th>
                @endforeach

                <th class="border border-gray-300 px-2 py-2 text-center" rowspan="2">Total Durasi<br>Shutdown</th>
                <th class="border border-gray-300 px-2 py-2 text-center" rowspan="2">Tanggal<br>Shutdown</th>
            </tr>
            {{-- Baris 2: sub-header W1–W4 per bulan --}}
            <tr class="bg-gray-50">
                @foreach($months as $m)
                    @foreach(['W1','W2','W3','W4'] as $w)
                    <th class="border border-gray-300 px-0.5 py-1 text-center text-gray-500">{{ $w }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($grouped as $cat => $items)
            {{-- Baris section header kategori --}}
            <tr style="background:#fefce8;">
                <td colspan="5"
                    style="{{ $stickyBase }} left:0; background:#fefce8; {{ $frozenBorder }}"
                    class="border border-gray-300 px-2 py-1.5 font-bold">
                    {{ chr(64 + $no++) }}. {{ strtoupper($cat) }}
                </td>
                @foreach(range(1,48) as $c)
                <td class="border border-gray-300 bg-yellow-50/60"></td>
                @endforeach
                <td class="border border-gray-300 bg-yellow-50/60"></td>
                <td class="border border-gray-300 bg-yellow-50/60"></td>
            </tr>

            @php $itemNo = 1; @endphp
            @foreach($items as $sched)
            @foreach(['Renc.', 'Real.'] as $rowType)
            <tr class="group hover:bg-gray-50">
                @if($loop->first)
                {{-- No --}}
                <td rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['no'] }}px; background:white;"
                    class="border border-gray-300 px-1 py-1 text-center group-hover:bg-gray-50">
                    {{ $itemNo++ }}
                </td>
                {{-- Nama Alat --}}
                <td rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['nama'] }}px; background:white;"
                    class="border border-gray-300 px-2 py-1 group-hover:bg-gray-50">
                    {{ $sched->equipment_name }}
                </td>
                {{-- Item Pekerjaan --}}
                <td rowspan="2"
                    style="{{ $stickyBase }} left:{{ $left['item'] }}px; background:white;"
                    class="border border-gray-300 px-2 py-1 group-hover:bg-gray-50">
                    {{ $sched->item_pekerjaan }}
                </td>
                @endif

                {{-- Renc./Real. --}}
                <td style="{{ $stickyBase }} left:{{ $left['renc'] }}px; background:white;"
                    class="border border-gray-300 px-1 py-1 text-center font-medium group-hover:bg-gray-50
                           {{ $rowType === 'Renc.' ? 'text-blue-700' : 'text-gray-700' }}">
                    {{ $rowType }}
                </td>

                {{-- Shutdown --}}
                <td style="{{ $stickyBase }} left:{{ $left['shut'] }}px; background:white; {{ $frozenBorder }}"
                    class="border border-gray-300 px-1 py-1 text-center group-hover:bg-gray-50
                           {{ $sched->shutdown_required ? 'text-orange-600 font-semibold' : 'text-gray-400' }}">
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
                        $weekDate  = \Carbon\Carbon::createFromDate($year, $month, 1)->addWeeks($week - 1);
                        $isPast    = $weekDate->isPast();

                        $completedWO = $workOrders->where('maintenance_schedule_id', $sched->id)
                            ->filter(fn($wo) =>
                                $wo->completed_at &&
                                $wo->completed_at->month == $month &&
                                $wo->completed_at->year == $year
                            )->first();
                    @endphp
                    <td class="border border-gray-300 px-0.5 py-1 text-center">
                        @if($rowType === 'Renc.')
                            @if($isPlanned)
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-500 mx-auto" title="Direncanakan"></span>
                            @endif
                        @else
                            @if($isPlanned && $completedWO && !$isPast)
                                <span class="text-green-600 font-bold" title="Selesai tepat waktu">✓</span>
                            @elseif($isPlanned && $completedWO && $isPast)
                                <span class="text-orange-500 font-bold" title="Selesai terlambat">✓</span>
                            @elseif($isPlanned && !$completedWO && $isPast)
                                <span class="text-red-600 font-bold" title="Terlewat / belum dikerjakan">✗</span>
                            @endif
                        @endif
                    </td>
                    @endforeach
                @endforeach

                <td class="border border-gray-300 px-2 py-1 text-center text-gray-600">
                    @if($rowType === 'Renc.' && $sched->shutdown_required)
                        {{ $sched->shutdown_duration_hours }}h
                    @endif
                </td>
                <td class="border border-gray-300 px-2 py-1 text-center text-gray-500"></td>
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

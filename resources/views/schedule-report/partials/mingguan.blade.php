@php
$weeklyType = $checksheetTypes->firstWhere('frequency', 'weekly');
$weeklySessions = $sessions->where('checksheet_type_id', $weeklyType?->id)->values();
$months = range(1, 12);
$monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
@endphp

@if(!$weeklyType)
    <div class="text-center py-8 text-gray-400">Data checksheet mingguan tidak tersedia.</div>
@else
@php
    $templates = \App\Models\ChecksheetTemplate::where('checksheet_type_id', $weeklyType->id)->orderBy('order')->get();
    $grouped = $templates->groupBy('lokasi_inspeksi');
@endphp
<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <table class="text-xs border-collapse" style="min-width:900px;">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:120px">Lokasi Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:180px">Item Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:120px">Metode Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:140px">Standar Ketentuan</th>
                @foreach($months as $m)
                    <th class="border border-gray-300 px-1 py-1 text-center font-bold" colspan="4">{{ $monthNames[$m-1] }}</th>
                @endforeach
            </tr>
            <tr class="bg-gray-50">
                <th class="border border-gray-300 px-1 py-1" colspan="4"></th>
                @foreach($months as $m)
                    @foreach([1,2,3,4] as $w)
                    <th class="border border-gray-300 px-1 py-1 text-center text-gray-500">W{{ $w }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $lokasi => $items)
            <tr class="bg-gray-100">
                <td class="border border-gray-300 px-2 py-1.5 font-bold" colspan="{{ 4 + 48 }}">{{ $lokasi }}</td>
            </tr>
            @foreach($items as $template)
            <tr class="hover:bg-gray-50">
                <td class="border border-gray-300 px-2 py-1"></td>
                <td class="border border-gray-300 px-2 py-1">{{ $template->item_inspeksi }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-500">{{ $template->metode_inspeksi }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-500">{{ $template->standar_ketentuan }}</td>
                @foreach($months as $month)
                    @foreach([1,2,3,4] as $week)
                    @php
                        $session = $weeklySessions->first(fn($s) => $s->month == $month && $s->week_number == $week);
                        $result = $session ? $session->results->firstWhere('template_id', $template->id) : null;
                    @endphp
                    <td class="border border-gray-300 px-0.5 py-1 text-center">
                        @if($result?->result === 'P')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">P</span>
                        @elseif($result?->result === 'X')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800"
                                  title="{{ $result->notes }}">X</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    @endforeach
                @endforeach
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>

{{-- Abnormal sub-table --}}
@php
$allAbnormals = $weeklySessions->flatMap(fn($s) => $s->abnormals ?? collect())->filter(fn($a) => $a->abnormal_description);
@endphp
@if($allAbnormals->isNotEmpty())
<div class="mt-4 bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="bg-gray-100 px-4 py-2 font-bold text-sm">Catatan Abnormal</div>
    <table class="w-full text-xs border-collapse">
        <thead class="bg-gray-50">
            <tr>
                <th class="border border-gray-200 px-3 py-2 text-left">Tanggal</th>
                <th class="border border-gray-200 px-3 py-2 text-left">Abnormal</th>
                <th class="border border-gray-200 px-3 py-2 text-left">Penanganan</th>
                <th class="border border-gray-200 px-3 py-2 text-left">Tgl Selesai</th>
                <th class="border border-gray-200 px-3 py-2 text-left">PIC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allAbnormals as $ab)
            <tr>
                <td class="border border-gray-200 px-3 py-2">{{ $ab->tanggal?->format('d/m/Y') ?? '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $ab->abnormal_description }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $ab->penanganan ?? '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $ab->tgl_selesai?->format('d/m/Y') ?? '—' }}</td>
                <td class="border border-gray-200 px-3 py-2">{{ $ab->pic ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Signature section --}}
@php $latestSession = $weeklySessions->sortByDesc('updated_at')->first(); @endphp
@if($latestSession)
<div class="mt-4 bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="bg-gray-100 px-4 py-2 font-bold text-sm">Tanda Tangan</div>
    <div class="grid grid-cols-3 divide-x divide-gray-200 text-center py-6 px-4 text-sm">
        <div>
            <p class="text-gray-500 text-xs mb-2">Dibuat oleh (Teknisi ONM)</p>
            <p class="font-medium">{{ $latestSession->signed_by_teknisi ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $latestSession->signed_date_teknisi?->format('d M Y') ?? '' }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-xs mb-2">Diperiksa oleh (SPV ONM)</p>
            <p class="font-medium">{{ $latestSession->signed_by_spv ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $latestSession->signed_date_spv?->format('d M Y') ?? '' }}</p>
        </div>
        <div>
            <p class="text-gray-500 text-xs mb-2">Disetujui oleh (PM)</p>
            <p class="font-medium">{{ $latestSession->signed_by_pm ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $latestSession->signed_date_pm?->format('d M Y') ?? '' }}</p>
        </div>
    </div>
</div>
@endif
@endif

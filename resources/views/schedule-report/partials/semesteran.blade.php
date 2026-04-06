@php
$semesterType = $checksheetTypes->firstWhere('frequency', 'semester');
$semesterSessions = $sessions->where('checksheet_type_id', $semesterType?->id)->values();
@endphp

@if(!$semesterType)
    <div class="text-center py-8 text-gray-400">Data checksheet semesteran tidak tersedia.</div>
@else
@php
    $templates = \App\Models\ChecksheetTemplate::where('checksheet_type_id', $semesterType->id)->orderBy('order')->get();
    $grouped = $templates->groupBy('lokasi_inspeksi');
    $semesters = [
        1 => ['label' => 'Semester 1 (Jan–Jun)', 'months' => range(1,6)],
        2 => ['label' => 'Semester 2 (Jul–Des)', 'months' => range(7,12)],
    ];
    $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
@endphp
<div class="space-y-6">
@foreach($semesters as $semNum => $semInfo)
<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <div class="bg-gray-50 px-4 py-2 font-bold text-sm border-b border-gray-200">{{ $semInfo['label'] }}</div>
    <table class="text-xs border-collapse" style="min-width:700px;">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:120px">Lokasi Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:180px">Item Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:120px">Metode Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:140px">Standar Ketentuan</th>
                @foreach($semInfo['months'] as $m)
                <th class="border border-gray-300 px-1 py-1 text-center font-bold" colspan="4">{{ $monthNames[$m-1] }}</th>
                @endforeach
            </tr>
            <tr class="bg-gray-50">
                <th colspan="4" class="border border-gray-300 px-1 py-1"></th>
                @foreach($semInfo['months'] as $m)
                    @foreach([1,2,3,4] as $w)
                    <th class="border border-gray-300 px-1 py-1 text-center text-gray-500">W{{ $w }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $lokasi => $items)
            <tr class="bg-gray-100">
                <td class="border border-gray-300 px-2 py-1.5 font-bold" colspan="{{ 4 + count($semInfo['months']) * 4 }}">{{ $lokasi }}</td>
            </tr>
            @foreach($items as $template)
            <tr class="hover:bg-gray-50">
                <td class="border border-gray-300 px-2 py-1"></td>
                <td class="border border-gray-300 px-2 py-1">{{ $template->item_inspeksi }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-500">{{ $template->metode_inspeksi }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-500">{{ $template->standar_ketentuan }}</td>
                @foreach($semInfo['months'] as $month)
                    @foreach([1,2,3,4] as $week)
                    @php
                        $session = $semesterSessions->first(fn($s) => $s->semester == $semNum);
                        $result = $session ? $session->results->firstWhere('template_id', $template->id) : null;
                    @endphp
                    <td class="border border-gray-300 px-0.5 py-1 text-center">
                        @if($result?->result === 'P')
                            <span class="inline-flex items-center px-1 rounded text-xs font-bold bg-green-100 text-green-800">P</span>
                        @elseif($result?->result === 'X')
                            <span class="inline-flex items-center px-1 rounded text-xs font-bold bg-red-100 text-red-800" title="{{ $result->notes }}">X</span>
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
@endforeach
</div>
@endif

@php
$triwulanSessions = $sessions->filter(fn($s) => $s->schedule?->frequency === 'triwulan')->values();
$triwulanSchedules = \App\Models\MaintenanceSchedule::where('frequency', 'triwulan')
    ->where('status', 'active')->get();
$quartersList = [
    1 => 'Kuartal 1 (Jan–Mar)',
    2 => 'Kuartal 2 (Apr–Jun)',
    3 => 'Kuartal 3 (Jul–Sep)',
    4 => 'Kuartal 4 (Okt–Des)',
];
@endphp

@if($triwulanSchedules->isEmpty())
    <div class="text-center py-8 text-gray-400">Data checksheet triwulan tidak tersedia.</div>
@else
@foreach($triwulanSchedules as $triSchedule)
@php
    $items = (array)($triSchedule->item_pekerjaan ?? []);
    $schedSessions = $triwulanSessions->where('maintenance_schedule_id', $triSchedule->id)->values();
@endphp
@if(empty($items))
<div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span><strong>{{ $triSchedule->title ?: $triSchedule->equipment_name }}</strong> — belum ada item inspeksi.</span>
</div>
@php continue; @endphp
@endif
<div class="mb-4">
<div class="px-4 py-2 bg-blue-50 border-b border-blue-100 text-sm font-semibold text-blue-800">
    {{ $triSchedule->title ?: $triSchedule->equipment_name }}
</div>
<div class="space-y-4">
@foreach($quartersList as $qNum => $qLabel)
<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <div class="bg-gray-50 px-4 py-2 font-bold text-sm border-b border-gray-200">{{ $qLabel }}</div>
    <table class="text-xs border-collapse w-full">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:200px">Lokasi Inspeksi</th>
                <th class="border border-gray-300 px-2 py-1.5 text-center font-bold">Hasil</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedItems = [];
                foreach($items as $i) {
                    $lok = is_array($i) ? ($i['lokasi_inspeksi'] ?? '') : '';
                    $groupedItems[$lok][] = $i;
                }
            @endphp
            @foreach($groupedItems as $lokasi => $groupItems)
            @if($lokasi)
            <tr>
                <td colspan="2" class="bg-blue-50/50 border border-gray-300 px-2 py-1.5 font-bold text-gray-700 text-xs tracking-wide uppercase">{{ $lokasi }}</td>
            </tr>
            @endif
            @foreach($groupItems as $item)
            @php
                $itemName = is_array($item) ? ($item['name'] ?? '') : $item;
                $session = $schedSessions->first(fn($s) => $s->quarter == $qNum);
                $result = $session ? $session->results->firstWhere('item_name', $itemName) : null;
            @endphp
            <tr class="hover:bg-opacity-90">
                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $itemName }}</td>
                <td class="border border-gray-300 px-1 py-1 text-center">
                    @if($result?->result === 'P')
                        <span class="inline-flex items-center px-1 rounded text-xs font-bold bg-green-100 text-green-800">P</span>
                    @elseif($result?->result === 'X')
                        <span class="inline-flex items-center px-1 rounded text-xs font-bold bg-red-100 text-red-800" title="{{ $result->notes }}">X</span>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
</div>
@endforeach
</div>
</div>
@endforeach
@endif

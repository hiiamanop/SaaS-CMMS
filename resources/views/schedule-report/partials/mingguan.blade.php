@php
$weeklySessions = $sessions->filter(fn($s) => $s->schedule?->frequency === 'weekly')->values();
$weeklySchedules = \App\Models\MaintenanceSchedule::where('frequency', 'weekly')
    ->where('status', 'active')->get();
$months = range(1, 12);
$monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
@endphp

@if($weeklySchedules->isEmpty())
    <div class="text-center py-8 text-gray-400">Data checksheet mingguan tidak tersedia.</div>
@else
@foreach($weeklySchedules as $weeklySchedule)
@php
    $items = (array)($weeklySchedule->item_pekerjaan ?? []);
    $schedSessions = $weeklySessions->where('maintenance_schedule_id', $weeklySchedule->id)->values();
@endphp
@if(empty($items))
<div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span><strong>{{ $weeklySchedule->equipment_name }}</strong> — belum ada item pekerjaan. <a href="{{ route('maintenance-schedules.show', $weeklySchedule) }}" class="underline font-medium">Tambahkan item di sini.</a></span>
</div>
@php continue; @endphp
@endif
<div class="mb-4">
<div class="px-4 py-2 bg-blue-50 border-b border-blue-100 text-sm font-semibold text-blue-800">
    {{ $weeklySchedule->title ?: $weeklySchedule->equipment_name }}
</div>
<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <table class="text-xs border-collapse" style="min-width:900px;">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:300px">Lokasi Inspeksi</th>
                @foreach($months as $m)
                    <th class="border border-gray-300 px-1 py-1 text-center font-bold" colspan="4">{{ $monthNames[$m-1] }}</th>
                @endforeach
            </tr>
            <tr class="bg-gray-50">
                <th class="border border-gray-300 px-1 py-1"></th>
                @foreach($months as $m)
                    @foreach([1,2,3,4] as $w)
                    <th class="border border-gray-300 px-1 py-1 text-center text-gray-500">W{{ $w }}</th>
                    @endforeach
                @endforeach
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
                <td colspan="49" class="bg-blue-50/50 border border-gray-300 px-2 py-1.5 font-bold text-gray-700 text-xs tracking-wide uppercase">{{ $lokasi }}</td>
            </tr>
            @endif
            @foreach($groupItems as $item)
            @php $itemName = is_array($item) ? ($item['name'] ?? '') : $item; @endphp
            <tr class="hover:bg-gray-50">
                <td class="border border-gray-300 px-2 py-1 font-medium">{{ $itemName }}</td>
                @foreach($months as $month)
                    @foreach([1,2,3,4] as $week)
                    @php
                        $session = $schedSessions->first(fn($s) => $s->month == $month && $s->week_number == $week);
                        $result = $session ? $session->results->firstWhere('item_name', $itemName) : null;
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
$allAbnormals = $schedSessions->flatMap(fn($s) => $s->abnormals ?? collect())->filter(fn($a) => $a->abnormal_description);
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
</div>{{-- end per-schedule --}}
@endforeach
@endif

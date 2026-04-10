@php
$annualSessions = $sessions->filter(fn($s) => $s->schedule?->frequency === 'annually')->values();
$annualSchedules = \App\Models\MaintenanceSchedule::with('checklistTemplates')
    ->where('frequency', 'annually')->where('status', 'active')->get();
@endphp

@if($annualSchedules->isEmpty())
    <div class="text-center py-8 text-gray-400">Data checksheet tahunan tidak tersedia.</div>
@else
@foreach($annualSchedules as $annSchedule)
@php
    $templates = $annSchedule->checklistTemplates()->orderBy('order')->get();
    $grouped = $templates->groupBy('lokasi_inspeksi');
    $schedSessions = $annualSessions->where('maintenance_schedule_id', $annSchedule->id)->values();
    $annSession = $schedSessions->first();
@endphp
@if($templates->isEmpty())
<div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    <span><strong>{{ $annSchedule->equipment_name }}</strong> — belum ada template checksheet. <a href="{{ route('maintenance-schedules.show', $annSchedule) }}" class="underline font-medium">Tambahkan template di sini.</a></span>
</div>
@php continue; @endphp
@endif
@endphp
<div class="mb-4">
<div class="px-4 py-2 bg-blue-50 border-b border-blue-100 text-sm font-semibold text-blue-800">
    {{ $annSchedule->equipment_name }} — {{ $annSchedule->item_pekerjaan_text }}
</div>
<div class="bg-white rounded-lg border border-gray-200 overflow-x-auto">
    <table class="text-xs border-collapse" style="min-width:700px;">
        <thead>
            <tr class="bg-gray-100">
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:120px">Lokasi Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:180px">Item Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:120px">Metode Inspeksi</th>
                <th class="border border-gray-300 px-2 py-2 text-left" style="min-width:140px">Standar Ketentuan</th>
                <th class="border border-gray-300 px-2 py-1.5 text-center font-bold">Hasil {{ $year }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $lokasi => $items)
            <tr class="bg-gray-100">
                <td class="border border-gray-300 px-2 py-1.5 font-bold" colspan="5">{{ $lokasi }}</td>
            </tr>
            @foreach($items as $template)
            @php
                $result = $annSession ? $annSession->results->firstWhere('template_id', $template->id) : null;
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="border border-gray-300 px-2 py-1"></td>
                <td class="border border-gray-300 px-2 py-1">{{ $template->item_inspeksi }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-500">{{ $template->metode_inspeksi }}</td>
                <td class="border border-gray-300 px-2 py-1 text-gray-500">{{ $template->standar_ketentuan }}</td>
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
</div>{{-- end per-schedule --}}
@endforeach
@endif

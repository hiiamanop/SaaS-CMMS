@extends('layouts.app')
@section('title', $maintenanceSchedule->title)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('maintenance-schedules.index') }}" class="hover:text-gray-800">Schedules</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $maintenanceSchedule->title }}</span>@endsection
@section('content')
@php $s = $maintenanceSchedule; @endphp
<div class="space-y-5 max-w-3xl">
    <div class="flex items-center gap-3">
        <a href="{{ route('maintenance-schedules.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <div class="flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900">{{ $s->title }}</h1>
<span class="px-2.5 py-0.5 {{ $s->type==='preventive'?'bg-blue-100 text-blue-700':'bg-orange-100 text-orange-700' }} text-xs font-medium rounded-full">{{ ucfirst($s->type) }}</span>
            </div>
        </div>
        @if(!auth()->user()->isTechnician())
        <a href="{{ route('maintenance-schedules.edit',$s) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            @foreach([['Asset',$s->asset->name],['Frequency',ucfirst($s->frequency)],['Last Done',$s->last_done_date?$s->last_done_date->format('M d, Y'):'Never'],['Status',ucfirst($s->status)],['Teknisi',$s->technician?->name??'—']] as [$l,$v])
            <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm font-medium text-gray-900">{{ $v }}</dd></div>
            @endforeach
        </div>
        @if($s->item_pekerjaan)
        <div class="border-t pt-4 mb-4">
            <dt class="text-xs font-medium text-gray-500 uppercase mb-2">Item Pekerjaan</dt>
            <ul class="space-y-1">
                @foreach((array)$s->item_pekerjaan as $item)
                <li class="flex items-center gap-2 text-sm text-gray-800">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                    {{ $item }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @if($s->notes)<p class="text-sm text-gray-600 mb-4 border-t pt-4">{{ $s->notes }}</p>@endif
        @if($s->checklistTemplates->count())
        <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-900">Template Checksheet ({{ $s->checklistTemplates->count() }} item)</p>
                <a href="{{ route('checksheet.templates.index', ['schedule_id' => $s->id]) }}"
                   class="text-xs text-blue-600 hover:underline">Kelola Template →</a>
            </div>
            <ul class="space-y-1.5">
            @foreach($s->checklistTemplates as $item)
            <li class="flex items-start gap-2.5 text-sm text-gray-700">
                <svg class="w-4 h-4 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                <span>{{ $item->item_inspeksi }} <span class="text-gray-400 text-xs">({{ $item->lokasi_inspeksi }})</span></span>
            </li>
            @endforeach
            </ul>
        </div>
        @else
        <div class="border-t pt-4 text-center py-4">
            <p class="text-sm text-gray-400">Belum ada template checksheet.</p>
            <a href="{{ route('checksheet.templates.index', ['schedule_id' => $s->id]) }}"
               class="mt-2 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:underline">
                Tambah template sekarang →
            </a>
        </div>
        @endif
    </div>

    @if($s->workOrders->count())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Work Orders from this Schedule</h2></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">WO #</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Due Date</th><th class="px-5 py-3 text-left">Assigned To</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($s->workOrders as $wo)
            @php $stc=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700']; @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3"><a href="{{ route('work-orders.show',$wo) }}" class="text-blue-600 hover:underline font-medium">{{ $wo->wo_number }}</a></td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $stc[$wo->status]??'' }}">{{ $wo->status_label }}</span></td>
                <td class="px-5 py-3 text-gray-600">{{ $wo->due_date->format('M d, Y') }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $wo->assignedTo?->name??'Unassigned' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection

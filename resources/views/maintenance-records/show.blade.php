@extends('layouts.app')
@section('title', $maintenanceRecord->record_number)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('maintenance-records.index') }}" class="hover:text-gray-800">Records</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $maintenanceRecord->record_number }}</span>@endsection
@section('content')
@php $mr = $maintenanceRecord; @endphp
<div class="space-y-5 max-w-3xl">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('maintenance-records.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $mr->record_number }}</h1>
                <p class="text-sm text-gray-500">{{ $mr->maintenance_date->format('F j, Y') }} · {{ $mr->asset->name }}</p>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $mr->type==='preventive'?'bg-blue-100 text-blue-700':'bg-orange-100 text-orange-700' }}">{{ ucfirst($mr->type) }}</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold text-gray-900">{{ $mr->duration_minutes }}</p><p class="text-xs text-gray-500 mt-0.5">Duration (min)</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold {{ $mr->downtime_minutes>0?'text-orange-600':'text-gray-900' }}">{{ $mr->downtime_minutes }}</p><p class="text-xs text-gray-500 mt-0.5">Downtime (min)</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold text-gray-900">{{ $mr->parts->sum('qty_used') }}</p><p class="text-xs text-gray-500 mt-0.5">Parts Used</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold text-gray-900">{{ $mr->photos->count() }}</p><p class="text-xs text-gray-500 mt-0.5">Photos</p></div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            @foreach([['Asset',$mr->asset->name],['Technician',$mr->technician->name],['Date',$mr->maintenance_date->format('M d, Y')],['Work Order',$mr->workOrder?$mr->workOrder->wo_number:'—'],['Duration',$mr->duration_minutes.' min'],['Downtime',$mr->downtime_minutes.' min']] as [$l,$v])
            <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm font-medium text-gray-900">{{ $v }}</dd></div>
            @endforeach
        </div>
        @if($mr->findings)
        <div class="pt-4 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-2">Findings</p><p class="text-sm text-gray-700">{{ $mr->findings }}</p></div>
        @endif
        @if($mr->actions_taken)
        <div class="pt-4 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-2">Actions Taken</p><p class="text-sm text-gray-700">{{ $mr->actions_taken }}</p></div>
        @endif
        @if($mr->notes)
        <div class="pt-4 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-2">Notes</p><p class="text-sm text-gray-700">{{ $mr->notes }}</p></div>
        @endif
    </div>

    @if($mr->parts->count())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Parts Used</h2></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">Part</th><th class="px-5 py-3 text-left">Qty Used</th><th class="px-5 py-3 text-left">Unit Price</th><th class="px-5 py-3 text-left">Total</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($mr->parts as $p)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $p->sparePart->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $p->qty_used }} {{ $p->sparePart->unit }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $p->unit_price ? 'IDR '.number_format($p->unit_price) : '—' }}</td>
                <td class="px-5 py-3 text-gray-700 font-medium">{{ $p->unit_price ? 'IDR '.number_format($p->unit_price * $p->qty_used) : '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($mr->photos->count())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h2 class="font-semibold text-gray-900 mb-4">Photos</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach($mr->photos as $photo)
            <a href="{{ Storage::url($photo->file_path) }}" target="_blank">
                <img src="{{ Storage::url($photo->file_path) }}" class="w-full h-32 object-cover rounded-lg hover:opacity-80 transition-opacity">
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

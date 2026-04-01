@extends('layouts.app')
@section('title','Maintenance Records')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Maintenance Records</span>@endsection
@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-gray-900">Maintenance Records</h1><p class="text-sm text-gray-500 mt-0.5">History of all maintenance activities</p></div>
        <a href="{{ route('maintenance-records.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>New Record
        </a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search records..." class="flex-1 min-w-[180px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="asset_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Assets</option>
                @foreach($assets as $a)<option value="{{ $a->id }}" {{ request('asset_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
            </select>
            <select name="technician_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Technicians</option>
                @foreach($technicians as $t)<option value="{{ $t->id }}" {{ request('technician_id')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
            </select>
            <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="preventive" {{ request('type')=='preventive'?'selected':'' }}>Preventive</option>
                <option value="corrective" {{ request('type')=='corrective'?'selected':'' }}>Corrective</option>
            </select>
            <input name="date_from" type="date" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input name="date_to" type="date" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium">Filter</button>
            <a href="{{ route('maintenance-records.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($records->isEmpty())
        <div class="py-16 text-center text-gray-400">No maintenance records found</div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3 text-left">Record #</th><th class="px-5 py-3 text-left">Asset</th><th class="px-5 py-3 text-left">Type</th><th class="px-5 py-3 text-left">Date</th><th class="px-5 py-3 text-left">Technician</th><th class="px-5 py-3 text-left">Duration</th><th class="px-5 py-3 text-left">Downtime</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($records as $r)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3"><a href="{{ route('maintenance-records.show',$r) }}" class="font-mono text-xs font-semibold text-blue-600 hover:underline">{{ $r->record_number }}</a></td>
                <td class="px-5 py-3 text-gray-700">{{ $r->asset->name }}</td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $r->type==='preventive'?'bg-blue-100 text-blue-700':'bg-orange-100 text-orange-700' }}">{{ ucfirst($r->type) }}</span></td>
                <td class="px-5 py-3 text-gray-600">{{ $r->maintenance_date->format('M d, Y') }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $r->technician->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $r->duration_minutes }} min</td>
                <td class="px-5 py-3 {{ $r->downtime_minutes>0?'text-orange-600 font-medium':'text-gray-400' }}">{{ $r->downtime_minutes }} min</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex justify-end gap-1">
                        <a href="{{ route('maintenance-records.show',$r) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></a>
                        @if(!auth()->user()->isTechnician())
                        <button @click="$dispatch('open-delete',{action:'{{ route('maintenance-records.destroy',$r) }}',message:'Delete record {{ addslashes($r->record_number) }}?'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $records->links() }}</div>
        @endif
    </div>
</div>
@endsection

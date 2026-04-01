@extends('layouts.app')
@section('title','Work Orders')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Work Orders</span>@endsection
@section('content')
@php
$pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'];
$sColors=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700'];
@endphp
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-gray-900">Work Orders</h1><p class="text-sm text-gray-500 mt-0.5">Track and manage maintenance jobs</p></div>
        @if(!auth()->user()->isTechnician())
        <a href="{{ route('work-orders.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>New Work Order
        </a>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search WO# or title..." class="flex-1 min-w-[180px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Statuses</option>
                @foreach(['open'=>'Open','in_progress'=>'In Progress','pending_review'=>'Pending Review','closed'=>'Closed'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Priorities</option>
                @foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" {{ request('priority')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
            </select>
            <select name="asset_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Assets</option>
                @foreach($assets as $a)<option value="{{ $a->id }}" {{ request('asset_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
            </select>
            <input name="date_from" type="date" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input name="date_to" type="date" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('work-orders.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($workOrders->isEmpty())
        <div class="py-16 text-center text-gray-400">No work orders found</div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                <th class="px-5 py-3 text-left">WO #</th><th class="px-5 py-3 text-left">Title</th><th class="px-5 py-3 text-left">Asset</th><th class="px-5 py-3 text-left">Assigned To</th><th class="px-5 py-3 text-left">Priority</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Due Date</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($workOrders as $wo)
            @php $overdue = $wo->due_date->isPast() && $wo->status !== 'closed'; @endphp
            <tr class="hover:bg-gray-50 transition-colors {{ $overdue?'bg-red-50/20':'' }}">
                <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700"><a href="{{ route('work-orders.show',$wo) }}" class="hover:text-blue-600">{{ $wo->wo_number }}</a></td>
                <td class="px-5 py-3">
                    <a href="{{ route('work-orders.show',$wo) }}" class="font-medium text-gray-900 hover:text-blue-600 max-w-[200px] truncate block">{{ $wo->title }}</a>
                    @if($overdue)<span class="text-xs text-red-500">Overdue</span>@endif
                </td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $wo->asset->name }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $wo->assignedTo?->name ?? '—' }}</td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pColors[$wo->priority]??'' }}">{{ ucfirst($wo->priority) }}</span></td>
                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sColors[$wo->status]??'' }}">{{ $wo->status_label }}</span></td>
                <td class="px-5 py-3 text-xs {{ $overdue?'text-red-600 font-medium':'text-gray-500' }}">{{ $wo->due_date->format('M d, Y') }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('work-orders.show',$wo) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></a>
                        @if(!auth()->user()->isTechnician())
                        <a href="{{ route('work-orders.edit',$wo) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('work-orders.destroy',$wo) }}',message:'Delete work order {{ addslashes($wo->wo_number) }}?'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $workOrders->links() }}</div>
        @endif
    </div>
</div>
@endsection

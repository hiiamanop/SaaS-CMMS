@extends('layouts.app')
@section('title','Work Orders & Records')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Work Orders & Records</span>@endsection
@section('content')
@php
$pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'];
$sColors=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700'];
@endphp

<div class="space-y-5" x-data="{ tab: '{{ request('records_page') ? 'records' : 'work_orders' }}' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Work Orders & Records</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage maintenance jobs and their historical records</p>
        </div>
        <div class="flex gap-2">
            @if(!auth()->user()->isTechnician())
            <a href="{{ route('work-orders.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                New Work Order
            </a>
            <a href="{{ route('maintenance-records.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                New Record
            </a>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="tab = 'work_orders'" 
                :class="tab === 'work_orders' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all">
                Work Orders
                <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-[10px]">{{ $workOrders->total() }}</span>
            </button>
            <button @click="tab = 'records'" 
                :class="tab === 'records' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all">
                Maintenance Records
                <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-[10px]">{{ $records->total() }}</span>
            </button>
        </nav>
    </div>

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search ID or title..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
            
            <div x-show="tab === 'work_orders'" class="contents">
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
            </div>

            <select name="asset_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Assets</option>
                @foreach($assets as $a)<option value="{{ $a->id }}" {{ request('asset_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
            </select>

            <input name="date_from" type="date" value="{{ request('date_from') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input name="date_to" type="date" value="{{ request('date_to') }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-all shadow-sm">Filter</button>
            <a href="{{ route('work-orders.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-all shadow-sm">Reset</a>
        </form>
    </div>

    {{-- Tab Content: Work Orders --}}
    <div x-show="tab === 'work_orders'" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
        @if($workOrders->isEmpty())
        <div class="py-16 text-center text-gray-400">No work orders found</div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-4 text-left">WO #</th><th class="px-5 py-4 text-left">Title</th><th class="px-5 py-4 text-left">Asset</th><th class="px-5 py-4 text-left">Assignee</th><th class="px-5 py-4 text-left">Priority</th><th class="px-5 py-4 text-left">Status</th><th class="px-5 py-4 text-left">Due Date</th><th class="px-5 py-4 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($workOrders as $wo)
            @php $overdue = $wo->due_date->isPast() && $wo->status !== 'closed'; @endphp
            <tr class="hover:bg-gray-50 transition-colors {{ $overdue?'bg-red-50/20':'' }}">
                <td class="px-5 py-4 font-mono text-xs font-bold text-gray-700"><a href="{{ route('work-orders.show',$wo) }}" class="text-blue-600 hover:underline">{{ $wo->wo_number }}</a></td>
                <td class="px-5 py-4">
                    <a href="{{ route('work-orders.show',$wo) }}" class="font-semibold text-gray-900 hover:text-blue-600 max-w-[200px] truncate block">{{ $wo->title }}</a>
                </td>
                <td class="px-5 py-4 text-gray-600 text-xs">{{ $wo->asset->name }}</td>
                <td class="px-5 py-4 text-gray-600 text-xs">{{ $wo->assignedTo?->name ?? '—' }}</td>
                <td class="px-5 py-4"><span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $pColors[$wo->priority]??'' }}">{{ $wo->priority }}</span></td>
                <td class="px-5 py-4"><span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $sColors[$wo->status]??'' }}">{{ str_replace('_',' ',$wo->status) }}</span></td>
                <td class="px-5 py-4 text-xs {{ $overdue ? 'text-red-600 font-bold' : 'text-gray-500' }}">{{ $wo->due_date->format('d M Y') }}</td>
                <td class="px-5 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('work-orders.show',$wo) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></a>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">{{ $workOrders->links() }}</div>
        @endif
    </div>

    {{-- Tab Content: Records --}}
    <div x-show="tab === 'records'" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1">
        @if($records->isEmpty())
        <div class="py-16 text-center text-gray-400">No maintenance records found</div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-4 text-left">Record #</th>
                    <th class="px-5 py-4 text-left">Asset</th>
                    <th class="px-5 py-4 text-left">Type</th>
                    <th class="px-5 py-4 text-left">Reference</th>
                    <th class="px-5 py-4 text-left">Date</th>
                    <th class="px-5 py-4 text-left">Technician</th>
                    <th class="px-5 py-4 text-left">Duration</th>
                    <th class="px-5 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($records as $r)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-5 py-4 font-mono text-xs font-bold text-gray-700">
                    <a href="{{ route('maintenance-records.show',$r) }}" class="text-blue-600 hover:underline">{{ $r->record_number }}</a>
                </td>
                <td class="px-5 py-4 text-gray-900 font-medium">{{ $r->asset->name }}</td>
                <td class="px-5 py-4 font-semibold text-[10px] uppercase tracking-wider">
                    <span class="{{ $r->type === 'preventive' ? 'text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded' : 'text-orange-600 bg-orange-50 px-1.5 py-0.5 rounded' }}">
                        {{ $r->type }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    @if($r->workOrder)
                        <a href="{{ route('work-orders.show', $r->workOrder) }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:underline uppercase">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                            {{ $r->workOrder->wo_number }}
                        </a>
                    @else
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Manual Entry</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-gray-600 text-xs">{{ $r->maintenance_date->format('d M Y') }}</td>
                <td class="px-5 py-4 text-gray-600 text-xs font-medium">{{ $r->technician->name }}</td>
                <td class="px-5 py-4 text-gray-500 text-xs">
                    <div class="flex gap-2">
                        <span>Dur: {{ $r->duration_minutes }}m</span>
                        @if($r->shutdown_minutes > 0)
                        <span class="text-orange-600 font-bold">Shut: {{ $r->shutdown_minutes }}m</span>
                        @endif
                    </div>
                </td>
                <td class="px-5 py-4 text-right">
                    <a href="{{ route('maintenance-records.show',$r) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">{{ $records->links() }}</div>
        @endif
    </div>
</div>
@endsection

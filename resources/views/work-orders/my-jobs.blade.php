@extends('layouts.app')
@section('title','My Jobs')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">My Jobs</span>@endsection
@section('content')
@php
$pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'];
$sColors=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700'];
@endphp
<div class="space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Jobs</h1>
        <p class="text-sm text-gray-500 mt-0.5">Work orders assigned to you</p>
    </div>

    @if($workOrders->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center shadow-sm">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        <p class="text-gray-500 font-medium">No open jobs assigned to you</p>
        <p class="text-sm text-gray-400 mt-1">Check back later or contact your supervisor</p>
    </div>
    @else
    <div class="grid gap-4">
    @foreach($workOrders as $wo)
    @php $overdue = $wo->due_date->isPast(); @endphp
    <div class="bg-white rounded-xl border {{ $overdue?'border-red-200':'border-gray-200' }} shadow-sm p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="font-mono text-xs text-gray-500">{{ $wo->wo_number }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pColors[$wo->priority]??'' }}">{{ ucfirst($wo->priority) }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sColors[$wo->status]??'' }}">{{ $wo->status_label }}</span>
                    @if($overdue)<span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-medium rounded-full">Overdue</span>@endif
                </div>
                <a href="{{ route('work-orders.show',$wo) }}" class="text-base font-semibold text-gray-900 hover:text-brand">{{ $wo->title }}</a>
                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>{{ $wo->asset->name }}</span>
                    <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>Due {{ $wo->due_date->format('M d, Y') }}</span>
                </div>
            </div>
            <a href="{{ route('work-orders.show',$wo) }}" class="flex-shrink-0 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100">View Job</a>
        </div>
        @if($wo->checklistItems->count())
        @php $done=$wo->checklistItems->where('is_checked',true)->count(); $total=$wo->checklistItems->count(); @endphp
        <div class="mt-3 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 rounded-full h-1.5"><div class="h-1.5 bg-green-500 rounded-full" style="width:{{ $total>0?round(($done/$total)*100):0 }}%"></div></div>
                <span class="text-xs text-gray-500">{{ $done }}/{{ $total }} tasks</span>
            </div>
        </div>
        @endif
    </div>
    @endforeach
    </div>
    <div>{{ $workOrders->links() }}</div>
    @endif
</div>
@endsection

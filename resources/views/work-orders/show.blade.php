@extends('layouts.app')
@section('title', $workOrder->wo_number)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('work-orders.index') }}" class="hover:text-gray-800">Work Orders</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $workOrder->wo_number }}</span>@endsection
@section('content')
@php
$pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'];
$sColors=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700'];
$wo = $workOrder;
@endphp
<div class="space-y-5" x-data="{tab:'details', statusModal:false, newStatus:'', statusNotes:''}">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div class="flex items-start gap-3">
            <a href="{{ route('work-orders.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg mt-0.5"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-bold text-gray-900">{{ $wo->wo_number }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pColors[$wo->priority]??'' }}">{{ ucfirst($wo->priority) }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sColors[$wo->status]??'' }}">{{ $wo->status_label }}</span>
                    @if($wo->isOverdue())<span class="px-2.5 py-0.5 bg-red-100 text-red-600 text-xs font-medium rounded-full">Overdue</span>@endif
                </div>
                <p class="text-gray-700 font-medium mt-0.5">{{ $wo->title }}</p>
            </div>
        </div>
        <div class="flex gap-2 flex-wrap">
            @if($wo->status !== 'closed')
            <button @click="statusModal=true" class="inline-flex items-center gap-2 px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Update Status
            </button>
            @endif
            @if(!auth()->user()->isTechnician())
            <a href="{{ route('work-orders.edit',$wo) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
            @endif
            @if($wo->status==='closed' && !$wo->maintenanceRecord)
            <a href="{{ route('maintenance-records.create', ['work_order_id'=>$wo->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Create Record</a>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-2">
            <div class="flex gap-1 -mb-px">
                @foreach(['details'=>'Details','checklist'=>'Checklist ('.$wo->checklistItems->count().')', 'maintenance' => 'Maintenance Detail', 'activity'=>'Activity Log'] as $k=>$l)
                    @if($k === 'maintenance' && !$wo->maintenanceRecord) @continue @endif
                    <button @click="tab='{{ $k }}'" :class="tab==='{{ $k }}'?'border-b-2 border-brand text-brand':'text-gray-500 hover:text-gray-700'" class="px-4 py-3.5 text-sm font-medium transition-colors whitespace-nowrap">{{ $l }}</button>
                @endforeach
            </div>
        </div>

        {{-- Details tab --}}
        <div x-show="tab==='details'" class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5">
                @foreach([['Asset',$wo->asset->name],['Type',ucfirst($wo->type)],['Due Date',$wo->due_date->format('M d, Y')],['Assigned To',$wo->assignedTo?->name??'Unassigned'],['Created By',$wo->createdBy->name],['Started At',$wo->started_at?$wo->started_at->format('M d, Y H:i'):'—'],['Completed At',$wo->completed_at?$wo->completed_at->format('M d, Y H:i'):'—'],['Created At',$wo->created_at->format('M d, Y')]] as [$l,$v])
                <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm text-gray-900">{{ $v }}</dd></div>
                @endforeach
            </div>
            @if($wo->description)<div class="mt-5 pt-5 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-2">Description</p><p class="text-sm text-gray-700">{{ $wo->description }}</p></div>@endif
        </div>

        {{-- Checklist tab --}}
        <div x-show="tab==='checklist'" class="p-6">
            @if($wo->checklistItems->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No checklist items</p>
            @else
            <div class="space-y-4">
            @foreach($wo->checklistItems as $item)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 border border-gray-100 rounded-lg bg-gray-50/50">
                <div class="flex-1 flex items-center gap-3">
                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 {{ $item->is_checked ? 'border-green-500 bg-green-500' : 'border-gray-300' }}">
                        @if($item->is_checked)<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>@endif
                    </div>
                    <span class="text-sm font-medium {{ $item->is_checked ? 'text-gray-900' : 'text-gray-500' }}">{{ $item->description }}</span>
                </div>
                @if($item->is_checked)
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $item->result === 'ok' ? 'bg-green-100 text-green-700' : ($item->result === 'repaired' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700') }}">
                        {{ $item->result ?: 'N/A' }}
                    </span>
                    <span class="text-[10px] text-gray-400 font-medium">Checked by {{ $item->checkedBy?->name }}</span>
                </div>
                @endif
            </div>
            @endforeach
            </div>
            @php $done = $wo->checklistItems->where('is_checked',true)->count(); $total = $wo->checklistItems->count(); @endphp
            <div class="mt-4 flex items-center gap-3">
                <div class="flex-1 bg-gray-200 rounded-full h-2"><div class="h-2 bg-green-500 rounded-full" style="width:{{ $total>0?round(($done/$total)*100):0 }}%"></div></div>
                <span class="text-xs text-gray-500 font-medium">{{ $done }}/{{ $total }}</span>
            </div>
            @endif
        </div>

        {{-- Maintenance Details tab --}}
        @if($mr = $wo->maintenanceRecord)
        <div x-show="tab==='maintenance'" class="p-6 space-y-6">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Completion Result</p>
                    <div class="mt-1 flex items-center gap-2">
                        @php $resColors=['solved'=>'bg-green-100 text-green-700','pending'=>'bg-yellow-100 text-yellow-700','failure'=>'bg-red-100 text-red-700']; @endphp
                        <span class="px-3 py-1 rounded-lg text-sm font-bold uppercase {{ $resColors[$mr->status_after]??'bg-gray-100' }}">{{ $mr->status_after }}</span>
                        <span class="text-gray-400 text-xs">— Registered on {{ $mr->maintenance_date->format('M d, Y') }}</span>
                    </div>
                </div>
                <a href="{{ route('maintenance-records.show', $mr) }}" class="text-brand text-sm font-bold hover:underline">View Full Record →</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Findings</h4>
                    <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 min-h-[60px]">{{ $mr->findings ?: 'No findings reported.' }}</p>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Actions Taken</h4>
                    <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 min-h-[60px]">{{ $mr->actions_taken ?: 'No actions reported.' }}</p>
                </div>
            </div>

            @if($mr->parts->isNotEmpty())
            <div>
                <h4 class="text-xs font-bold text-gray-500 uppercase mb-3">Parts Replaced</h4>
                <div class="divide-y divide-gray-100 border border-gray-100 rounded-lg overflow-hidden">
                    @foreach($mr->parts as $part)
                    <div class="flex items-center justify-between px-4 py-2.5 bg-white">
                        <span class="text-sm text-gray-800">{{ $part->sparePart->name }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $part->qty_used }} {{ $part->sparePart->unit }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Activity log --}}
        <div x-show="tab==='activity'" class="p-6">
            @if($wo->activityLogs->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">No activity yet</p>
            @else
            <div class="space-y-4">
            @foreach($wo->activityLogs as $log)
            <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 text-xs font-semibold flex-shrink-0 uppercase">{{ substr($log->user->name,0,1) }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-medium text-sm text-gray-900">{{ $log->user->name }}</span>
                        @if($log->from_status)
                        <span class="text-xs text-gray-400">changed status from</span>
                        <span class="text-xs font-medium text-gray-600">{{ ucwords(str_replace('_',' ',$log->from_status)) }}</span>
                        <span class="text-xs text-gray-400">→</span>
                        @endif
                        <span class="text-xs font-medium text-brand">{{ ucwords(str_replace('_',' ',$log->to_status)) }}</span>
                        <span class="text-xs text-gray-400 ml-auto">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    @if($log->notes)<p class="text-sm text-gray-600 mt-1">{{ $log->notes }}</p>@endif
                </div>
            </div>
            @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Status update modal --}}
    <div x-show="statusModal" @keydown.escape.window="statusModal=false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" style="display:none" x-transition>
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6" @click.stop>
            <h3 class="font-semibold text-gray-900 mb-4">Update Work Order Status</h3>
            <form action="{{ route('work-orders.update-status',$wo) }}" method="POST" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">New Status</label>
                    <select name="status" x-model="newStatus" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        @if($wo->status === 'open')<option value="in_progress">In Progress</option>@endif
                        @if(in_array($wo->status,['open','in_progress']))<option value="pending_review">Pending Review</option>@endif
                        @if(!auth()->user()->isTechnician())<option value="closed">Closed</option>@endif
                        @if(!auth()->user()->isTechnician() && $wo->status !== 'open')<option value="open">Re-open</option>@endif
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label><textarea name="notes" rows="3" x-model="statusNotes" placeholder="Add notes about this status change..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"></textarea></div>
                <div class="flex gap-3">
                    <button type="button" @click="statusModal=false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-600">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

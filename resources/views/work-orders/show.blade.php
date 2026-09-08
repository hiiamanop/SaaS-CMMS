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
            <button @click="statusModal=true" class="inline-flex items-center gap-2 px-4 py-2 bg-brand text-gray-900 rounded-lg text-sm font-medium hover:bg-brand-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Update Status
            </button>
            @endif
            @if(!auth()->user()->isTechnician())
            <a href="{{ route('work-orders.edit',$wo) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Edit</a>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="border-b border-gray-200 px-2">
            <div class="flex gap-1 -mb-px">
                @foreach(['details'=>'Details', 'activity'=>'Activity Log'] as $k=>$l)
                    <button @click="tab='{{ $k }}'" :class="tab==='{{ $k }}'?'border-b-2 border-brand text-brand':'text-gray-500 hover:text-gray-700'" class="px-4 py-3.5 text-sm font-medium transition-colors whitespace-nowrap">{{ $l }}</button>
                @endforeach
            </div>
        </div>

        {{-- Details tab --}}
        <div x-show="tab==='details'" class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-5">
                @foreach([
                    [$wo->is_external_client ? 'Client Location' : 'Internal Asset', $wo->is_external_client ? ($wo->client_name ?: 'Unknown Client') : ($wo->asset?->name ?: '—')],
                    ['Type', ucfirst($wo->type)],
                    ['Due Date', $wo->due_date->format('M d, Y')],
                    ['Assigned To', $wo->assignees->isNotEmpty() ? $wo->assignees->pluck('name')->implode(', ') : 'Unassigned'],
                    ['Created By', $wo->createdBy->name],
                    ['Started At', $wo->started_at ? $wo->started_at->format('M d, Y H:i') : '—'],
                    ['Completed At', $wo->completed_at ? $wo->completed_at->format('M d, Y H:i') : '—'],
                    ['Created At', $wo->created_at->format('M d, Y')]
                ] as [$l, $v])
                <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm text-gray-900">{{ $v }}</dd></div>
                @endforeach
            </div>

            @if($wo->asset && ($wo->asset->category === 'PV Module' || $wo->asset->transformer_block))
            <div class="mt-5 p-4 bg-emerald-50/70 border border-emerald-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-600/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded">Posisi Aset di Peta PV Module</span>
                            @if($wo->asset->transformer_block)
                            <span class="text-xs font-bold text-gray-800 bg-white px-2 py-0.5 rounded border border-emerald-200">Blok {{ $wo->asset->transformer_block }}</span>
                            @endif
                        </div>
                        <p class="text-sm font-bold text-gray-900 mt-0.5">
                            {{ $wo->asset->name }}
                            <span class="font-mono text-xs text-emerald-800 font-bold">({{ $wo->asset->hierarchy_code ?: $wo->asset->asset_code }})</span>
                        </p>
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-600 mt-1 font-mono">
                            @if($wo->asset->string_number)
                            <span>Inverter: <strong class="text-gray-900">INV{{ str_pad($wo->asset->string_number, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            @endif
                            @if($wo->asset->module_slot)
                            <span>String: <strong class="text-gray-900">S{{ str_pad($wo->asset->module_slot, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            @endif
                            @if($wo->asset->visual_row && $wo->asset->visual_col)
                            <span>Grid: <strong class="text-gray-900">Baris {{ $wo->asset->visual_row }}, Kolom {{ $wo->asset->visual_col }}</strong></span>
                            @endif
                            @if($wo->asset->location)
                            <span class="font-sans text-gray-500">Lokasi: {{ $wo->asset->location }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}#peta-pv"
                   class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-all shadow-sm shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Buka Peta PV
                </a>
            </div>
            @endif

            @if($wo->description)<div class="mt-5 pt-5 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-2">Description</p><p class="text-sm text-gray-700">{{ $wo->description }}</p></div>@endif
        </div>

        {{-- Items used --}}
        <div x-show="tab==='details'" class="px-6 pb-6">
            <div class="pt-5 border-t border-gray-100">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Items Terpakai</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Barang yang dicatat dan dipakai pada Work Order ini.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">{{ $wo->items->count() }} item</span>
                </div>
                @if($wo->items->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 py-6 text-center text-xs text-gray-400 bg-gray-50/50">Belum ada item yang dicatat.</div>
                @else
                <div class="overflow-x-auto border border-gray-200 rounded-xl shadow-xs">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                                <th class="px-4 py-3 text-left">Jenis</th>
                                <th class="px-4 py-3 text-left">Item</th>
                                <th class="px-4 py-3 text-left">Qty</th>
                                <th class="px-4 py-3 text-left">Dicatat Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($wo->items as $usedItem)
                        @php
                            $itemModel = $usedItem->item;
                            $itemUrl = match($usedItem->item_type) {
                                'spare_part' => $itemModel ? route('spare-parts.show', $itemModel) : null,
                                'consumable' => $itemModel ? route('consumables.show', $itemModel) : null,
                                'tool'       => $itemModel ? route('tools.show', $itemModel) : null,
                                default      => null,
                            };
                            $badgeColor = match($usedItem->item_type) {
                                'spare_part' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'consumable' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'tool'       => 'bg-purple-50 text-purple-700 border-purple-200',
                                default      => 'bg-gray-50 text-gray-700 border-gray-200',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $badgeColor }}">
                                    {{ $usedItem->item_type_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($itemUrl)
                                    <a href="{{ $itemUrl }}" class="font-semibold text-brand hover:underline">{{ $itemModel?->name ?: 'Item dihapus' }}</a>
                                @else
                                    <span class="font-semibold text-gray-900">{{ $itemModel?->name ?: 'Item dihapus' }}</span>
                                @endif
                                <div class="text-xs font-mono text-gray-400">
                                    {{ $itemModel?->part_code ?: ($itemModel?->item_code ?: ($itemModel?->tool_code ?: '-')) }}
                                </div>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $usedItem->qty_used }} {{ $itemModel?->unit ?: 'unit' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                <div class="font-medium text-gray-700">{{ $usedItem->createdBy?->name ?: 'System' }}</div>
                                <div class="text-[10px] text-gray-400">{{ $usedItem->used_at?->format('d M Y H:i') }}</div>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

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
                        <option value="">Pilih Status...</option>
                        @if($wo->status === 'open') <option value="in_progress">In Progress</option> @endif
                        @if($wo->status === 'in_progress') <option value="open">Re-open (Set to Open)</option> @endif
                        @if($wo->status !== 'canceled') <option value="canceled">Cancel Work Order</option> @endif
                        @if(!auth()->user()->isTechnician()) <option value="closed">Closed (Manual)</option> @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Notes <span x-show="newStatus === 'canceled'" class="text-red-500">*</span>
                    </label>
                    <textarea name="notes" rows="3" x-model="statusNotes" 
                              :required="newStatus === 'canceled'"
                              placeholder="Add notes about this status change..." 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none"></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="statusModal=false" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-brand text-gray-900 rounded-lg text-sm font-medium hover:bg-brand-600">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', $asset->name)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('assets.index') }}" class="hover:text-gray-800">Assets</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $asset->name }}</span>@endsection
@section('content')
@php $sc=['active'=>'bg-green-100 text-green-700','inactive'=>'bg-gray-100 text-gray-600','replaced'=>'bg-amber-100 text-amber-700','retired'=>'bg-red-100 text-red-600']; @endphp
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('assets.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $asset->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sc[$asset->status]??'bg-gray-100' }}">{{ ucwords(str_replace('_',' ',$asset->status)) }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $asset->asset_code }} · {{ $asset->category }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button"
                    onclick="printQrLabel({
                        title: '{{ addslashes($asset->name) }}',
                        code: '{{ $asset->asset_code }}',
                        category: '{{ addslashes($asset->category) }}',
                        location: '{{ addslashes($asset->location ?: $asset->transformer_block) }}',
                        qrValue: '{{ route('assets.show', $asset) }}'
                    })"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all"
                    title="Cetak Label QR Code Aset">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="6" height="6" x="3" y="3" rx="1"/><rect width="6" height="6" x="15" y="3" rx="1"/><rect width="6" height="6" x="3" y="15" rx="1"/><path d="M15 15h2v2h-2zM19 19h2v2h-2zM15 19h2v2h-2zM19 15h2v2h-2z"/></svg>
                <span>Print QR Label</span>
            </button>
            @if(auth()->user()->isAdminOrSupervisor())
            <a href="{{ route('assets.edit', $asset) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>Edit</a>
            <button @click="$dispatch('open-delete',{action:'{{ route('assets.destroy',$asset) }}',message:'Delete asset {{ addslashes($asset->name) }}?'})" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>Delete</button>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold text-gray-900">{{ $openWorkOrders }}</p><p class="text-xs text-gray-500 mt-0.5">Open Work Orders</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold text-gray-900">{{ $totalMaintenance }}</p><p class="text-xs text-gray-500 mt-0.5">Maintenance Records</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm text-center"><p class="text-2xl font-bold text-gray-900">{{ round($totalDowntime/60,1) }}h</p><p class="text-xs text-gray-500 mt-0.5">Total Shutdown</p></div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden" x-data="{tab:'overview'}">
        <div class="border-b border-gray-200 px-2">
            <div class="flex gap-1 -mb-px">
                @foreach(['overview'=>'Overview','work_orders'=>'Work Orders','records'=>'Records'] as $key=>$label)
                <button @click="tab='{{ $key }}'" :class="tab==='{{ $key }}' ? 'border-b-2 border-brand text-brand' : 'text-gray-500 hover:text-gray-700'" class="px-4 py-3.5 text-sm font-medium transition-colors whitespace-nowrap">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        {{-- Overview --}}
        <div x-show="tab==='overview'" class="p-6">
            @if($asset->hierarchy_code)
            <div class="mb-5 p-3 bg-blue-50 border border-blue-100 rounded-lg flex items-center gap-3">
                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></svg>
                <div>
                    <span class="text-xs font-medium text-blue-500 uppercase tracking-wide">PV Module Position</span>
                    <p class="text-base font-bold text-blue-800 font-mono">{{ $asset->hierarchy_code }}</p>
                </div>
            </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                @php $fields=[['Location',$asset->location],['Brand',$asset->brand??'—'],['Model',$asset->model??'—'],['Serial #',$asset->serial_number??'—'],['Purchase Date',$asset->purchase_date?->format('M d, Y')??'—'],['Purchase Price',$asset->purchase_price?'IDR '.number_format($asset->purchase_price):'—'],['Warranty Expiry',$asset->warranty_expiry?->format('M d, Y')??'—'],['Description',$asset->description??'—']]; @endphp
                @foreach($fields as [$label,$value])
                <div><dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $label }}</dt><dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd></div>
                @endforeach
            </div>
            @if($asset->photo)
            <div class="mt-6"><img src="{{ Storage::url($asset->photo) }}" class="h-48 rounded-xl object-cover"></div>
            @endif

            {{-- QR Code Asset Tag --}}
            <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/80 p-4 rounded-xl">
                <div class="flex items-center gap-4">
                    <canvas id="asset-qr-canvas" class="w-20 h-20 bg-white p-1 rounded-lg border border-gray-200"></canvas>
                    <div>
                        <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">Scannable Asset Tag</span>
                        <p class="font-mono text-sm font-bold text-gray-900 mt-0.5">{{ $asset->asset_code }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Scan via smartphone untuk akses cepat halaman riwayat aset ini.</p>
                    </div>
                </div>
                <button type="button"
                        onclick="printQrLabel({
                            title: '{{ addslashes($asset->name) }}',
                            code: '{{ $asset->asset_code }}',
                            category: '{{ addslashes($asset->category) }}',
                            location: '{{ addslashes($asset->location ?: $asset->transformer_block) }}',
                            qrValue: '{{ route('assets.show', $asset) }}'
                        })"
                        class="px-3.5 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-bold shadow-sm flex items-center gap-2 self-start sm:self-auto transition-all">
                    <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    Print Label QR
                </button>
            </div>
        </div>

        {{-- Work Orders --}}
        <div x-show="tab==='work_orders'" class="overflow-x-auto">
            @if($asset->workOrders->isEmpty())
            <div class="py-12 text-center text-sm text-gray-400">No work orders for this asset</div>
            @else
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">WO #</th><th class="px-5 py-3 text-left">Title</th><th class="px-5 py-3 text-left">Priority</th><th class="px-5 py-3 text-left">Status</th><th class="px-5 py-3 text-left">Due Date</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($asset->workOrders as $wo)
                @php $pc=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700'];$stc=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700']; @endphp
                <tr class="hover:bg-opacity-90">
                    <td class="px-5 py-3"><a href="{{ route('work-orders.show',$wo) }}" class="text-brand hover:underline font-medium">{{ $wo->wo_number }}</a></td>
                    <td class="px-5 py-3 text-gray-700">{{ $wo->title }}</td>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $pc[$wo->priority]??'' }}">{{ ucfirst($wo->priority) }}</span></td>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $stc[$wo->status]??'' }}">{{ $wo->status_label }}</span></td>
                    <td class="px-5 py-3 text-gray-500">{{ $wo->due_date->format('M d, Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>



        {{-- Records --}}
        <div x-show="tab==='records'" class="overflow-x-auto">
            @if($asset->maintenanceRecords->isEmpty())
            <div class="py-12 text-center text-sm text-gray-400">No maintenance records</div>
            @else
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">Record #</th><th class="px-5 py-3 text-left">Type</th><th class="px-5 py-3 text-left">Date</th><th class="px-5 py-3 text-left">Technician</th><th class="px-5 py-3 text-left">Duration</th></tr></thead>
                <tbody class="divide-y divide-gray-50">
                @foreach($asset->maintenanceRecords as $mr)
                <tr class="hover:bg-opacity-90">
                    <td class="px-5 py-3"><a href="{{ route('maintenance-records.show',$mr) }}" class="text-brand hover:underline font-medium">{{ $mr->record_number }}</a></td>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $mr->type==='preventive'?'bg-blue-100 text-blue-700':'bg-orange-100 text-orange-700' }}">{{ ucfirst($mr->type) }}</span></td>
                    <td class="px-5 py-3 text-gray-600">{{ $mr->maintenance_date->format('M d, Y') }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $mr->technician->name }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ $mr->duration_minutes }} min</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.QRCode) {
            const canvas = document.getElementById('asset-qr-canvas');
            if (canvas) {
                QRCode.toCanvas(canvas, '{{ route('assets.show', $asset) }}', { width: 80, margin: 1 });
            }
        }
    });
</script>
@endpush

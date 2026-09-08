@extends('layouts.app')
@section('title', $sparePart->name)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('spare-parts.index') }}" class="hover:text-gray-800">Spare Parts</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $sparePart->name }}</span>@endsection
@section('content')
@php
    $woUsages = $sparePart->workOrderItems ? $sparePart->workOrderItems->map(fn($usage) => [
        'date' => $usage->used_at,
        'qty' => $usage->qty_used,
        'unit' => $sparePart->unit,
        'source' => 'Work Order',
        'reference' => $usage->workOrder?->wo_number,
        'title' => $usage->workOrder?->title,
        'asset' => $usage->workOrder?->asset ? ($usage->workOrder->asset->name . ($usage->workOrder->asset->transformer_block ? ' (Blok ' . $usage->workOrder->asset->transformer_block . ')' : '')) : ($usage->workOrder?->client_name ?: '-'),
        'url' => $usage->workOrder ? route('work-orders.show', $usage->workOrder) : null,
        'user' => $usage->createdBy?->name,
    ]) : collect();

    $mrUsages = $sparePart->maintenanceRecordParts ? $sparePart->maintenanceRecordParts->map(function($usage) use ($sparePart) {
        $mr = $usage->maintenanceRecord;
        $wo = $mr?->workOrder;
        $asset = $mr?->asset ?? $wo?->asset;
        return [
            'date' => $usage->created_at,
            'qty' => $usage->qty_used,
            'unit' => $sparePart->unit,
            'source' => 'Maintenance Record',
            'reference' => $mr?->record_number,
            'title' => $wo?->title ?? $mr?->findings,
            'asset' => $asset ? ($asset->name . ($asset->transformer_block ? ' (Blok ' . $asset->transformer_block . ')' : '')) : ($wo?->client_name ?: '-'),
            'url' => $mr ? route('maintenance-records.show', $mr) : null,
            'user' => $mr?->technician?->name,
        ];
    }) : collect();

    $usageHistory = $woUsages->merge($mrUsages)->sortByDesc('date');
@endphp

<div class="max-w-7xl space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('spare-parts.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900">{{ $sparePart->name }}</h1>
                @if($sparePart->qty_actual==0)<span class="px-2.5 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">Out of Stock</span>
                @elseif($sparePart->isLowStock())<span class="px-2.5 py-0.5 bg-orange-100 text-orange-600 text-xs font-bold rounded-full">Low Stock</span>
                @else<span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">In Stock</span>@endif
            </div>
            <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $sparePart->part_code }} &bull; {{ $sparePart->category ?? 'Spare Part' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left: Identity & Stock Card --}}
        <div class="lg:col-span-5 space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    @foreach([['Part Code',$sparePart->part_code],['Category',$sparePart->category??'—'],['Unit',$sparePart->unit],['Supplier',$sparePart->supplier??'—'],['Location',$sparePart->location??'—'],['Unit Price',$sparePart->unit_price?'IDR '.number_format($sparePart->unit_price):'—']] as [$l,$v])
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm text-gray-900 font-medium">{{ $v }}</dd></div>
                    @endforeach
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Stock Level</span>
                        <span class="text-sm font-bold text-gray-900">{{ $sparePart->qty_actual }} / Min: {{ $sparePart->qty_minimum }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $sparePart->isLowStock()?'bg-orange-500':'bg-emerald-500' }}" style="width:{{ $sparePart->stock_percentage }}%"></div>
                    </div>
                </div>

                {{-- QR Code Card --}}
                <div class="pt-4 border-t border-gray-100 flex items-center justify-between bg-gray-50/70 p-4 rounded-xl">
                    <div class="flex items-center gap-4">
                        <canvas id="part-qr-canvas" class="w-16 h-16 bg-white p-1 rounded-lg border border-gray-200"></canvas>
                        <div>
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wide">Label Rak Gudang</span>
                            <p class="font-mono text-sm font-bold text-gray-900">{{ $sparePart->part_code }}</p>
                            <p class="text-xs text-gray-500">{{ $sparePart->location ?: 'Gudang' }}</p>
                        </div>
                    </div>
                    <button type="button"
                            onclick="printQrLabel({
                                title: '{{ addslashes($sparePart->name) }}',
                                code: '{{ $sparePart->part_code }}',
                                category: '{{ addslashes($sparePart->category ?? 'Spare Part') }}',
                                location: '{{ addslashes($sparePart->location ?? 'Gudang') }}',
                                qrValue: '{{ route('spare-parts.show', $sparePart) }}'
                            })"
                            class="px-3 py-1.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-bold shadow-sm flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                        Print Label QR
                    </button>
                </div>

                @if(!auth()->user()->isTechnician())
                <div class="flex gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('spare-parts.edit', $sparePart) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Edit</a>
                    <button @click="$dispatch('open-delete',{action:'{{ route('spare-parts.destroy',$sparePart) }}',message:'Delete part {{ addslashes($sparePart->name) }}?'})" class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium">Delete</button>
                </div>
                @endif
            </div>
        </div>

        {{-- Right: Usage History --}}
        <div class="lg:col-span-7">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <span>Usage History</span>
                            <span class="text-xs text-gray-400 font-normal">(Log Pemakaian)</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">Riwayat pemakaian spare part ini di Work Order.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        Total: {{ $usageHistory->sum('qty') }} {{ $sparePart->unit }}
                    </span>
                </div>
                @if($usageHistory->isEmpty())
                <div class="py-16 text-center text-sm text-gray-400">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Belum ada histori pemakaian untuk spare part ini.
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase border-b border-gray-100">
                                <th class="px-5 py-3 text-left">Tanggal</th>
                                <th class="px-5 py-3 text-left">Work Order</th>
                                <th class="px-5 py-3 text-left">Digunakan Pada Aset</th>
                                <th class="px-5 py-3 text-left">Qty</th>
                                <th class="px-5 py-3 text-left">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($usageHistory as $usage)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-5 py-3 text-gray-600 text-xs whitespace-nowrap">
                                    {{ $usage['date']?->format('d M Y H:i') }}
                                </td>
                                <td class="px-5 py-3">
                                    @if($usage['url'])
                                        <a href="{{ $usage['url'] }}" class="text-brand hover:underline font-bold font-mono text-xs">
                                            {{ $usage['reference'] }}
                                        </a>
                                    @else
                                        <span class="font-mono text-xs font-bold text-gray-700">{{ $usage['reference'] ?: '—' }}</span>
                                    @endif
                                    @if($usage['title'])
                                        <span class="block text-xs text-gray-600 truncate max-w-[200px]">{{ $usage['title'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-700">
                                    <span class="font-medium">{{ $usage['asset'] ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-3 font-bold text-gray-800 text-xs whitespace-nowrap">
                                    {{ $usage['qty'] }} {{ $sparePart->unit }}
                                </td>
                                <td class="px-5 py-3 text-gray-500 text-xs">
                                    {{ $usage['user'] ?: 'System' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.QRCode) {
            const canvas = document.getElementById('part-qr-canvas');
            if (canvas) {
                QRCode.toCanvas(canvas, '{{ route('spare-parts.show', $sparePart) }}', { width: 64, margin: 1 });
            }
        }
    });
</script>
@endpush

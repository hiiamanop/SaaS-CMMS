@extends('layouts.app')
@section('title', $tool->name)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('tools.index') }}" class="hover:text-gray-800">Tools</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $tool->name }}</span>@endsection
@section('content')
@php
    $woUsages = $tool->workOrderItems ? $tool->workOrderItems->map(fn($usage) => [
        'date' => $usage->used_at,
        'qty' => $usage->qty_used,
        'unit' => 'unit',
        'source' => 'Work Order',
        'reference' => $usage->workOrder?->wo_number,
        'title' => $usage->workOrder?->title,
        'asset' => $usage->workOrder?->asset ? ($usage->workOrder->asset->name . ($usage->workOrder->asset->transformer_block ? ' (Blok ' . $usage->workOrder->asset->transformer_block . ')' : '')) : ($usage->workOrder?->client_name ?: '-'),
        'url' => $usage->workOrder ? route('work-orders.show', $usage->workOrder) : null,
        'user' => $usage->createdBy?->name,
    ]) : collect();

    $mrUsages = $tool->maintenanceRecordTools ? $tool->maintenanceRecordTools->map(function($usage) use ($tool) {
        $mr = $usage->maintenanceRecord;
        $wo = $mr?->workOrder;
        $asset = $mr?->asset ?? $wo?->asset;
        return [
            'date' => $usage->created_at,
            'qty' => 1,
            'unit' => 'unit',
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
        <a href="{{ route('tools.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-bold text-gray-900">{{ $tool->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $tool->condition === 'good' ? 'bg-green-100 text-green-700' : ($tool->condition === 'damaged' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ ucfirst($tool->condition) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $tool->tool_code }} &bull; {{ $tool->category ?: 'Tool' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Left: Identity & Condition Card --}}
        <div class="lg:col-span-5 space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">Tool Code</dt><dd class="mt-1 text-sm font-mono text-gray-900 font-medium">{{ $tool->tool_code }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">Kategori</dt><dd class="mt-1 text-sm text-gray-900 font-medium">{{ $tool->category ?: 'Tool' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">Merek</dt><dd class="mt-1 text-sm text-gray-900 font-medium">{{ $tool->brand ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">Lokasi Rak</dt><dd class="mt-1 text-sm text-gray-900 font-medium">{{ $tool->location ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">Tersedia</dt><dd class="mt-1 text-lg font-bold text-emerald-600">{{ $tool->qty_available }} / {{ $tool->qty_total }}</dd></div>
                    <div><dt class="text-xs font-medium text-gray-500 uppercase">Kondisi Fisik</dt><dd class="mt-1 text-sm font-bold text-gray-900">{{ ucfirst($tool->condition) }}</dd></div>
                </div>

                @if($tool->description)
                <div class="pt-4 border-t border-gray-100">
                    <dt class="text-xs font-medium text-gray-500 uppercase mb-1">Deskripsi / Catatan</dt>
                    <dd class="text-sm text-gray-700">{{ $tool->description }}</dd>
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
                        <p class="text-xs text-gray-500 mt-0.5">Riwayat pemakaian tool ini di Work Order & pemeliharaan.</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        Total Terpakai: {{ $usageHistory->count() }} kali
                    </span>
                </div>
                @if($usageHistory->isEmpty())
                <div class="py-16 text-center text-sm text-gray-400">
                    <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.6-3.6a6 6 0 01-7.7 7.7L6.4 20.6a2 2 0 01-2.8-2.8l7.2-7.2a6 6 0 017.7-7.7l-3.8 3.4z"/></svg>
                    Belum ada histori pemakaian untuk tool ini.
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
                                    {{ $usage['qty'] }} unit
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

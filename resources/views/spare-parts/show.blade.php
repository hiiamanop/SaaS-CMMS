@extends('layouts.app')
@section('title', $sparePart->name)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('spare-parts.index') }}" class="hover:text-gray-800">Spare Parts</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $sparePart->name }}</span>@endsection
@section('content')
<div class="max-w-2xl space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('spare-parts.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $sparePart->name }}</h1>
        @if($sparePart->qty_actual==0)<span class="px-2.5 py-0.5 bg-red-100 text-red-600 text-xs font-medium rounded-full">Out of Stock</span>
        @elseif($sparePart->isLowStock())<span class="px-2.5 py-0.5 bg-orange-100 text-orange-600 text-xs font-medium rounded-full">Low Stock</span>
        @else<span class="px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">In Stock</span>@endif
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            @foreach([['Part Code',$sparePart->part_code],['Category',$sparePart->category??'—'],['Unit',$sparePart->unit],['Supplier',$sparePart->supplier??'—'],['Location',$sparePart->location??'—'],['Unit Price',$sparePart->unit_price?'IDR '.number_format($sparePart->unit_price):'—']] as [$l,$v])
            <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm text-gray-900 font-medium">{{ $v }}</dd></div>
            @endforeach
        </div>
        <div class="pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Stock Level</span>
                <span class="text-sm font-bold {{ $sparePart->qty_actual==0?'text-red-600':($sparePart->isLowStock()?'text-orange-600':'text-green-600') }}">{{ $sparePart->qty_actual }} / {{ $sparePart->qty_minimum }} min</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full {{ $sparePart->qty_actual==0?'bg-red-500':($sparePart->isLowStock()?'bg-orange-400':'bg-green-500') }}"
                     style="width:{{ $sparePart->qty_minimum>0?min(100,round(($sparePart->qty_actual/($sparePart->qty_minimum*2))*100)):($sparePart->qty_actual>0?100:0) }}%"></div>
            </div>
        </div>
        @if($sparePart->description)<div class="pt-4 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-1">Description</p><p class="text-sm text-gray-700">{{ $sparePart->description }}</p></div>@endif

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
            <button @click="$dispatch('open-delete',{action:'{{ route('spare-parts.destroy',$sparePart) }}',message:'Delete part {{ addslashes($sparePart->name) }}?'})" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Delete</button>
        </div>
        @endif
    </div>

    {{-- Usage history --}}
    @php
        $usageHistory = $sparePart->workOrderItems->map(fn($usage) => [
            'date' => $usage->used_at,
            'qty' => $usage->qty_used,
            'source' => 'Work Order',
            'reference' => $usage->workOrder?->wo_number,
            'title' => $usage->workOrder?->title,
            'url' => $usage->workOrder ? route('work-orders.show', $usage->workOrder) : null,
            'user' => $usage->createdBy?->name,
        ])->merge($sparePart->maintenanceRecordParts->map(fn($usage) => [
            'date' => $usage->created_at,
            'qty' => $usage->qty_used,
            'source' => 'Maintenance Record',
            'reference' => $usage->maintenanceRecord?->record_number,
            'title' => $usage->maintenanceRecord?->workOrder?->title,
            'url' => $usage->maintenanceRecord ? route('maintenance-records.show', $usage->maintenanceRecord) : null,
            'user' => $usage->maintenanceRecord?->technician?->name,
        ]))->sortByDesc('date');
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div><h2 class="font-semibold text-gray-900">Usage History</h2><p class="text-xs text-gray-500 mt-0.5">Riwayat pemakaian pada Work Order dan Maintenance Record.</p></div>
            <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">{{ $usageHistory->sum('qty') }} {{ $sparePart->unit }}</span>
        </div>
        @if($usageHistory->isEmpty())
        <p class="py-10 text-center text-sm text-gray-400">Belum ada histori pemakaian.</p>
        @else
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">Tanggal</th><th class="px-5 py-3 text-left">Sumber</th><th class="px-5 py-3 text-left">Referensi</th><th class="px-5 py-3 text-left">Qty</th><th class="px-5 py-3 text-left">Oleh</th></tr></thead><tbody class="divide-y divide-gray-50">
        @foreach($usageHistory as $usage)
        <tr><td class="px-5 py-3 text-gray-600">{{ $usage['date']?->format('d M Y H:i') }}</td><td class="px-5 py-3 text-gray-600">{{ $usage['source'] }}</td><td class="px-5 py-3 font-medium">@if($usage['url'])<a href="{{ $usage['url'] }}" class="text-brand hover:underline">{{ $usage['reference'] }}</a>@else{{ $usage['reference'] ?: '—' }}@endif @if($usage['title'])<span class="block text-xs text-gray-400">{{ $usage['title'] }}</span>@endif</td><td class="px-5 py-3 font-semibold text-gray-800">{{ $usage['qty'] }} {{ $sparePart->unit }}</td><td class="px-5 py-3 text-gray-500">{{ $usage['user'] ?: 'System' }}</td></tr>
        @endforeach
        </tbody></table></div>
        @endif
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

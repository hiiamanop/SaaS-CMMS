@extends('layouts.app')

@section('title', 'Loss of Production')

@section('breadcrumb')
    <span class="text-sm font-semibold text-gray-700">Loss of Production</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Loss of Production (LOP)</h1>
            <p class="text-sm text-gray-500 mt-0.5">Laporan gangguan & kehilangan produksi kWh</p>
        </div>
        <a href="{{ route('production-losses.create') }}"
            class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-brand/90 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Tambah LOP
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 items-end bg-white border border-gray-200 rounded-xl p-4">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Bulan</label>
            <select name="month" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tahun</label>
            <select name="year" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                @foreach(range(now()->year - 1, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            Filter
        </button>
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Events</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $totalEvents }}</p>
        </div>
        <div class="bg-white border border-red-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-red-400 uppercase tracking-wider">Total LOP</p>
            <p class="text-2xl font-black text-red-600 mt-1">{{ number_format($totalLopKwh, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">kWh</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Durasi</p>
            @php
                $h = intdiv($totalDuration, 60);
                $m = $totalDuration % 60;
            @endphp
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $h }}<span class="text-sm font-normal">j</span> {{ $m }}<span class="text-sm font-normal">m</span></p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hari OFF (÷12j)</p>
            <p class="text-2xl font-black text-yellow-600 mt-1">{{ number_format($totalDuration / 60 / 12, 2) }}</p>
            <p class="text-xs text-gray-400">hari operasional</p>
        </div>
    </div>

    {{-- Category breakdown --}}
    @if($byCategory->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Breakdown per Kategori</p>
        <div class="flex flex-wrap gap-3">
            @foreach(\App\Models\ProductionLoss::$categories as $key => $label)
                @if($byCategory->has($key))
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                    <span class="w-2 h-2 rounded-full bg-{{ match($key) {
                        'planned_maintenance' => 'blue',
                        'corrective_maintenance' => 'yellow',
                        'equipment_fault' => 'red',
                        'grid_fault' => 'purple',
                        'natural' => 'gray',
                        default => 'gray'
                    } }}-400"></span>
                    <span class="text-xs font-semibold text-gray-600">{{ $label }}</span>
                    <span class="text-xs font-black text-gray-900">{{ $byCategory[$key] }}</span>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($losses->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                <p class="text-gray-400 font-semibold">Tidak ada data LOP untuk periode ini</p>
                <a href="{{ route('production-losses.create') }}" class="mt-3 inline-block text-brand text-sm font-semibold hover:underline">
                    + Catat LOP pertama
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu Mulai</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lokasi / Sektor</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Detail</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Durasi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Kapasitas (kW)</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-red-400 uppercase tracking-wider">LOP (kWh)</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">WO</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($losses as $loss)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold text-gray-900">{{ $loss->started_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $loss->started_at->format('H:i') }}
                                    @if($loss->ended_at) — {{ $loss->ended_at->format('H:i') }} @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-xs text-gray-400">{{ $loss->location->name ?? '-' }}</div>
                                <div class="font-semibold text-gray-700">{{ $loss->sector->name ?? 'Semua Sektor' }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                @if($loss->trafo) <span class="font-medium">Trafo:</span> {{ $loss->trafo }} @endif
                                @if($loss->inverter) <br><span class="font-medium">Inv:</span> {{ $loss->inverter }} @endif
                                @if($loss->string_info) <br><span class="font-medium">String:</span> {{ $loss->string_info }} @endif
                                @if($loss->affected_strings) <br><span class="font-medium">Jml:</span> {{ $loss->affected_strings }} string @endif
                            </td>
                            <td class="px-4 py-3">
                                @php $color = $loss->category_color; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                    {{ $loss->category_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700 whitespace-nowrap">
                                {{ $loss->duration_label }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">
                                {{ $loss->affected_capacity_kw ? number_format($loss->affected_capacity_kw, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-red-600">
                                {{ $loss->lop_kwh ? number_format($loss->lop_kwh, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($loss->workOrder)
                                    <a href="{{ route('work-orders.show', $loss->work_order_id) }}"
                                        class="text-xs text-brand hover:underline font-mono">
                                        {{ $loss->workOrder->wo_number }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('production-losses.edit', $loss) }}"
                                        class="text-gray-400 hover:text-brand transition-colors p-1" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <button type="button"
                                        class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                        @click="$dispatch('open-delete', {
                                            action: '{{ route('production-losses.destroy', $loss) }}',
                                            message: 'Hapus data LOP {{ $loss->started_at->format('d/m/Y H:i') }}?'
                                        })">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-red-50 border-t-2 border-red-200">
                        <tr class="font-bold">
                            <td class="px-4 py-3 text-xs text-gray-700 uppercase" colspan="4">TOTAL BULAN</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700 whitespace-nowrap">
                                @php $th = intdiv($totalDuration,60); $tm = $totalDuration%60; @endphp
                                {{ $th }}j {{ $tm }}m
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700">
                                {{ number_format($losses->sum('affected_capacity_kw'), 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-red-600 text-base">
                                {{ number_format($totalLopKwh, 2, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

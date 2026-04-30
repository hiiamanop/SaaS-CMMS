@extends('layouts.app')

@section('title', 'Detail Production Report')

@section('breadcrumb')
    <a href="{{ route('production-reports.index') }}" class="text-sm text-gray-500 hover:text-brand">Production Report</a>
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-sm font-semibold text-gray-700">{{ $productionReport->report_date->format('d M Y') }}</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                Laporan Produksi — {{ $productionReport->report_date->translatedFormat('l, d F Y') }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $productionReport->location->name ?? '-' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('production-reports.edit', $productionReport) }}"
                class="inline-flex items-center gap-2 border border-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">kWh Meter Total</p>
            <p class="text-2xl font-black text-brand mt-1">{{ number_format($productionReport->total_kwh_meter, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">kWh</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">kWh Trafo Total</p>
            <p class="text-2xl font-black text-gray-700 mt-1">{{ number_format($productionReport->total_kwh_trafo, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400">kWh</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sektor Aktif</p>
            <p class="text-2xl font-black text-gray-700 mt-1">{{ $productionReport->entries->count() }}</p>
            <p class="text-xs text-gray-400">sektor</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Dicatat oleh</p>
            <p class="text-base font-bold text-gray-700 mt-1 truncate">{{ $productionReport->creator->name ?? '-' }}</p>
            <p class="text-xs text-gray-400">{{ $productionReport->created_at->format('H:i') }} WIB</p>
        </div>
    </div>

    {{-- Cuaca --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Kondisi Cuaca</h2>
        <div class="flex flex-wrap gap-4">
            @if($productionReport->cerah_hours > 0)
                <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3">
                    <span class="text-2xl">☀️</span>
                    <div>
                        <p class="text-xs text-yellow-600 font-semibold">Cerah</p>
                        <p class="text-xl font-black text-yellow-700">{{ $productionReport->cerah_hours }} <span class="text-sm font-normal">jam</span></p>
                    </div>
                </div>
            @endif
            @if($productionReport->berawan_hours > 0)
                <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                    <span class="text-2xl">⛅</span>
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">Berawan</p>
                        <p class="text-xl font-black text-gray-700">{{ $productionReport->berawan_hours }} <span class="text-sm font-normal">jam</span></p>
                    </div>
                </div>
            @endif
            @if($productionReport->mendung_hours > 0)
                <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                    <span class="text-2xl">🌥</span>
                    <div>
                        <p class="text-xs text-slate-500 font-semibold">Mendung</p>
                        <p class="text-xl font-black text-slate-700">{{ $productionReport->mendung_hours }} <span class="text-sm font-normal">jam</span></p>
                    </div>
                </div>
            @endif
            @if($productionReport->hujan_hours > 0)
                <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3">
                    <span class="text-2xl">🌧</span>
                    <div>
                        <p class="text-xs text-blue-500 font-semibold">Hujan</p>
                        <p class="text-xl font-black text-blue-700">{{ $productionReport->hujan_hours }} <span class="text-sm font-normal">jam</span></p>
                    </div>
                </div>
            @endif
            @if(!$productionReport->cerah_hours && !$productionReport->berawan_hours && !$productionReport->mendung_hours && !$productionReport->hujan_hours)
                <p class="text-sm text-gray-400">Data cuaca tidak dicatat</p>
            @endif
        </div>
    </div>

    {{-- Production per Sector --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Data Produksi per Sektor</h2>

        @if($productionReport->entries->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Tidak ada data produksi sektor</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sektor</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">kWp</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">kWh Trafo</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">kWh Meter</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Target kWh</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Sun Hour</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">CF</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">PR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($productionReport->entries as $entry)
                        @php $target = $targets->get($entry->sector_id); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-semibold text-gray-800">{{ $entry->sector->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400 font-mono">
                                {{ $entry->sector?->capacity_kwp ? number_format($entry->sector->capacity_kwp, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">
                                {{ $entry->kwh_trafo !== null ? number_format($entry->kwh_trafo, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900">
                                {{ $entry->kwh_meter !== null ? number_format($entry->kwh_meter, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-xs">
                                @if($target && $target->target_kwh_daily)
                                    <div class="text-gray-500">{{ number_format($target->target_kwh_daily, 0, ',', '.') }}</div>
                                    @if($entry->kwh_meter && $entry->kwh_meter > 0)
                                        @php $pct = round($entry->kwh_meter / $target->target_kwh_daily * 100, 1); @endphp
                                        <div class="text-{{ $pct >= 100 ? 'green' : ($pct >= 80 ? 'yellow' : 'red') }}-500 font-semibold">
                                            {{ $pct }}%
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">
                                {{ $entry->sun_hour !== null ? number_format($entry->sun_hour, 2, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">
                                @if($entry->capacity_factor !== null)
                                    {{ number_format($entry->capacity_factor * 100, 2, ',', '.') }}%
                                @else -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                @if($entry->performance_ratio !== null)
                                    @php $prOk = $target?->target_pr ? $entry->performance_ratio >= $target->target_pr : true; @endphp
                                    <span class="font-semibold {{ $prOk ? 'text-green-600' : 'text-red-500' }}">
                                        {{ number_format($entry->performance_ratio * 100, 2, ',', '.') }}%
                                    </span>
                                    @if($target?->target_pr)
                                        <div class="text-xs text-gray-400">target {{ number_format($target->target_pr * 100, 1) }}%</div>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-brand-50/50 border-t-2 border-brand/20">
                        <tr class="font-bold">
                            <td class="px-4 py-3 text-xs text-gray-700 uppercase tracking-wider" colspan="2">TOTAL</td>
                            <td class="px-4 py-3 text-right font-mono text-gray-700">
                                {{ number_format($productionReport->total_kwh_trafo, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-brand text-base">
                                {{ number_format($productionReport->total_kwh_meter, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3" colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    @if($productionReport->notes)
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Catatan</h2>
        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $productionReport->notes }}</p>
    </div>
    @endif

</div>
@endsection

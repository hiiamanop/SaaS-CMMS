@extends('layouts.app')

@section('title', 'Production Report')

@section('breadcrumb')
    <span class="text-sm font-semibold text-gray-700">Production Report</span>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Production Report</h1>
            <p class="text-sm text-gray-500 mt-0.5">Laporan produksi harian kWh per sektor PLTS</p>
        </div>
        <a href="{{ route('production-reports.create') }}"
            class="inline-flex items-center gap-2 bg-brand text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-brand/90 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14" />
            </svg>
            Tambah Laporan
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
        <button type="submit"
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            Filter
        </button>
    </form>

    {{-- Summary Cards --}}
    @if($reports->isNotEmpty())
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Hari</p>
            <p class="text-2xl font-black text-gray-900 mt-1">{{ $reports->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total kWh Meter</p>
            <p class="text-2xl font-black text-brand mt-1">{{ number_format($reports->sum('total_kwh_meter'), 0, ',', '.') }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hari Cerah</p>
            <p class="text-2xl font-black text-yellow-500 mt-1">{{ $reports->where('cerah_hours', '>', 0)->count() }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Hari Hujan</p>
            <p class="text-2xl font-black text-blue-500 mt-1">{{ $reports->where('hujan_hours', '>', 0)->count() }}</p>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        @if($reports->isEmpty())
            <div class="py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M9 17H7A5 5 0 0 1 7 7h2 M15 7h2a5 5 0 1 1 0 10h-2 M8 12h8" />
                </svg>
                <p class="text-gray-400 font-semibold">Belum ada laporan untuk periode ini</p>
                <a href="{{ route('production-reports.create') }}" class="mt-3 inline-block text-brand text-sm font-semibold hover:underline">
                    + Tambah laporan pertama
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lokasi</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">kWh Meter Total</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">kWh Trafo Total</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cuaca</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Dicatat oleh</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($reports as $report)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 font-semibold text-gray-900 whitespace-nowrap">
                                {{ $report->report_date->translatedFormat('d M Y') }}
                                <div class="text-xs text-gray-400 font-normal">{{ $report->report_date->translatedFormat('l') }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $report->location->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900">
                                {{ number_format($report->total_kwh_meter, 0, ',', '.') }}
                                <span class="text-xs text-gray-400 font-normal"> kWh</span>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-gray-600">
                                {{ number_format($report->total_kwh_trafo, 0, ',', '.') }}
                                <span class="text-xs text-gray-400 font-normal"> kWh</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if($report->cerah_hours > 0)
                                        <span class="inline-flex items-center gap-1 text-xs bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full px-2 py-0.5 font-medium">
                                            ☀️ {{ $report->cerah_hours }}j
                                        </span>
                                    @endif
                                    @if($report->berawan_hours > 0)
                                        <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-600 border border-gray-200 rounded-full px-2 py-0.5 font-medium">
                                            ⛅ {{ $report->berawan_hours }}j
                                        </span>
                                    @endif
                                    @if($report->mendung_hours > 0)
                                        <span class="inline-flex items-center gap-1 text-xs bg-slate-100 text-slate-600 border border-slate-200 rounded-full px-2 py-0.5 font-medium">
                                            🌥 {{ $report->mendung_hours }}j
                                        </span>
                                    @endif
                                    @if($report->hujan_hours > 0)
                                        <span class="inline-flex items-center gap-1 text-xs bg-blue-50 text-blue-700 border border-blue-200 rounded-full px-2 py-0.5 font-medium">
                                            🌧 {{ $report->hujan_hours }}j
                                        </span>
                                    @endif
                                    @if(!$report->cerah_hours && !$report->berawan_hours && !$report->mendung_hours && !$report->hujan_hours)
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $report->creator->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('production-reports.show', $report) }}"
                                        class="text-gray-400 hover:text-brand transition-colors p-1" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('production-reports.edit', $report) }}"
                                        class="text-gray-400 hover:text-brand transition-colors p-1" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <button type="button" title="Hapus"
                                        class="text-gray-400 hover:text-red-500 transition-colors p-1"
                                        @click="$dispatch('open-delete', {
                                            action: '{{ route('production-reports.destroy', $report) }}',
                                            message: 'Hapus laporan tanggal {{ $report->report_date->format('d/m/Y') }}?'
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
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', 'Performance Report')

@section('breadcrumb')
    <a href="{{ route('production-reports.index') }}" class="text-sm text-gray-500 hover:text-brand">Production Report</a>
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-sm font-semibold text-gray-700">Performance & Target</span>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Performance Report</h1>
            <p class="text-sm text-gray-500 mt-0.5">Aktual vs Target produksi & Performance Ratio bulanan</p>
        </div>
        <a href="{{ route('production-reports.index') }}"
            class="inline-flex items-center gap-2 border border-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            </svg>
            Daily Report
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 items-end bg-white border border-gray-200 rounded-xl p-4">
        @if(auth()->user()->isAdmin())
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Lokasi</label>
            <select name="location_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ $loc->id == $locationId ? 'selected' : '' }}>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
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
            Tampilkan
        </button>
    </form>

    @if($reports->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M18 20V10 M12 20V4 M6 20v-6"/>
            </svg>
            <p class="text-gray-400 font-semibold">Belum ada data laporan untuk periode ini</p>
            <a href="{{ route('production-reports.create') }}" class="mt-3 inline-block text-brand text-sm font-semibold hover:underline">
                + Tambah laporan produksi
            </a>
        </div>
    @else

    {{-- Summary per Sector --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800">Ringkasan Bulanan per Sektor</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }} —
                {{ $reports->count() }} hari dicatat dari {{ $daysInMonth }} hari
            </p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sektor</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">kWh Aktual</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Target Bulanan</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Pencapaian</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Avg PR</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Target PR</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Target Harian</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Hari Dicatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sectorSummary as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-800">{{ $row['sector']->name }}</td>
                        <td class="px-4 py-3 text-right font-mono font-semibold text-brand">
                            {{ number_format($row['total_kwh'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-500">
                            {{ $row['target_monthly'] ? number_format($row['target_monthly'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($row['achievement_pct'] !== null)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $row['achievement_pct'] >= 100 ? 'bg-green-100 text-green-700' :
                                       ($row['achievement_pct'] >= 80 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ number_format($row['achievement_pct'], 1) }}%
                                </span>
                            @else
                                <span class="text-xs text-gray-400">No target</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-700">
                            @if($row['avg_pr'] !== null)
                                <span class="{{ $row['avg_pr'] >= ($row['target_pr'] ?? 0.75) ? 'text-green-600' : 'text-red-500' }} font-semibold">
                                    {{ number_format($row['avg_pr'] * 100, 2) }}%
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-400 text-xs">
                            {{ $row['target_pr'] ? number_format($row['target_pr'] * 100, 1) . '%' : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-500 text-xs">
                            {{ $row['target_daily'] ? number_format($row['target_daily'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">
                            {{ $row['days_recorded'] }} / {{ $daysInMonth }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- kWh Chart --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="font-bold text-gray-800 mb-1">Produksi kWh Harian</h2>
            <p class="text-xs text-gray-400 mb-4">kWh Meter Total — {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}</p>
            <canvas id="kwhChart" height="200"></canvas>
        </div>

        {{-- PR Chart --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="font-bold text-gray-800 mb-1">Performance Ratio Harian</h2>
            <p class="text-xs text-gray-400 mb-4">Rata-rata PR semua sektor (%)</p>
            <canvas id="prChart" height="200"></canvas>
        </div>
    </div>

    {{-- Daily detail table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800">Detail Harian</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Cuaca</th>
                        @foreach($sectors as $sector)
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase whitespace-nowrap">
                            {{ $sector->name }}<br>
                            <span class="font-normal text-gray-400">kWh / PR</span>
                        </th>
                        @endforeach
                        <th class="px-4 py-3 text-right text-xs font-bold text-brand uppercase">Total kWh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php
                            $date   = \Carbon\Carbon::create($year, $month, $d);
                            $report = $reports->first(fn($r) => $r->report_date->day === $d);
                            $entries = $report ? $report->entries->keyBy('sector_id') : collect();
                        @endphp
                        <tr class="{{ $report ? 'hover:bg-gray-50' : 'bg-gray-50/40 text-gray-400' }}">
                            <td class="px-4 py-2.5 whitespace-nowrap">
                                <span class="{{ $report ? 'font-semibold text-gray-800' : 'text-gray-400' }}">
                                    {{ $date->translatedFormat('d D') }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                @if($report)
                                    <div class="flex gap-1 flex-wrap">
                                        @if($report->cerah_hours > 0) <span class="text-xs">☀️{{ $report->cerah_hours }}j</span> @endif
                                        @if($report->berawan_hours > 0) <span class="text-xs">⛅{{ $report->berawan_hours }}j</span> @endif
                                        @if($report->mendung_hours > 0) <span class="text-xs">🌥{{ $report->mendung_hours }}j</span> @endif
                                        @if($report->hujan_hours > 0) <span class="text-xs">🌧{{ $report->hujan_hours }}j</span> @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            @foreach($sectors as $sector)
                            <td class="px-4 py-2.5 text-right font-mono text-xs">
                                @if($entry = $entries->get($sector->id))
                                    <div class="font-semibold text-gray-700">{{ number_format($entry->kwh_meter ?? 0, 0, ',', '.') }}</div>
                                    @if($entry->performance_ratio !== null)
                                        <div class="text-{{ $entry->performance_ratio >= 0.75 ? 'green' : 'red' }}-500">
                                            PR {{ number_format($entry->performance_ratio * 100, 1) }}%
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="px-4 py-2.5 text-right font-mono font-bold text-brand">
                                @if($report)
                                    {{ number_format($report->total_kwh_meter, 0, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot class="bg-brand-50/50 border-t-2 border-brand/20">
                    <tr class="font-bold">
                        <td class="px-4 py-3 text-xs text-gray-700 uppercase" colspan="2">TOTAL BULAN</td>
                        @foreach($sectors as $sector)
                        <td class="px-4 py-3 text-right font-mono text-gray-700">
                            @php $s = collect($sectorSummary)->first(fn($r) => $r['sector']->id === $sector->id); @endphp
                            {{ $s ? number_format($s['total_kwh'], 0, ',', '.') : '—' }}
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-right font-mono text-brand">
                            {{ number_format($reports->sum('total_kwh_meter'), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @endif

</div>
@endsection

@push('scripts')
<script>
const labels = @json($chartLabels);
const kwhData = @json($chartKwh);
const prData  = @json($chartPr);

// Find target daily from first sector summary that has one
const targetKwh = {{ collect($sectorSummary)->sum('target_daily') ?? 'null' }};
const targetPr  = {{ collect($sectorSummary)->whereNotNull('target_pr')->avg('target_pr') ?? 'null' }};

// kWh Chart
new Chart(document.getElementById('kwhChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'kWh Aktual',
                data: kwhData,
                backgroundColor: 'rgba(22, 163, 74, 0.7)',
                borderRadius: 4,
            },
            ...(targetKwh ? [{
                label: 'Target Harian (Total)',
                data: labels.map(() => targetKwh),
                type: 'line',
                borderColor: '#f59e0b',
                borderDash: [6, 3],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
            }] : [])
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true, position: 'bottom' } },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: v => v.toLocaleString('id')
                }
            }
        }
    }
});

// PR Chart
new Chart(document.getElementById('prChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'PR (%)',
                data: prData,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                pointRadius: 3,
                fill: true,
                tension: 0.3,
                spanGaps: true,
            },
            ...(targetPr ? [{
                label: 'Target PR',
                data: labels.map(() => targetPr * 100),
                borderColor: '#f59e0b',
                borderDash: [6, 3],
                borderWidth: 2,
                pointRadius: 0,
                fill: false,
            }] : [])
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true, position: 'bottom' } },
        scales: {
            y: {
                beginAtZero: false,
                min: 0,
                max: 100,
                ticks: { callback: v => v + '%' }
            }
        }
    }
});
</script>
@endpush

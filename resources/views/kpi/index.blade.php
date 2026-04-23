@extends('layouts.app')
@section('title', 'KPI Dashboard')

@section('breadcrumb')
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">KPI Dashboard</span>
@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Date Range Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
        <form method="GET" action="{{ route('kpi.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $dateFrom->toDateString() }}"
                    class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $dateTo->toDateString() }}"
                    class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                Apply
            </button>
            <a href="{{ route('kpi.index') }}" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50">
                Reset
            </a>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        @php
        $cards = [
            ['label'=>'MTTR', 'value'=>$mttr.' h', 'sub'=>'Mean Time to Repair', 'color'=>'blue',
             'icon'=>'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M12 6v6l4 2'],
            ['label'=>'MTBF', 'value'=>$mtbf.' h', 'sub'=>'Mean Time Between Failures', 'color'=>'purple',
             'icon'=>'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M12 6v6l4 2'],
            ['label'=>'PM Completed', 'value'=>$pmCompliance, 'sub'=>'Checksheets Finished', 'color'=>'green',
             'icon'=>'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01 9 11.01'],
            ['label'=>'Completion Rate', 'value'=>$completionRate.'%', 'sub'=>'Work Orders Closed', 'color'=>'teal',
             'icon'=>'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
            ['label'=>'Overdue WOs', 'value'=>$overdueWo, 'sub'=>'Past Due Date', 'color'=>'red',
             'icon'=>'m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z M12 9v4 M12 17h.01'],
            ['label'=>'Total Shutdown', 'value'=>$totalShutdownHours.' h', 'sub'=>'MR + WO dalam periode', 'color'=>'orange',
             'icon'=>'M18.36 6.64a9 9 0 1 1-12.73 0 M12 2v4'],
        ];
        $colorMap = ['blue'=>['bg'=>'bg-blue-50','text'=>'text-blue-700','icon'=>'text-blue-500'],'purple'=>['bg'=>'bg-purple-50','text'=>'text-purple-700','icon'=>'text-purple-500'],'green'=>['bg'=>'bg-green-50','text'=>'text-green-700','icon'=>'text-green-500'],'teal'=>['bg'=>'bg-teal-50','text'=>'text-teal-700','icon'=>'text-teal-500'],'red'=>['bg'=>'bg-red-50','text'=>'text-red-700','icon'=>'text-red-500'],'orange'=>['bg'=>'bg-orange-50','text'=>'text-orange-700','icon'=>'text-orange-500'],'yellow'=>['bg'=>'bg-yellow-50','text'=>'text-yellow-700','icon'=>'text-yellow-500']];
        @endphp

        @foreach($cards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 {{ $c['bg'] }} rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 {{ $c['icon'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $c['text'] }}">{{ $card['value'] }}</p>
            <p class="text-xs font-semibold text-gray-700 mt-1">{{ $card['label'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $card['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Monthly Work Orders --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Work Orders by Month</h2>
            <div style="height:220px"><canvas id="chartMonthlyWo"></canvas></div>
        </div>

        {{-- Priority Distribution --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Work Orders by Priority</h2>
            <div class="flex items-center justify-center" style="height:260px">
                <canvas id="chartPriority"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- MTTR Trend --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">MTTR Trend (hours)</h2>
            <div style="height:220px"><canvas id="chartMttr"></canvas></div>
        </div>

        {{-- Shutdown Trend --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-1">Shutdown Trend (hours)</h2>
            <p class="text-xs text-gray-400 mb-4">Akumulasi durasi shutdown dari Maintenance Record + WO</p>
            <div style="height:220px"><canvas id="chartShutdown"></canvas></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const monthlyData = @json($monthlyData);
const mttrTrend = @json($mttrTrend);
const shutdownTrend = @json($shutdownTrend);
const byPriority = @json($byPriority);

const labels = monthlyData.map(d => d.label);

// Monthly WO stacked bar
new Chart(document.getElementById('chartMonthlyWo'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Open', data: monthlyData.map(d => d.open), backgroundColor: '#3b82f6' },
            { label: 'In Progress', data: monthlyData.map(d => d.in_progress), backgroundColor: '#f59e0b' },
            { label: 'Pending Review', data: monthlyData.map(d => d.pending_review), backgroundColor: '#8b5cf6' },
            { label: 'Closed', data: monthlyData.map(d => d.closed), backgroundColor: '#10b981' },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
        scales: {
            x: { stacked: true, ticks: { font: { size: 11 } } },
            y: { stacked: true, beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 } }
        }
    }
});

// Priority doughnut
new Chart(document.getElementById('chartPriority'), {
    type: 'doughnut',
    data: {
        labels: ['Low', 'Medium', 'High', 'Critical'],
        datasets: [{
            data: [byPriority.low, byPriority.medium, byPriority.high, byPriority.critical],
            backgroundColor: ['#6b7280', '#3b82f6', '#f59e0b', '#ef4444'],
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
        }
    }
});

// MTTR trend line
new Chart(document.getElementById('chartMttr'), {
    type: 'line',
    data: {
        labels: mttrTrend.map(d => d.label),
        datasets: [{
            label: 'MTTR (h)',
            data: mttrTrend.map(d => d.value),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            fill: true,
            tension: 0.3,
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, ticks: { font: { size: 11 } } }
        }
    }
});

// Shutdown trend bar
new Chart(document.getElementById('chartShutdown'), {
    type: 'bar',
    data: {
        labels: shutdownTrend.map(d => d.label),
        datasets: [{
            label: 'Shutdown (h)',
            data: shutdownTrend.map(d => d.value),
            backgroundColor: 'rgba(234,179,8,0.7)',
            borderColor: '#ca8a04',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, ticks: { font: { size: 11 } } }
        }
    }
});

</script>
@endpush

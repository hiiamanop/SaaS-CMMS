@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">Dashboard</span>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
        </div>
        <span class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</span>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Total Assets</span>
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $totalAssets }}</p>
            <a href="{{ route('assets.index') }}" class="text-xs text-brand hover:underline mt-1 inline-block">View all →</a>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Open Work Orders</span>
                <div class="w-9 h-9 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $openWorkOrders }}</p>
            <a href="{{ route('work-orders.index', ['status'=>'open']) }}" class="text-xs text-yellow-600 hover:underline mt-1 inline-block">View open →</a>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Overdue Tasks</span>
                <div class="w-9 h-9 bg-red-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $overdueWorkOrders }}</p>
            <a href="{{ route('work-orders.index', ['filter'=>'overdue']) }}" class="text-xs text-red-600 hover:underline mt-1 inline-block">View overdue →</a>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Low Stock Alerts</span>
                <div class="w-9 h-9 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $lowStockCount }}</p>
            <a href="{{ route('spare-parts.index', ['filter'=>'low_stock']) }}" class="text-xs text-orange-600 hover:underline mt-1 inline-block">View parts →</a>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Pending Inspections</span>
                <div class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15L11 17L15 13"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $pendingChecksheets }}</p>
            <a href="{{ route('checksheet.index', ['status'=>'draft']) }}" class="text-xs text-purple-600 hover:underline mt-1 inline-block">View checksheets →</a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Chart --}}
        <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <h2 class="font-semibold text-gray-900 mb-4">Work Order Trend (Last 6 Months)</h2>
            <div style="height:220px"><canvas id="woChart"></canvas></div>
        </div>

        {{-- Upcoming Schedules --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Upcoming (7 days)</h2>
                <a href="{{ route('maintenance-schedules.index') }}" class="text-xs text-brand hover:underline">View all</a>
            </div>
            @forelse($upcomingSchedules as $session)
            <div class="flex items-start gap-3 py-3.5 border-b border-gray-50 last:border-0 hover:bg-brand-50/20 transition-all rounded-xl px-2 -mx-2">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand/20 to-brand/10 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="min-w-0 flex-1 pr-2">
                    <p class="text-sm font-black bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 truncate uppercase tracking-tight">
                        {{ $session->period_label }}
                    </p>
                    <p class="text-xs font-bold text-brand mt-0.5 truncate">{{ $session->schedule->trafo_name ?? $session->equipment_location }}</p>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-[9px] text-emerald-600 font-black uppercase tracking-widest">{{ $session->schedule->location->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('checksheet.fill', $session) }}" class="inline-flex items-center justify-center h-8 px-4 rounded-full bg-brand-dark text-white font-bold text-[10px] font-black text-gray-900 hover:bg-brand hover:scale-105 transition-all shadow-lg shadow-gray-200">
                        MULAI
                    </a>
                </div>
            </div>
            @empty
            <div class="py-10 text-center">
                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <p class="text-xs text-gray-400 font-medium">No upcoming draft schedules</p>
                <p class="text-[9px] text-gray-400 mt-1 uppercase tracking-tighter">Check Maint. Schedule to generate sessions</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Recent Work Orders --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Recent Work Orders</h2>
                <a href="{{ route('work-orders.index') }}" class="text-xs text-brand hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wide">
                        <th class="px-5 py-3 text-left">WO #</th>
                        <th class="px-5 py-3 text-left">Asset</th>
                        <th class="px-5 py-3 text-left">Priority</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                    @foreach($recentWorkOrders as $wo)
                    <tr class="hover:bg-opacity-90 transition-colors">
                        @php $isFollowUp = str_contains(strtoupper($wo->title), '[FOLLOW-UP]'); @endphp
                        <td class="px-5 py-3"><a href="{{ route('work-orders.show', $wo) }}" class="font-medium {{ $isFollowUp ? 'text-red-600' : 'text-gray-900' }} hover:underline">{{ $wo->wo_number }}</a></td>
                        <td class="px-5 py-3 text-gray-600 truncate max-w-[150px]">
                            @if($wo->asset)
                                <span class="text-gray-900 font-bold">{{ $wo->asset->name }}</span>
                            @else
                                <span class="text-gray-900 font-bold">{{ $wo->client_name ?: 'External Client' }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @php $pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-yellow-100 text-yellow-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $pColors[$wo->priority] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($wo->priority) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php $sColors=['open'=>'bg-blue-50 text-blue-700','in_progress'=>'bg-yellow-50 text-yellow-700','pending_review'=>'bg-purple-50 text-purple-700','closed'=>'bg-emerald-50 text-emerald-700']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $sColors[$wo->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $wo->status_label }}</span>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Low Stock Parts --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Low Stock Parts</h2>
                <a href="{{ route('spare-parts.index', ['filter'=>'low_stock']) }}" class="text-xs text-brand hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockParts as $part)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <a href="{{ route('spare-parts.show', $part) }}" class="text-sm font-medium text-gray-900 hover:text-brand truncate max-w-[200px]">{{ $part->name }}</a>
                        <span class="text-xs {{ $part->qty_actual===0 ? 'text-red-600 font-semibold' : 'text-orange-600' }}">{{ $part->qty_actual }} / {{ $part->qty_minimum }} min</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $part->qty_actual===0 ? 'bg-red-500' : 'bg-orange-400' }}" style="width:{{ min(100, $part->qty_minimum>0 ? round(($part->qty_actual/$part->qty_minimum)*50) : 0) }}%"></div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/></svg>
                    <p class="text-sm text-gray-400">All parts sufficiently stocked</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('woChart');
const labels = @json(array_column($chartData, 'label'));
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Open', data: @json(array_column($chartData,'open')), backgroundColor: '#93c5fd' },
            { label: 'Closed', data: @json(array_column($chartData,'closed')), backgroundColor: '#6ee7b7' },
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
});
</script>
@endpush

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
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $totalAssets }}</p>
            <a href="{{ route('assets.index') }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View all →</a>
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
                <a href="{{ route('maintenance-schedules.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>
            @forelse($upcomingSchedules as $schedule)
            <div class="flex items-start gap-3 py-2.5 border-b border-gray-50 last:border-0">
                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ $schedule->type==='preventive' ? 'bg-blue-500' : 'bg-orange-500' }}"></div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $schedule->title }}</p>
                    <p class="text-xs text-gray-500">{{ $schedule->asset->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $schedule->next_due_date->format('M d, Y') }}</p>
                </div>
            </div>
            @empty
            <div class="py-8 text-center">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <p class="text-sm text-gray-400">No upcoming schedules</p>
            </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Recent Work Orders --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Recent Work Orders</h2>
                <a href="{{ route('work-orders.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
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
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3"><a href="{{ route('work-orders.show', $wo) }}" class="font-medium text-blue-600 hover:underline">{{ $wo->wo_number }}</a></td>
                        <td class="px-5 py-3 text-gray-600 truncate max-w-[150px]">{{ $wo->asset->name }}</td>
                        <td class="px-5 py-3">
                            @php $pColors=['low'=>'bg-gray-100 text-gray-600','medium'=>'bg-blue-100 text-blue-700','high'=>'bg-orange-100 text-orange-700','critical'=>'bg-red-100 text-red-700']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $pColors[$wo->priority] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($wo->priority) }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @php $sColors=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-yellow-100 text-yellow-700','pending_review'=>'bg-purple-100 text-purple-700','closed'=>'bg-green-100 text-green-700']; @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sColors[$wo->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $wo->status_label }}</span>
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
                <a href="{{ route('spare-parts.index', ['filter'=>'low_stock']) }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockParts as $part)
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <a href="{{ route('spare-parts.show', $part) }}" class="text-sm font-medium text-gray-900 hover:text-blue-600 truncate max-w-[200px]">{{ $part->name }}</a>
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
            { label: 'In Progress', data: @json(array_column($chartData,'in_progress')), backgroundColor: '#fde68a' },
            { label: 'Closed', data: @json(array_column($chartData,'closed')), backgroundColor: '#6ee7b7' },
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
});
</script>
@endpush

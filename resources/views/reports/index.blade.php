@extends('layouts.app')
@section('title', 'Monthly Report')
@section('content')

<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h1 class="text-xl font-bold text-gray-900">Monthly Report</h1>
            <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2">
                <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
                <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium">Apply</button>
                @if(Route::has('reports.pdf'))
                    <a href="{{ route('reports.pdf', ['year' => $year, 'month' => $month]) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">Export PDF</a>
                @endif
            </form>
        </div>

        {{-- Activities --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Work Orders ({{ $workOrders->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($workOrders as $wo)
                        <li>{{ $wo->wo_number }} — {{ $wo->title }} <span class="text-xs uppercase text-gray-400">({{ $wo->status }})</span></li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Maintenance Records ({{ $records->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($records as $r)
                        <li>{{ $r->record_number }} — {{ $r->asset->name ?? '—' }} <span class="text-xs uppercase text-gray-400">({{ $r->status_after }})</span></li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Checksheets ({{ $checksheets->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($checksheets as $cs)
                        <li>{{ $cs->schedule->trafo_name ?? ('Session #'.$cs->id) }}</li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Findings ({{ $findings->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($findings as $f)
                        <li>{{ $f->title }} <span class="text-xs uppercase text-gray-400">({{ $f->status }})</span></li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
        </div>

        {{-- Items used --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Barang Terpakai</h2></div>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left">Item</th><th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Qty</th><th class="px-5 py-3 text-left">Value</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($spareParts as $it)
                        <tr><td class="px-5 py-3">{{ $it['name'] }}</td><td class="px-5 py-3 text-gray-500">Spare Part</td><td class="px-5 py-3">{{ $it['qty'] }} {{ $it['unit'] }}</td><td class="px-5 py-3">IDR {{ number_format($it['value']) }}</td></tr>
                    @endforeach
                    @foreach($consumables as $it)
                        <tr><td class="px-5 py-3">{{ $it['name'] }}</td><td class="px-5 py-3 text-gray-500">Consumable</td><td class="px-5 py-3">{{ $it['qty'] }} {{ $it['unit'] }}</td><td class="px-5 py-3">IDR {{ number_format($it['value']) }}</td></tr>
                    @endforeach
                    @foreach($tools as $it)
                        <tr><td class="px-5 py-3">{{ $it['name'] }}</td><td class="px-5 py-3 text-gray-500">Tool</td><td class="px-5 py-3">{{ $it['count'] }}x used</td><td class="px-5 py-3">—</td></tr>
                    @endforeach
                    @if($spareParts->isEmpty() && $consumables->isEmpty() && $tools->isEmpty())
                        <tr><td colspan="4" class="px-5 py-3 text-gray-400 text-center">No items used this month</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

@endsection

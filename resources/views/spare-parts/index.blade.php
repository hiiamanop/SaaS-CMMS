@extends('layouts.app')
@section('title','Spare Parts')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Spare Parts</span>@endsection
@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Spare Parts</h1>
            <p class="text-sm text-gray-500 mt-0.5">Inventory management</p>
        </div>
        @if(!auth()->user()->isTechnician())
        <a href="{{ route('spare-parts.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add Part
        </a>
        @endif
    </div>

    @if($lowStockCount > 0)
    <div class="flex items-center gap-3 bg-orange-50 border border-orange-200 text-orange-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        <span><strong>{{ $lowStockCount }} part(s)</strong> are at or below minimum stock level.</span>
        <a href="{{ route('spare-parts.index', ['filter'=>'low_stock']) }}" class="ml-auto font-medium underline">View</a>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search parts..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
            </select>
            <select name="filter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Stock</option>
                <option value="low_stock" {{ request('filter')=='low_stock'?'selected':'' }}>Low Stock Only</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('spare-parts.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($parts->isEmpty())
        <div class="py-16 text-center"><p class="text-gray-400">No spare parts found</p></div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Code</th><th class="px-5 py-3 text-left">Name</th><th class="px-5 py-3 text-left">Category</th><th class="px-5 py-3 text-left">Stock Level</th><th class="px-5 py-3 text-left">Unit Price</th><th class="px-5 py-3 text-left">Location</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($parts as $part)
            <tr class="hover:bg-gray-50 transition-colors {{ $part->isLowStock() ? 'bg-orange-50/30' : '' }}">
                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $part->part_code }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900">{{ $part->name }}</span>
                        @if($part->qty_actual == 0)<span class="px-1.5 py-0.5 bg-red-100 text-red-600 text-xs rounded-full font-medium">Out of Stock</span>
                        @elseif($part->isLowStock())<span class="px-1.5 py-0.5 bg-orange-100 text-orange-600 text-xs rounded-full font-medium">Low Stock</span>@endif
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $part->category }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2 min-w-[140px]">
                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $part->qty_actual==0?'bg-red-500':($part->isLowStock()?'bg-orange-400':'bg-green-500') }}"
                                 style="width:{{ $part->qty_minimum>0 ? min(100,round(($part->qty_actual/($part->qty_minimum*2))*100)) : ($part->qty_actual>0?100:0) }}%"></div>
                        </div>
                        <span class="text-xs text-gray-600 whitespace-nowrap font-medium">{{ $part->qty_actual }} {{ $part->unit }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">Min: {{ $part->qty_minimum }}</p>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $part->unit_price ? 'IDR '.number_format($part->unit_price) : '—' }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $part->location }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if(!auth()->user()->isTechnician())
                        <div x-data="{open:false}" class="relative">
                            <button @click="open=!open" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-100">Adjust Stock</button>
                            <div x-show="open" @click.outside="open=false" x-transition class="absolute right-0 top-full mt-1 w-56 bg-white rounded-xl shadow-lg border border-gray-200 p-4 z-20" style="display:none">
                                <p class="text-sm font-medium text-gray-900 mb-3">Adjust Stock</p>
                                <form action="{{ route('spare-parts.adjust-stock', $part) }}" method="POST" class="space-y-3">
                                    @csrf
                                    <select name="type" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="add">Add Stock</option>
                                        <option value="reduce">Reduce Stock</option>
                                    </select>
                                    <input name="quantity" type="number" min="1" placeholder="Quantity" required class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700">Update</button>
                                </form>
                            </div>
                        </div>
                        <a href="{{ route('spare-parts.edit', $part) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('spare-parts.destroy',$part) }}',message:'Delete part {{ addslashes($part->name) }}?'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $parts->links() }}</div>
        @endif
    </div>
</div>
@endsection

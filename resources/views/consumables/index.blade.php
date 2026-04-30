@extends('layouts.app')
@section('title','Consumables')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Consumables</span>@endsection
@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Consumables</h1>
            <p class="text-sm text-gray-500 mt-0.5">Cleaning and safety materials management</p>
        </div>
        @if(!auth()->user()->isTechnician())
        <div class="flex flex-col items-end gap-1" x-data="{ uploading: false }">
            <div class="flex items-center gap-2">
                <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data" class="hidden" id="import-form">
                    @csrf
                    <input type="hidden" name="type" value="consumable">
                    <input type="file" name="file" id="import-file" @change="uploading = true; $el.form.submit()" accept=".csv, .xlsx, .xls">
                </form>

                <button @click="document.getElementById('import-file').click()" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all h-[38px]"
                        :disabled="uploading">
                    <svg x-show="!uploading" class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242M12 12v9m-4-4l4 4 4-4"/></svg>
                    <svg x-show="uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="uploading ? 'Importing...' : 'Import Consumables'"></span>
                </button>

                <a href="{{ route('consumables.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm font-bold hover:bg-opacity-90 shadow-sm transition-all h-[38px]">
                    <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add Item
                </a>
            </div>
            <span class="text-[10px] text-gray-400 font-medium italic">Support: .xlsx, .csv</span>
        </div>
        @endif
    </div>

    @if($lowStockCount > 0)
    <div class="flex items-center gap-3 bg-orange-50 border border-orange-200 text-orange-800 rounded-lg px-4 py-3 text-sm">
        <svg class="w-4 h-4 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        <span><strong>{{ $lowStockCount }} item(s)</strong> are at or below minimum stock level.</span>
        <a href="{{ route('consumables.index', ['filter'=>'low_stock']) }}" class="ml-auto font-medium underline">View</a>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search consumables..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <select name="filter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Stock</option>
                <option value="low_stock" {{ request('filter')=='low_stock'?'selected':'' }}>Low Stock Only</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-dark text-white font-bold rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('consumables.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($items->isEmpty())
        <div class="py-16 text-center"><p class="text-gray-400">No consumables found</p></div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Code</th><th class="px-5 py-3 text-left">Name</th><th class="px-5 py-3 text-left">Stock Level</th><th class="px-5 py-3 text-left">Location</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($items as $item)
            <tr class="hover:bg-opacity-90 transition-colors {{ $item->qty_actual <= $item->qty_minimum ? 'bg-orange-50/30' : '' }}">
                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $item->item_code }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-gray-900">{{ $item->name }}</span>
                        @if($item->qty_actual <= $item->qty_minimum)
                            <span class="px-1.5 py-0.5 bg-orange-100 text-orange-600 text-xs rounded-full font-medium">Low Stock</span>
                        @endif
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-600 font-medium">{{ $item->qty_actual }} {{ $item->unit }} <span class="text-xs text-gray-400 font-normal">/ Min: {{ $item->qty_minimum }}</span></td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $item->location }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1">
                        @if(!auth()->user()->isTechnician())
                        <a href="{{ route('consumables.edit', $item) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('consumables.destroy',$item) }}',message:'Delete item {{ addslashes($item->name) }}?'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection

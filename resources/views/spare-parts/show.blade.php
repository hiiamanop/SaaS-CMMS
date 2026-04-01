@extends('layouts.app')
@section('title', $sparePart->name)
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('spare-parts.index') }}" class="hover:text-gray-800">Spare Parts</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">{{ $sparePart->name }}</span>@endsection
@section('content')
<div class="max-w-2xl space-y-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('spare-parts.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $sparePart->name }}</h1>
        @if($sparePart->qty_actual==0)<span class="px-2.5 py-0.5 bg-red-100 text-red-600 text-xs font-medium rounded-full">Out of Stock</span>
        @elseif($sparePart->isLowStock())<span class="px-2.5 py-0.5 bg-orange-100 text-orange-600 text-xs font-medium rounded-full">Low Stock</span>
        @else<span class="px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full">In Stock</span>@endif
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            @foreach([['Part Code',$sparePart->part_code],['Category',$sparePart->category??'—'],['Unit',$sparePart->unit],['Supplier',$sparePart->supplier??'—'],['Location',$sparePart->location??'—'],['Unit Price',$sparePart->unit_price?'IDR '.number_format($sparePart->unit_price):'—']] as [$l,$v])
            <div><dt class="text-xs font-medium text-gray-500 uppercase">{{ $l }}</dt><dd class="mt-1 text-sm text-gray-900 font-medium">{{ $v }}</dd></div>
            @endforeach
        </div>
        <div class="pt-4 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Stock Level</span>
                <span class="text-sm font-bold {{ $sparePart->qty_actual==0?'text-red-600':($sparePart->isLowStock()?'text-orange-600':'text-green-600') }}">{{ $sparePart->qty_actual }} / {{ $sparePart->qty_minimum }} min</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="h-2.5 rounded-full {{ $sparePart->qty_actual==0?'bg-red-500':($sparePart->isLowStock()?'bg-orange-400':'bg-green-500') }}"
                     style="width:{{ $sparePart->qty_minimum>0?min(100,round(($sparePart->qty_actual/($sparePart->qty_minimum*2))*100)):($sparePart->qty_actual>0?100:0) }}%"></div>
            </div>
        </div>
        @if($sparePart->description)<div class="pt-4 border-t border-gray-100"><p class="text-xs font-medium text-gray-500 uppercase mb-1">Description</p><p class="text-sm text-gray-700">{{ $sparePart->description }}</p></div>@endif
        @if(!auth()->user()->isTechnician())
        <div class="flex gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('spare-parts.edit', $sparePart) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
            <button @click="$dispatch('open-delete',{action:'{{ route('spare-parts.destroy',$sparePart) }}',message:'Delete part {{ addslashes($sparePart->name) }}?'})" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Delete</button>
        </div>
        @endif
    </div>
</div>
@endsection

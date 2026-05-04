@extends('layouts.app')
@section('title', 'Add Consumable')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('consumables.index') }}" class="hover:text-gray-800">Consumables</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Add Consumable</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('consumables.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Add Consumable</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('consumables.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @if(!field_is_hidden('consumables', 'item_code'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'item_code', 'Item Code') !!}</label><input name="item_code" value="{{ old('item_code') }}" {{ field_attributes('consumables', 'item_code') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('item_code') border-red-400 @enderror">@error('item_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                @endif

                @if(!field_is_hidden('consumables', 'name'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'name', 'Name') !!}</label><input name="name" value="{{ old('name') }}" {{ field_attributes('consumables', 'name') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">@error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                @endif

                @if(!field_is_hidden('consumables', 'category'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'category', 'Category') !!}</label><input name="category" value="{{ old('category') }}" {{ field_attributes('consumables', 'category') }} placeholder="e.g. Lubrication, Cleaning" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif

                @if(!field_is_hidden('consumables', 'unit'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'unit', 'Unit') !!}</label><input name="unit" value="{{ old('unit','pcs') }}" {{ field_attributes('consumables', 'unit') }} placeholder="pcs, kg, liter..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif

                @if(!field_is_hidden('consumables', 'stock'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'stock', 'Actual Qty') !!}</label><input name="qty_actual" type="number" min="0" value="{{ old('qty_actual',0) }}" {{ field_attributes('consumables', 'stock') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif

                @if(!field_is_hidden('consumables', 'min_stock'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'min_stock', 'Minimum Qty') !!}</label><input name="qty_minimum" type="number" min="0" value="{{ old('qty_minimum',0) }}" {{ field_attributes('consumables', 'min_stock') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif

                @if(!field_is_hidden('consumables', 'unit_price'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'unit_price', 'Unit Price (IDR)') !!}</label><input name="unit_price" type="number" step="0.01" value="{{ old('unit_price') }}" {{ field_attributes('consumables', 'unit_price') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif

                @if(!field_is_hidden('consumables', 'supplier'))
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'supplier', 'Supplier') !!}</label><input name="supplier" value="{{ old('supplier') }}" {{ field_attributes('consumables', 'supplier') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif

                @if(!field_is_hidden('consumables', 'location'))
                <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'location', 'Storage Location') !!}</label><input name="location" value="{{ old('location') }}" {{ field_attributes('consumables', 'location') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                @endif
            </div>

            @if(!field_is_hidden('consumables', 'description'))
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('consumables', 'description', 'Description') !!}</label><textarea name="description" rows="2" {{ field_attributes('consumables', 'description') }} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description') }}</textarea></div>
            @endif
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-medium hover:bg-brand-600">Create Consumable</button>
                <a href="{{ route('consumables.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

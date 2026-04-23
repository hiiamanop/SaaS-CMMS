@extends('layouts.app')
@section('title','Edit Spare Part')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('spare-parts.index') }}" class="hover:text-gray-800">Spare Parts</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('spare-parts.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Spare Part</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('spare-parts.update', $sparePart) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Part Code <span class="text-red-500">*</span></label><input name="part_code" value="{{ old('part_code',$sparePart->part_code) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">@error('part_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label><input name="name" value="{{ old('name',$sparePart->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label><input name="category" value="{{ old('category',$sparePart->category) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Unit <span class="text-red-500">*</span></label><input name="unit" value="{{ old('unit',$sparePart->unit) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Actual Qty <span class="text-red-500">*</span></label><input name="qty_actual" type="number" min="0" value="{{ old('qty_actual',$sparePart->qty_actual) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Minimum Qty <span class="text-red-500">*</span></label><input name="qty_minimum" type="number" min="0" value="{{ old('qty_minimum',$sparePart->qty_minimum) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Unit Price (IDR)</label><input name="unit_price" type="number" step="0.01" value="{{ old('unit_price',$sparePart->unit_price) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier</label><input name="supplier" value="{{ old('supplier',$sparePart->supplier) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">Storage Location</label><input name="location" value="{{ old('location',$sparePart->location) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label><textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description',$sparePart->description) }}</textarea></div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-brand text-white rounded-lg text-sm font-medium hover:bg-brand-600">Update Part</button>
                <a href="{{ route('spare-parts.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title','Edit Tool')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('tools.index') }}" class="hover:text-gray-800">Tools</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit Tool</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('tools.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Tool</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('tools.update', $tool) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Tool Code <span class="text-red-500">*</span></label><input name="tool_code" value="{{ old('tool_code', $tool->tool_code) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('tool_code') border-red-400 @enderror">@error('tool_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Name <span class="text-red-500">*</span></label><input name="name" value="{{ old('name', $tool->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">@error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label><input name="category" value="{{ old('category', $tool->category) }}" placeholder="e.g. Hand Tools, Power Tools" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label><input name="brand" value="{{ old('brand', $tool->brand) }}" placeholder="e.g. Makita, Bosch" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Condition <span class="text-red-500">*</span></label>
                    <select name="condition" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="good" {{ old('condition', $tool->condition) == 'good' ? 'selected' : '' }}>Good</option>
                        <option value="damaged" {{ old('condition', $tool->condition) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="lost" {{ old('condition', $tool->condition) == 'lost' ? 'selected' : '' }}>Lost</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Storage Location</label><input name="location" value="{{ old('location', $tool->location) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Total Qty <span class="text-red-500">*</span></label><input name="qty_total" type="number" min="1" value="{{ old('qty_total', $tool->qty_total) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Available Qty <span class="text-red-500">*</span></label><input name="qty_available" type="number" min="0" value="{{ old('qty_available', $tool->qty_available) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label><textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description', $tool->description) }}</textarea></div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-medium hover:bg-brand-600">Update Tool</button>
                <a href="{{ route('tools.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

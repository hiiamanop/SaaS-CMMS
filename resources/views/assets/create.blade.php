@extends('layouts.app')
@section('title','Add Asset')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('assets.index') }}" class="hover:text-gray-800">Assets</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Add Asset</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('assets.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Add New Asset</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Basic Info Section --}}
                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @if(!field_is_hidden('assets', 'asset_code'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'asset_code', 'Asset Code') !!}</label>
                            <input name="asset_code" value="{{ old('asset_code') }}" {!! field_attributes('assets', 'asset_code') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('asset_code') border-red-400 @enderror">
                            @error('asset_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        @endif

                        @if(!field_is_hidden('assets', 'name'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'name', 'Asset Name') !!}</label>
                            <input name="name" value="{{ old('name') }}" {!! field_attributes('assets', 'name') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('name') border-red-400 @enderror">
                            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        @endif

                        @if(!field_is_hidden('assets', 'location_id'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'location_id', 'Select PLTS') !!}</label>
                            <select name="location_id" {!! field_attributes('assets', 'location_id') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="">— Select PLTS —</option>
                                @foreach($pltsList as $p)
                                <option value="{{ $p->id }}" {{ old('location_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        @if(!field_is_hidden('assets', 'category'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'category', 'Category') !!}</label>
                            <input name="category" value="{{ old('category') }}" {!! field_attributes('assets', 'category') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>
                        @endif

                        {{-- Internal Location --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Internal Location <span class="text-red-500">*</span></label>
                            <input name="location" value="{{ old('location') }}" required placeholder="e.g. Control Room, Field A" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="active" {{ old('status')=='active'?'selected':'' }}>Active</option>
                                <option value="inactive" {{ old('status')=='inactive'?'selected':'' }}>Inactive</option>
                                <option value="under_maintenance" {{ old('status')=='under_maintenance'?'selected':'' }}>Under Maintenance</option>
                                <option value="retired" {{ old('status')=='retired'?'selected':'' }}>Retired</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Specs Section --}}
                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Technical Specifications</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @if(!field_is_hidden('assets', 'brand'))
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'brand', 'Brand') !!}</label><input name="brand" value="{{ old('brand') }}" {!! field_attributes('assets', 'brand') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        @endif

                        @if(!field_is_hidden('assets', 'model_number'))
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'model_number', 'Model / Type') !!}</label><input name="model" value="{{ old('model') }}" {!! field_attributes('assets', 'model_number') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        @endif

                        @if(!field_is_hidden('assets', 'serial_number'))
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'serial_number', 'Serial Number') !!}</label><input name="serial_number" value="{{ old('serial_number') }}" {!! field_attributes('assets', 'serial_number') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        @endif
                    </div>
                </div>

                {{-- Purchase Section --}}
                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Procurement & Warranty</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @if(!field_is_hidden('assets', 'purchase_date'))
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'purchase_date', 'Purchase Date') !!}</label><input name="purchase_date" type="date" value="{{ old('purchase_date') }}" {!! field_attributes('assets', 'purchase_date') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        @endif

                        @if(!field_is_hidden('assets', 'warranty_expiry'))
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'warranty_expiry', 'Warranty Expiry') !!}</label><input name="warranty_expiry" type="date" value="{{ old('warranty_expiry') }}" {!! field_attributes('assets', 'warranty_expiry') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        @endif
                        
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Price (IDR)</label><input name="purchase_price" type="number" step="0.01" value="{{ old('purchase_price') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Photo</label>
                    <input name="photo" type="file" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                
                @if(!field_is_hidden('assets', 'description'))
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">{!! field_label('assets', 'description', 'Description / Technical Notes') !!}</label>
                    <textarea name="description" rows="4" {!! field_attributes('assets', 'description') !!} class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description') }}</textarea>
                </div>
                @endif
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-bold hover:bg-brand-600 transition-all shadow-sm">Save Asset</button>
                <a href="{{ route('assets.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90 transition-all">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title','Edit Asset')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('assets.index') }}" class="hover:text-gray-800">Assets</a><span class="text-gray-400">/</span><a href="{{ route('assets.show', $asset) }}" class="hover:text-gray-800">{{ $asset->name }}</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('assets.show', $asset) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Asset</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('assets.update', $asset) }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ category: '{{ old('category', $asset->category) }}' }">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Basic Info Section --}}
                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Basic Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Code</label>
                            <input name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('asset_code') border-red-400 @enderror">
                            @error('asset_code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Name <span class="text-red-500">*</span></label>
                            <input name="name" value="{{ old('name', $asset->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('name') border-red-400 @enderror">
                            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Select PLTS <span class="text-red-500">*</span></label>
                            <select name="location_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="">— Select PLTS —</option>
                                @foreach($pltsList as $p)
                                <option value="{{ $p->id }}" {{ old('location_id', $asset->location_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
                            <input name="category" x-model="category" list="asset-categories" value="{{ old('category', $asset->category) }}" required placeholder="Pilih atau ketik kategori..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <datalist id="asset-categories">
                                <option value="PV Module">PV Module (Panel Surya)</option>
                                <option value="Inverter">Inverter</option>
                                <option value="Transformer">Transformer (Trafo)</option>
                                <option value="String Combiner Box">String Combiner Box (SCB)</option>
                                <option value="Battery">Battery / Energy Storage</option>
                                <option value="Monitoring System">Monitoring System (SCADA)</option>
                                <option value="Mounting Structure">Mounting Structure (Racking)</option>
                                <option value="Protection Relay">Protection Relay</option>
                                <option value="Switchgear">Switchgear / MV Panel</option>
                                <option value="Metering">Metering</option>
                                <option value="Cable">Cable & Wiring</option>
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Internal Location <span class="text-red-500">*</span></label>
                            <input name="location" value="{{ old('location', $asset->location) }}" required placeholder="e.g. Control Room, Field A" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                @foreach(['active','inactive','replaced','retired'] as $s)
                                <option value="{{ $s }}" {{ old('status',$asset->status)==$s?'selected':'' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Specs Section --}}
                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Technical Specifications</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Brand</label><input name="brand" value="{{ old('brand', $asset->brand) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Model / Type</label><input name="model" value="{{ old('model', $asset->model) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Serial Number</label><input name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    </div>
                </div>

                {{-- PV Module Hierarchy --}}
                <div class="md:col-span-3" x-show="category === 'PV Module'" x-cloak>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">PV Module Hierarchy <span class="text-blue-500 font-normal normal-case tracking-normal">(T01-N05-S14)</span></h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Transformer Block</label>
                            <input name="transformer_block" value="{{ old('transformer_block', $asset->transformer_block) }}" placeholder="e.g. T01, T02" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand uppercase">
                            <p class="text-xs text-gray-400 mt-1">Blok trafo (T01, T02, ...)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">String Number (N)</label>
                            <input name="string_number" type="number" min="1" max="999" value="{{ old('string_number', $asset->string_number) }}" placeholder="e.g. 5" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <p class="text-xs text-gray-400 mt-1">Nomor string (1-21)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Module Slot (S)</label>
                            <input name="module_slot" type="number" min="1" max="999" value="{{ old('module_slot', $asset->module_slot) }}" placeholder="e.g. 14" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <p class="text-xs text-gray-400 mt-1">Slot modul dalam string (1-21)</p>
                        </div>
                    </div>
                </div>

                {{-- Purchase Section --}}
                <div class="md:col-span-3">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Procurement & Warranty</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Date</label><input name="purchase_date" type="date" value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Warranty Expiry</label><input name="warranty_expiry" type="date" value="{{ old('warranty_expiry', $asset->warranty_expiry?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Price (IDR)</label><input name="purchase_price" type="number" step="0.01" value="{{ old('purchase_price', $asset->purchase_price) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Photo</label>
                    @if($asset->photo)
                        <div class="mb-3 flex items-center gap-3 bg-gray-50 p-2 rounded-lg border border-gray-200">
                            <img src="{{ Storage::url($asset->photo) }}" class="h-16 w-16 rounded-lg object-cover">
                            <span class="text-xs text-gray-500">Current photo</span>
                        </div>
                    @endif
                    <input name="photo" type="file" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Description / Technical Notes</label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description', $asset->description) }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-bold hover:bg-brand-600 transition-all shadow-sm">Update Asset</button>
                <a href="{{ route('assets.show', $asset) }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90 transition-all">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

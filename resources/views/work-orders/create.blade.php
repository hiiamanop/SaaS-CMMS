@extends('layouts.app')
@section('title','New Work Order')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('work-orders.index') }}" class="hover:text-gray-800">Work Orders</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">New</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('work-orders.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">New Work Order</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('work-orders.store') }}" method="POST" class="space-y-6" x-data="{checklist:[''], isExternal:false}">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Main Details --}}
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Task Title <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title') }}" required placeholder="e.g. Inverter Repair" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                             <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" name="is_external_client" value="1" id="isExternal" x-model="isExternal" class="w-4 h-4 text-brand border-gray-300 rounded focus:ring-brand">
                                <label for="isExternal" class="text-sm font-semibold text-gray-700 cursor-pointer">Pekerjaan di Lokasi Client (Bukan PLTS Internal)</label>
                             </div>
                        </div>

                        <div x-show="!isExternal" x-transition:enter="transition ease-out duration-200" class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Internal <span class="text-red-500">*</span></label>
                            <select name="asset_id" :required="!isExternal" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('asset_id') border-red-400 @enderror">
                                <option value="">Select asset...</option>
                                @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id')==$a->id?'selected':'' }}>{{ $a->name }} ({{ $a->asset_code }})</option>@endforeach
                            </select>
                            @error('asset_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="isExternal" x-transition:enter="transition ease-out duration-200" class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Client / Lokasi Luar <span class="text-red-500">*</span></label>
                            <input name="client_name" value="{{ old('client_name') }}" :required="isExternal" placeholder="Masukkan nama client..." class="w-full px-3 py-2 border border-blue-300 bg-blue-50/30 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            @error('client_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            {{-- Placeholder asset for external --}}
                            <input type="hidden" name="asset_id" value="1"> 
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign To</label>
                            <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="">Unassigned</option>
                                @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('assigned_to')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                            <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                @foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" {{ old('priority','medium')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description') }}</textarea>
                    </div>
                </div>

                {{-- Right Column: Schedule & Tasks --}}
                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h4 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-wider">Schedule Info</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date <span class="text-red-500">*</span></label>
                                <input name="due_date" type="date" value="{{ old('due_date') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div class="pt-2">
                                <div class="flex items-start gap-3 p-3 bg-white border border-gray-200 rounded-lg">
                                    <input type="hidden" name="shutdown_required" value="0">
                                    <input type="checkbox" name="shutdown_required" value="1" id="shutdownRequired" {{ old('shutdown_required') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                    <div>
                                        <label for="shutdownRequired" class="text-sm font-medium text-gray-800 cursor-pointer">Shutdown Required</label>
                                        <p class="text-[10px] text-gray-500 mt-0.5 leading-tight">Downtime dihitung otomatis saat WO dikerjakan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h4 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-wider">Checklist Tasks</h4>
                        <div class="space-y-2">
                            <template x-for="(item, index) in checklist" :key="index">
                                <div class="flex gap-2">
                                    <input :name="'checklist['+index+']'" x-model="checklist[index]" placeholder="New task..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-brand">
                                    <button type="button" @click="checklist.splice(index,1)" class="p-2 text-red-400 hover:text-red-600 rounded-lg"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="checklist.push('')" class="mt-3 inline-flex items-center gap-1.5 text-xs text-brand hover:text-blue-700 font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add Task
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-bold hover:bg-brand-600 shadow-sm transition-all">Submit Order</button>
                <a href="{{ route('work-orders.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Cancel</a>
            </div>
            
            <input type="hidden" name="type" value="corrective">
        </form>
    </div>
</div>
@endsection

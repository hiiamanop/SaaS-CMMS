@extends('layouts.app')
@section('title','Edit Work Order')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('work-orders.index') }}" class="hover:text-gray-800">Work Orders</a><span class="text-gray-400">/</span><a href="{{ route('work-orders.show', $workOrder) }}" class="hover:text-gray-800">{{ $workOrder->wo_number }}</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('work-orders.show',$workOrder) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Work Order</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('work-orders.update', $workOrder) }}" method="POST" class="space-y-6" x-data="{isExternal: {{ $workOrder->is_external_client ? 'true' : 'false' }} }">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column --}}
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Task Title <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title', $workOrder->title) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                             <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" name="is_external_client" value="1" id="isExternal" x-model="isExternal" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="isExternal" class="text-sm font-semibold text-gray-700 cursor-pointer">Pekerjaan di Lokasi Client (Bukan PLTS Internal)</label>
                             </div>
                        </div>

                        <div x-show="!isExternal" class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Asset Internal <span class="text-red-500">*</span></label>
                            <select name="asset_id" :required="!isExternal" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select asset...</option>
                                @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id', $workOrder->asset_id)==$a->id?'selected':'' }}>{{ $a->name }} ({{ $a->asset_code }})</option>@endforeach
                            </select>
                        </div>

                        <div x-show="isExternal" class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Client / Lokasi Luar <span class="text-red-500">*</span></label>
                            <input name="client_name" value="{{ old('client_name', $workOrder->client_name) }}" :required="isExternal" class="w-full px-3 py-2 border border-blue-300 bg-blue-50/30 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{-- Asset placeholder --}}
                            <input type="hidden" name="asset_id" value="1">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign To</label>
                            <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Unassigned</option>
                                @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('assigned_to', $workOrder->assigned_to)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                            <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" {{ old('priority', $workOrder->priority)==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $workOrder->description) }}</textarea>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date <span class="text-red-500">*</span></label>
                        <input name="due_date" type="date" value="{{ old('due_date', $workOrder->due_date?->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-bold hover:bg-blue-700 shadow-sm transition-all">Update Order</button>
                <a href="{{ route('work-orders.show', $workOrder) }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
            
            <input type="hidden" name="type" value="corrective">
        </form>
    </div>
</div>
@endsection

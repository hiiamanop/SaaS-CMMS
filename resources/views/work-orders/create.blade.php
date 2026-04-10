@extends('layouts.app')
@section('title','New Work Order')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('work-orders.index') }}" class="hover:text-gray-800">Work Orders</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">New</span>@endsection
@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('work-orders.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">New Work Order</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('work-orders.store') }}" method="POST" class="space-y-5" x-data="{checklist:['']}">
            @csrf
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-500">*</span></label><input name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">@error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Asset <span class="text-red-500">*</span></label>
                    <select name="asset_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('asset_id') border-red-400 @enderror">
                        <option value="">Select asset...</option>
                        @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id')==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
                    </select>@error('asset_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Assign To</label>
                    <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Unassigned</option>
                        @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('assigned_to')==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
                    <div class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600">
                        Corrective
                    </div>
                    <input type="hidden" name="type" value="corrective">
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                    <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" {{ old('priority','medium')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
                    </select>
                </div>
                <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date <span class="text-red-500">*</span></label><input name="due_date" type="date" value="{{ old('due_date') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">@error('due_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror</div>
                <div class="sm:col-span-2 flex items-start gap-3 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                    <input type="hidden" name="shutdown_required" value="0">
                    <input type="checkbox" name="shutdown_required" value="1" id="shutdownRequired"
                           {{ old('shutdown_required') ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                    <div>
                        <label for="shutdownRequired" class="text-sm font-medium text-orange-800 cursor-pointer">Memerlukan Shutdown</label>
                        <p class="text-xs text-orange-600 mt-0.5">Durasi shutdown dihitung otomatis dari saat WO mulai dikerjakan hingga diselesaikan.</p>
                    </div>
                </div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label><textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description') }}</textarea></div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Checklist Items</label>
                <div class="space-y-2">
                    <template x-for="(item, index) in checklist" :key="index">
                        <div class="flex gap-2">
                            <input :name="'checklist['+index+']'" x-model="checklist[index]" placeholder="Task item..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="checklist.splice(index,1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="checklist.push('')" class="mt-2 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add checklist item
                </button>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Create Work Order</button>
                <a href="{{ route('work-orders.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

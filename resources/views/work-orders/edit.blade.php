@extends('layouts.app')
@section('title','Edit Work Order')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('work-orders.index') }}" class="hover:text-gray-800">Work Orders</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('work-orders.show',$workOrder) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Work Order</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('work-orders.update',$workOrder) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label><input name="title" value="{{ old('title',$workOrder->title) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Asset</label>
                    <select name="asset_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id',$workOrder->asset_id)==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Assigned To</label>
                    <select name="assigned_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Unassigned</option>
                        @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('assigned_to',$workOrder->assigned_to)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe</label>
                    <div class="w-full px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600">
                        Corrective
                    </div>
                    <input type="hidden" name="type" value="corrective">
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Priority</label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" {{ old('priority',$workOrder->priority)==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
                    </select>
                </div>
                <div class="sm:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date</label><input name="due_date" type="date" value="{{ old('due_date',$workOrder->due_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label><textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description',$workOrder->description) }}</textarea></div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Update Work Order</button>
                <a href="{{ route('work-orders.show',$workOrder) }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

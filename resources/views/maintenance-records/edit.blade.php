@extends('layouts.app')
@section('title','Edit Record')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('maintenance-records.index') }}" class="hover:text-gray-800">Records</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('maintenance-records.show',$maintenanceRecord) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Maintenance Record</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('maintenance-records.update',$maintenanceRecord) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Asset</label>
                    <select name="asset_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id',$maintenanceRecord->asset_id)==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Technician</label>
                    <select name="technician_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('technician_id',$maintenanceRecord->technician_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="corrective" {{ old('type',$maintenanceRecord->type)=='corrective'?'selected':'' }}>Corrective</option>
                        <option value="preventive" {{ old('type',$maintenanceRecord->type)=='preventive'?'selected':'' }}>Preventive</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Date</label><input name="maintenance_date" type="date" value="{{ old('maintenance_date',$maintenanceRecord->maintenance_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes)</label><input name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes',$maintenanceRecord->duration_minutes) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Shutdown (minutes)</label><input name="shutdown_minutes" type="number" min="0" value="{{ old('shutdown_minutes',$maintenanceRecord->shutdown_minutes) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Findings</label><textarea name="findings" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('findings',$maintenanceRecord->findings) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Actions Taken</label><textarea name="actions_taken" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('actions_taken',$maintenanceRecord->actions_taken) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes',$maintenanceRecord->notes) }}</textarea></div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Update Record</button>
                <a href="{{ route('maintenance-records.show',$maintenanceRecord) }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

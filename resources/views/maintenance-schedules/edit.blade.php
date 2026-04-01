@extends('layouts.app')
@section('title','Edit Schedule')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('maintenance-schedules.index') }}" class="hover:text-gray-800">Schedules</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
@php $s = $maintenanceSchedule; @endphp
<div class="max-w-2xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('maintenance-schedules.show',$s) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Schedule</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('maintenance-schedules.update',$s) }}" method="POST" class="space-y-5"
              x-data="{checklist: {{ json_encode(old('checklist', $s->checklistItems->pluck('description')->toArray())) }}, freq: '{{ old('frequency', $s->frequency) }}'}">
            @csrf @method('PUT')
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Title</label><input name="title" value="{{ old('title',$s->title) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Asset</label>
                    <select name="asset_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id',$s->asset_id)==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="preventive" {{ old('type',$s->type)=='preventive'?'selected':'' }}>Preventive</option>
                        <option value="corrective" {{ old('type',$s->type)=='corrective'?'selected':'' }}>Corrective</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Frequency</label>
                    <select name="frequency" x-model="freq" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach(['daily','weekly','monthly','quarterly','annually','custom'] as $f)
                        <option value="{{ $f }}" {{ old('frequency',$s->frequency)==$f?'selected':'' }}>{{ ucfirst($f) }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="freq==='custom'"><label class="block text-sm font-medium text-gray-700 mb-1.5">Every N days</label><input name="frequency_days" type="number" min="1" value="{{ old('frequency_days',$s->frequency_days) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date</label><input name="start_date" type="date" value="{{ old('start_date',$s->start_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Next Due Date</label><input name="next_due_date" type="date" value="{{ old('next_due_date',$s->next_due_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="active" {{ old('status',$s->status)=='active'?'selected':'' }}>Active</option>
                        <option value="inactive" {{ old('status',$s->status)=='inactive'?'selected':'' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes',$s->notes) }}</textarea></div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Checklist Items</label>
                <div class="space-y-2">
                    <template x-for="(item, index) in checklist" :key="index">
                        <div class="flex gap-2">
                            <input :name="'checklist['+index+']'" x-model="checklist[index]" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="checklist.splice(index,1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="checklist.push('')" class="mt-2 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add item
                </button>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Update Schedule</button>
                <a href="{{ route('maintenance-schedules.show',$s) }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

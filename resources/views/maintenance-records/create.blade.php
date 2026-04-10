@extends('layouts.app')
@section('title','New Maintenance Record')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('maintenance-records.index') }}" class="hover:text-gray-800">Records</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">New Record</span>@endsection
@section('content')
<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('maintenance-records.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">New Maintenance Record</h1>
    </div>
    @if($workOrder)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-5 text-sm text-blue-800">
        Creating record for Work Order <strong>{{ $workOrder->wo_number }}</strong>: {{ $workOrder->title }}
    </div>
    @endif
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('maintenance-records.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5"
              x-data="{parts:[{spare_part_id:'',qty_used:1}]}">
            @csrf
            {{-- Work Order picker --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Work Order <span class="text-red-500">*</span></label>
                <select name="work_order_id" id="woSelect" onchange="onWoChange(this)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('work_order_id') border-red-400 @enderror">
                    <option value="">Pilih Work Order...</option>
                    @foreach($workOrders as $wo)
                    <option value="{{ $wo->id }}"
                            data-asset-id="{{ $wo->asset_id }}"
                            data-asset-name="{{ $wo->asset?->name }}"
                            data-technician-id="{{ $wo->assigned_to }}"
                            {{ old('work_order_id', $workOrder?->id) == $wo->id ? 'selected' : '' }}>
                        {{ $wo->wo_number }} — {{ $wo->title }} ({{ $wo->asset?->name }})
                    </option>
                    @endforeach
                </select>
                @error('work_order_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            {{-- Derived asset (read-only display + hidden) --}}
            <input type="hidden" name="asset_id" id="hiddenAssetId" value="{{ old('asset_id', $workOrder?->asset_id) }}">
            <div id="assetDisplay" class="text-sm text-gray-500 -mt-2 px-1">
                @if($workOrder) Asset: <span class="font-medium text-gray-800">{{ $workOrder->asset?->name }}</span> @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Teknisi <span class="text-red-500">*</span></label>
                    <select name="technician_id" id="technicianSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih teknisi...</option>
                        @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('technician_id', $workOrder?->assigned_to) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="corrective" {{ old('type',$workOrder?->type)=='corrective'?'selected':'' }}>Corrective</option>
                        <option value="preventive" {{ old('type',$workOrder?->type)=='preventive'?'selected':'' }}>Preventive</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Maintenance Date <span class="text-red-500">*</span></label><input name="maintenance_date" type="date" value="{{ old('maintenance_date', now()->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes) <span class="text-red-500">*</span></label><input name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes',0) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Downtime (minutes)</label><input name="downtime_minutes" type="number" min="0" value="{{ old('downtime_minutes',0) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Findings</label><textarea name="findings" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Describe what was found...">{{ old('findings') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Actions Taken</label><textarea name="actions_taken" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" placeholder="Describe what was done...">{{ old('actions_taken') }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes') }}</textarea></div>

            {{-- Parts used --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Parts Used</label>
                <div class="space-y-2">
                    <template x-for="(part, index) in parts" :key="index">
                        <div class="flex gap-2 items-center">
                            <select :name="'parts['+index+'][spare_part_id]'" x-model="part.spare_part_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select part...</option>
                                @foreach($spareParts as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->qty_actual }} {{ $p->unit }} available)</option>@endforeach
                            </select>
                            <input :name="'parts['+index+'][qty_used]'" x-model="part.qty_used" type="number" min="1" placeholder="Qty" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="parts.splice(index,1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="parts.push({spare_part_id:'',qty_used:1})" class="mt-2 inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add part
                </button>
            </div>

            {{-- Photos --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Photos</label>
                <input name="photos[]" type="file" accept="image/*" multiple class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">You can select multiple photos</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Save Record</button>
                <a href="{{ route('maintenance-records.index') }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
function onWoChange(sel) {
    const opt = sel.options[sel.selectedIndex];
    const assetId   = opt.dataset.assetId   || '';
    const assetName = opt.dataset.assetName  || '';
    const techId    = opt.dataset.technicianId || '';

    document.getElementById('hiddenAssetId').value = assetId;

    const display = document.getElementById('assetDisplay');
    display.innerHTML = assetId
        ? 'Asset: <span class="font-medium text-gray-800">' + assetName + '</span>'
        : '';

    const techSel = document.getElementById('technicianSelect');
    if (techId) techSel.value = techId;
}
// Pre-fill on page load if a WO is already selected
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('woSelect');
    if (sel && sel.value) onWoChange(sel);
});
</script>
@endpush
@endsection

@extends('layouts.app')
@section('title','Edit Record')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('maintenance-records.index') }}" class="hover:text-gray-800">Records</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Edit</span>@endsection
@section('content')
<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('maintenance-records.show',$maintenanceRecord) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">Edit Maintenance Record</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('maintenance-records.update',$maintenanceRecord) }}" method="POST" enctype="multipart/form-data" class="space-y-5"
              x-data="{
                photos: [],
                handlePhotos(e) {
                    const files = Array.from(e.target.files);
                    this.photos = files.map(file => ({
                        url: URL.createObjectURL(file),
                        name: file.name
                    }));
                }
              }">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Asset</label>
                    <select name="asset_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        @foreach($assets as $a)<option value="{{ $a->id }}" {{ old('asset_id',$maintenanceRecord->asset_id)==$a->id?'selected':'' }}>{{ $a->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Technician</label>
                    <select name="technician_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        @foreach($technicians as $t)<option value="{{ $t->id }}" {{ old('technician_id',$maintenanceRecord->technician_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                        <option value="corrective" {{ old('type',$maintenanceRecord->type)=='corrective'?'selected':'' }}>Corrective</option>
                        <option value="preventive" {{ old('type',$maintenanceRecord->type)=='preventive'?'selected':'' }}>Preventive</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Date</label><input name="maintenance_date" type="date" value="{{ old('maintenance_date',$maintenanceRecord->maintenance_date->format('Y-m-d')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Duration (minutes)</label><input name="duration_minutes" type="number" min="0" value="{{ old('duration_minutes',$maintenanceRecord->duration_minutes) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Shutdown (minutes)</label><input name="shutdown_minutes" type="number" min="0" value="{{ old('shutdown_minutes',$maintenanceRecord->shutdown_minutes) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand"></div>
            </div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Findings</label><textarea name="findings" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('findings',$maintenanceRecord->findings) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Actions Taken</label><textarea name="actions_taken" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('actions_taken',$maintenanceRecord->actions_taken) }}</textarea></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label><textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('notes',$maintenanceRecord->notes) }}</textarea></div>

            {{-- Existing Photos --}}
            @if($maintenanceRecord->photos->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Photos</label>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-4">
                    @foreach($maintenanceRecord->photos as $p)
                    <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200">
                        <img src="{{ Storage::url($p->file_path) }}" class="w-full h-full object-cover">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- New Photos --}}
            <div class="pt-4 border-t border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Add More Photos</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-brand transition-colors cursor-pointer relative">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-brand hover:text-brand-600 focus-within:outline-none">
                                <span>Upload files</span>
                                <input name="photos[]" type="file" accept="image/*" multiple @change="handlePhotos" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                    </div>
                </div>

                {{-- New Photo Preview Grid --}}
                <template x-if="photos.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 mt-4">
                        <template x-for="(photo, idx) in photos" :key="idx">
                            <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200">
                                <img :src="photo.url" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <p class="text-[10px] text-white font-medium px-2 text-center truncate" x-text="photo.name"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-medium hover:bg-brand-600">Update Record</button>
                <a href="{{ route('maintenance-records.show',$maintenanceRecord) }}" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

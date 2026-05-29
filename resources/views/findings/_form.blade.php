<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Judul Finding <span class="text-red-500">*</span></label>
        <input name="title" value="{{ old('title', $finding?->title) }}" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
            placeholder="Deskripsi singkat temuan">
        @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            @foreach(['open'=>'Open','in_progress'=>'In Progress','resolved'=>'Resolved','closed'=>'Closed'] as $val => $label)
            <option value="{{ $val }}" {{ old('status', $finding?->status ?? 'open') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi PLTS</label>
        <select name="location_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <option value="">— Pilih Lokasi —</option>
            @foreach($locations as $loc)
            <option value="{{ $loc->id }}" {{ old('location_id', $finding?->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Ditemukan</label>
        <input type="date" name="found_date" value="{{ old('found_date', $finding?->found_date?->format('Y-m-d')) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Selesai</label>
        <input type="date" name="resolved_date" value="{{ old('resolved_date', $finding?->resolved_date?->format('Y-m-d')) }}"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Deskripsi Detail</label>
        <textarea name="description" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
            placeholder="Detail anomali atau temuan...">{{ old('description', $finding?->description) }}</textarea>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Tindakan Penanganan</label>
        <textarea name="action_taken" rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
            placeholder="Tindakan yang sudah dilakukan...">{{ old('action_taken', $finding?->action_taken) }}</textarea>
    </div>
</div>

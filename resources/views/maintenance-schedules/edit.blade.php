@extends('layouts.app')
@section('title','Edit Jadwal')
@section('breadcrumb')
    <span class="text-gray-400">/</span>
    <a href="{{ route('maintenance-schedules.index') }}" class="hover:text-gray-800">Maint. Schedule</a>
    <span class="text-gray-400">/</span>
    <span class="text-gray-700 font-medium">Edit Jadwal</span>
@endsection
@section('content')
@php
$s          = $maintenanceSchedule;
$months     = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
// Build set of already-planned keys like "1_1", "3_2" etc
$plannedSet = collect($s->planned_weeks ?? [])->mapWithKeys(fn($p) => [$p['month'].'_'.$p['week'] => true])->all();
// If there's old() input (validation failed), override with that
$oldWeeks   = old('planned_weeks', null);
if ($oldWeeks !== null) {
    $plannedSet = $oldWeeks; // old() returns the checkbox array
}
@endphp
<div class="max-w-4xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('maintenance-schedules.show', $s) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Jadwal Maintenance</h1>
            <p class="text-sm text-gray-500 mt-0.5">Tipe: <span class="font-medium text-blue-600">Preventive</span></p>
        </div>
    </div>

    <form action="{{ route('maintenance-schedules.update', $s) }}" method="POST" class="space-y-6"
          x-data="{ shutdown: {{ ($s->shutdown_required || old('shutdown_required')) ? 'true' : 'false' }} }">
        @csrf @method('PUT')

        {{-- Informasi Alat --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Informasi Alat</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Lokasi PLTS --}}
                @if(auth()->user()->isAdmin())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi PLTS <span class="text-red-500">*</span></label>
                    <select name="location_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('location_id') border-red-400 @enderror">
                        <option value="">Pilih lokasi PLTS...</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id', $s->location_id) == $loc->id ? 'selected' : '' }}>
                            {{ $loc->name }} ({{ $loc->code }})
                        </option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi PLTS</label>
                    <div class="px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700">
                        {{ $userLocation?->name ?? $s->location?->name ?? '—' }}
                        @php $displayLoc = $userLocation ?? $s->location; @endphp
                        @if($displayLoc)<span class="text-gray-400 text-xs ml-1">({{ $displayLoc->code }})</span>@endif
                    </div>
                    <input type="hidden" name="location_id" value="{{ $userLocation?->id ?? $s->location_id }}">
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Teknisi / PIC</label>
                    <select name="technician_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih teknisi...</option>
                        @foreach($technicians as $t)
                        <option value="{{ $t->id }}" {{ old('technician_id', $s->technician_id) == $t->id ? 'selected' : '' }}>{{ $t->name }} ({{ ucfirst($t->role) }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Alat / Trafo <span class="text-red-500">*</span></label>
                    <input name="trafo_name" value="{{ old('trafo_name', $s->trafo_name) }}" required
                           placeholder="cth: Trafo 1600 kVA, TR-01"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('trafo_name') border-red-400 @enderror">
                    @error('trafo_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Frekuensi <span class="text-red-500">*</span></label>
                    <select name="frequency" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="weekly"    {{ old('frequency', $s->frequency) == 'weekly'    ? 'selected' : '' }}>Mingguan</option>
                        <option value="monthly"   {{ old('frequency', $s->frequency) == 'monthly'   ? 'selected' : '' }}>Bulanan</option>
                        <option value="triwulan"  {{ old('frequency', $s->frequency) == 'triwulan'  ? 'selected' : '' }}>Triwulan</option>
                        <option value="quarterly" {{ old('frequency', $s->frequency) == 'quarterly' ? 'selected' : '' }}>Semesteran</option>
                        <option value="annually"  {{ old('frequency', $s->frequency) == 'annually'  ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>
                @php
                    $rawItems = old('item_pekerjaan', $s->item_pekerjaan ?? []);
                @endphp
                <div class="sm:col-span-2"
                     x-data="{
                        groups: {{ json_encode(
                            empty($rawItems)
                                ? [['lokasi_inspeksi' => '', 'items' => [['name' => '', 'metode' => '', 'standar' => '']]]]
                                : collect($rawItems)->map(fn($i) => is_string($i) ? ['name'=>$i,'metode'=>'','standar'=>'','lokasi_inspeksi'=>''] : $i)
                                    ->groupBy(fn($i) => $i['lokasi_inspeksi'] ?? '')
                                    ->map(fn($items, $lok) => ['lokasi_inspeksi' => $lok, 'items' => $items->values()->all()])
                                    ->values()->all()
                        ) }}
                     }">
                    
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Grup Item Pekerjaan <span class="text-red-500">*</span></label>
                        <button type="button" @click="groups.push({lokasi_inspeksi:'', items:[{name:'',metode:'',standar:''}]})"
                                class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:text-blue-700 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Lokasi Inspeksi
                        </button>
                    </div>

                    @error('item_pekerjaan')<p class="text-xs text-red-500 mb-2">{{ $message }}</p>@enderror

                    <div class="space-y-4">
                        <template x-for="(group, gIdx) in groups" :key="gIdx">
                            <div class="border border-gray-300 rounded-xl p-4 bg-gray-50/50 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-4 flex-1">
                                        <input x-model="group.lokasi_inspeksi"
                                               placeholder="Nama Lokasi Inspeksi (cth: Area Inverter, Atap)"
                                               class="w-full sm:w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white shadow-sm">
                                        <button type="button" x-show="groups.length > 1" @click="groups.splice(gIdx, 1)"
                                                class="flex-shrink-0 text-xs text-red-500 hover:text-red-700 font-medium">
                                            Hapus Lokasi
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-3 pl-4 border-l-2 border-gray-200 mt-2">
                                    <template x-for="(item, iIdx) in group.items" :key="iIdx">
                                        <div class="border border-gray-200 rounded-lg p-3 space-y-2 bg-white">
                                            <input type="hidden" :name="`item_pekerjaan[${gIdx}_${iIdx}][lokasi_inspeksi]`" :value="group.lokasi_inspeksi">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-medium text-gray-400 w-5 flex-shrink-0" x-text="(iIdx + 1) + '.'"></span>
                                                <input :name="`item_pekerjaan[${gIdx}_${iIdx}][name]`"
                                                       x-model="item.name" required
                                                       placeholder="Nama item pekerjaan (cth: Pembersihan panel)"
                                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                <button type="button" x-show="group.items.length > 1" @click="group.items.splice(iIdx, 1)"
                                                        class="flex-shrink-0 px-2 py-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg border border-gray-200">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 pl-7">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Metode Inspeksi</label>
                                                    <input :name="`item_pekerjaan[${gIdx}_${iIdx}][metode]`"
                                                           x-model="item.metode" placeholder="cth: Visual, Pengukuran"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Standar Ketentuan</label>
                                                    <input :name="`item_pekerjaan[${gIdx}_${iIdx}][standar]`"
                                                           x-model="item.standar" placeholder="cth: Tegangan ≥ 380V"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" @click="group.items.push({name:'',metode:'',standar:''})"
                                            class="mt-2 inline-block text-xs text-blue-600 hover:text-blue-700 font-medium">
                                        + Tambah Item Pekerjaan
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="active"   {{ old('status', $s->status) == 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $s->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Shutdown --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Shutdown</h2>
            <label class="flex items-center gap-3 cursor-pointer w-fit">
                <input type="checkbox" name="shutdown_required" value="1" x-model="shutdown"
                       {{ old('shutdown_required', $s->shutdown_required) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-700">Pekerjaan ini membutuhkan shutdown</span>
            </label>
            <div x-show="shutdown" x-transition>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Estimasi Durasi Shutdown (jam)</label>
                <input name="shutdown_duration_hours" type="number" min="1"
                       value="{{ old('shutdown_duration_hours', $s->shutdown_duration_hours) }}"
                       class="w-48 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Grid Jadwal Minggu --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Jadwal Minggu dalam Setahun</h2>
                <div class="flex items-center gap-2">
                    <p class="text-xs text-gray-400 hidden sm:block">Klik header baris/kolom untuk pilih semua di baris/kolom tersebut</p>
                    <button type="button" id="btnCheckAllWeeks" onclick="toggleAllWeeks(this)"
                            class="text-xs px-2.5 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-md border border-blue-200 font-medium whitespace-nowrap">
                        Ceklis Semua
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="weekGrid" class="text-xs border-collapse w-full" style="min-width:680px">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-2 py-1.5 text-left w-8 text-gray-500">Mgg</th>
                            @foreach($months as $idx => $m)
                            <th class="border border-gray-300 px-1 py-1.5 text-center font-semibold cursor-pointer hover:bg-blue-100 select-none"
                                onclick="toggleWeekCol({{ $idx + 1 }})" title="Pilih semua {{ $m }}">{{ $m }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([1,2,3,4] as $week)
                        <tr class="hover:bg-blue-50/30">
                            <td class="border border-gray-300 px-2 py-1.5 font-medium text-blue-600 text-center cursor-pointer hover:bg-blue-100 select-none"
                                onclick="toggleWeekRow({{ $week }})" title="Pilih semua Minggu {{ $week }}">W{{ $week }}</td>
                            @foreach(range(1,12) as $month)
                            @php $key = $month.'_'.$week; $checked = isset($plannedSet[$key]); @endphp
                            <td class="border border-gray-300 px-1 py-1 text-center">
                                <input type="checkbox" name="planned_weeks[{{ $key }}]" value="1"
                                       {{ $checked ? 'checked' : '' }}
                                       data-week="{{ $week }}" data-month="{{ $month }}"
                                       class="week-cb w-3.5 h-3.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-400 sm:hidden">Klik header baris/kolom untuk pilih semua di baris/kolom tersebut</p>
        </div>

        {{-- Catatan --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Catatan</label>
            <textarea name="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes', $s->notes) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                Simpan Perubahan
            </button>
            <a href="{{ route('maintenance-schedules.show', $s) }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function toggleAllWeeks(btn) {
    const cbs = document.querySelectorAll('#weekGrid .week-cb');
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
    btn.textContent = allChecked ? 'Ceklis Semua' : 'Hapus Semua';
}
function toggleWeekRow(week) {
    const cbs = document.querySelectorAll('#weekGrid .week-cb[data-week="' + week + '"]');
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
}
function toggleWeekCol(month) {
    const cbs = document.querySelectorAll('#weekGrid .week-cb[data-month="' + month + '"]');
    const allChecked = [...cbs].every(cb => cb.checked);
    cbs.forEach(cb => cb.checked = !allChecked);
}
</script>
@endpush
@endsection

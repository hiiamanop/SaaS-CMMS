@extends('layouts.app')

@section('title', 'Isi Checksheet — ' . $session->schedule->category)

@section('content')
<div x-data="checksheetFill({{ $session->id }}, {{ $total }})" x-init="init()" class="max-w-2xl mx-auto">

    {{-- Sticky top bar --}}
    <div class="sticky top-0 z-30 bg-white border-b border-gray-200 px-4 py-3 shadow-sm -mx-4 mb-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="font-semibold text-gray-900 text-base">{{ $session->schedule->category }} — {{ $session->plts_location }}</p>
                <p class="text-sm text-gray-500">{{ $session->period_label }}</p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-medium text-gray-700" x-text="filled + ' / {{ $total }} item'"></p>
                <p class="text-xs text-gray-400" x-text="saveStatus"></p>
            </div>
        </div>
        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full bg-blue-500 transition-all duration-300"
                 :style="'width: ' + Math.round((filled/{{ $total }})*100) + '%'"></div>
        </div>
    </div>

    {{-- Header info (read-only summary) --}}
    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 mb-6 text-sm text-gray-700 grid grid-cols-2 gap-3">
        <div><span class="text-gray-500">Tipe:</span> <span class="font-medium">{{ $session->schedule->equipment_name }}</span></div>
        <div><span class="text-gray-500">Periode:</span> <span class="font-medium">{{ $session->period_label }}</span></div>
        <div><span class="text-gray-500">Lokasi PLTS:</span> <span class="font-medium">{{ $session->plts_location }}</span></div>
        @if($session->equipment_location)
        <div><span class="text-gray-500">Lokasi Alat:</span> <span class="font-medium">{{ $session->equipment_location }}</span></div>
        @endif
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        @foreach($errors->all() as $err)
            <p class="text-sm text-red-600">{{ $err }}</p>
        @endforeach
    </div>
    @endif

    {{-- Inspection Items list --}}
    @php
        $isWeekly = $session->schedule->frequency === 'weekly';
        $groupedItems = [];
        foreach($items as $i) {
            $lok = is_array($i) ? ($i['lokasi_inspeksi'] ?? '') : '';
            $groupedItems[$lok][] = $i;
        }
    @endphp

    <div class="mb-6">
        @foreach($groupedItems as $lokasi => $groupItems)
        @if($lokasi)
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wide mb-3 mt-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $lokasi }}
        </h3>
        @endif
        <div class="space-y-4 mb-6">
            @foreach($groupItems as $item)
            @php
                $itemKey    = is_array($item) ? ($item['name']    ?? '') : $item;
                $itemMetode = is_array($item) ? ($item['metode']  ?? '') : '';
                $itemStandar= is_array($item) ? ($item['standar'] ?? '') : '';
                $existing   = $results[$itemKey] ?? null;
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 p-4"
                 x-data="{ result: '{{ $existing?->result ?? '' }}', notes: {{ json_encode($existing?->notes ?? '') }}, photos: [] }"
                 x-init="if(result) $dispatch('item-filled')">

                <div class="mb-3">
                    <p class="font-semibold text-gray-900 text-base">{{ $itemKey }}</p>
                    @if($itemMetode || $itemStandar)
                    <div class="flex flex-wrap gap-3 mt-1 text-xs text-gray-500">
                        @if($itemMetode)
                        <span class="flex items-center gap-1">
                            <span class="font-medium text-gray-600">Metode:</span> {{ $itemMetode }}
                        </span>
                        @endif
                        @if($itemStandar)
                        <span class="flex items-center gap-1">
                            <span class="font-medium text-gray-600">Standar:</span> {{ $itemStandar }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- P / X toggle --}}
                <div class="flex gap-3 mb-3">
                    <button type="button"
                            @click="result = 'P'; saveItem('{{ addslashes($itemKey) }}', 'P', notes)"
                            :class="result === 'P' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-green-50'"
                            class="flex-1 min-h-[48px] text-base font-bold rounded-lg border-2 transition-colors">
                        ✓ P (Normal)
                    </button>
                    <button type="button"
                            @click="result = 'X'; saveItem('{{ addslashes($itemKey) }}', 'X', notes)"
                            :class="result === 'X' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-red-50'"
                            class="flex-1 min-h-[48px] text-base font-bold rounded-lg border-2 transition-colors">
                        ✗ X (Anomali)
                    </button>
                </div>

                {{-- Notes + Photo (shown when X) --}}
                <div x-show="result === 'X'" x-transition class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan <span class="text-red-500">*</span></label>
                        <textarea x-model="notes"
                                  @change="saveItem('{{ addslashes($itemKey) }}', result, notes)"
                                  rows="2" placeholder="Deskripsikan anomali..."
                                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-base focus:ring-2 focus:ring-gray-900 min-h-[60px]"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Foto
                            @if(!$isWeekly) <span class="text-red-500">*</span> @endif
                            <span class="text-gray-400 font-normal">(JPG/PNG/HEIC, max 5MB)</span>
                        </label>
                        <input type="file" accept=".jpg,.jpeg,.png,.heic"
                               @change="uploadPhoto($event, '{{ addslashes($itemKey) }}')"
                               class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-gray-900 file:text-white hover:file:bg-gray-700">

                        {{-- Existing photos --}}
                        @if($existing && $existing->photos)
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach($existing->photos as $photo)
                            <img src="{{ Storage::url($photo) }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                            @endforeach
                        </div>
                        @endif

                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="p in photos" :key="p.url">
                                <img :src="p.url" class="w-16 h-16 object-cover rounded-lg border border-gray-200">
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Abnormal Notes (Semesteran & Tahunan) --}}
    @if(in_array($session->schedule->frequency, ['semester', 'yearly']))
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <h3 class="text-base font-bold text-gray-900 mb-3">Catatan Abnormal</h3>
        <div id="abnormal-rows" class="space-y-3">
            @foreach($session->abnormals as $ab)
            <div class="grid grid-cols-5 gap-2 border border-gray-100 rounded-lg p-3 abnormal-row">
                <input type="date" name="abnormals[{{ $loop->index }}][tanggal]" value="{{ $ab->tanggal?->format('Y-m-d') }}" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                <input type="text" name="abnormals[{{ $loop->index }}][abnormal_description]" value="{{ $ab->abnormal_description }}" placeholder="Deskripsi" class="rounded border border-gray-300 px-2 py-1.5 text-sm col-span-1">
                <input type="text" name="abnormals[{{ $loop->index }}][penanganan]" value="{{ $ab->penanganan }}" placeholder="Penanganan" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                <input type="date" name="abnormals[{{ $loop->index }}][tgl_selesai]" value="{{ $ab->tgl_selesai?->format('Y-m-d') }}" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                <input type="text" name="abnormals[{{ $loop->index }}][pic]" value="{{ $ab->pic }}" placeholder="PIC" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
            </div>
            @endforeach
        </div>
        <button type="button" onclick="addAbnormalRow()" class="mt-3 text-sm text-blue-600 hover:text-blue-800 font-medium">+ Tambah Baris</button>
    </div>
    @endif

    {{-- Submit section --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h3 class="text-base font-bold text-gray-900 mb-4">Tanda Tangan</h3>
        <form action="{{ route('checksheet.submit', $session) }}" method="POST" id="submitForm" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dibuat oleh (Teknisi ONM) <span class="text-red-500">*</span></label>
                        <input type="text" name="signed_by_teknisi" value="{{ old('signed_by_teknisi', auth()->user()->name) }}" required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="signed_date_teknisi" value="{{ old('signed_date_teknisi', now()->toDateString()) }}" required
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Diperiksa oleh (SPV ONM)</label>
                        <input type="text" name="signed_by_spv" value="{{ old('signed_by_spv') }}"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="signed_date_spv" value="{{ old('signed_date_spv') }}"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Disetujui oleh (PM)</label>
                        <input type="text" name="signed_by_pm" value="{{ old('signed_by_pm') }}"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="signed_date_pm" value="{{ old('signed_date_pm') }}"
                               class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        :disabled="filled < {{ $total }}"
                        :title="filled < {{ $total }} ? 'Lengkapi semua item terlebih dahulu (' + filled + '/{{ $total }})' : 'Submit Checksheet'"
                        :class="filled >= {{ $total }}
                            ? 'bg-gray-900 hover:bg-gray-700 text-white cursor-pointer'
                            : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                        class="flex-1 text-sm font-medium py-3 rounded-lg min-h-[48px] transition-colors">
                    <span x-text="filled >= {{ $total }} ? 'Submit Checksheet' : 'Submit (' + filled + '/{{ $total }} terisi)'"></span>
                </button>
                <a href="{{ url()->previous() }}" class="px-6 flex items-center justify-center bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 min-h-[48px]">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
@php
    $confirmedItemsJson = json_encode(
        $results->whereNotNull('result')
            ->mapWithKeys(fn($r) => [$r->item_name => true])
            ->toArray()
    );
@endphp
function checksheetFill(sessionId, totalItems) {
    return {
        filled: {{ $results->whereNotNull('result')->count() }},
        confirmedItems: {!! $confirmedItemsJson !!},
        saveStatus: 'Belum disimpan',
        autosaveTimer: null,
        pendingItems: {},

        init() {
            this.autosaveTimer = setInterval(() => this.autosave(), 30000);
        },

        saveItem(templateId, result, notes) {
            this.pendingItems[templateId] = { result, notes };
            this.updateFilled();
            clearTimeout(this._debounce);
            this._debounce = setTimeout(() => this.autosave(), 2000);
        },

        updateFilled() {
            // Merge confirmed server state with pending changes
            const effective = { ...this.confirmedItems };
            Object.entries(this.pendingItems).forEach(([key, val]) => {
                if (val.result) effective[key] = true;
                else delete effective[key];
            });
            this.filled = Math.min(Object.keys(effective).length, totalItems);
        },

        async autosave() {
            if (Object.keys(this.pendingItems).length === 0) return;
            this.saveStatus = 'Menyimpan...';
            try {
                const resp = await fetch(`/checksheet/${sessionId}/autosave`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ items: this.pendingItems }),
                });
                const data = await resp.json();
                if (data.ok) {
                    // Update confirmed state so updateFilled stays accurate after clear
                    Object.entries(this.pendingItems).forEach(([key, val]) => {
                        if (val.result) this.confirmedItems[key] = true;
                        else delete this.confirmedItems[key];
                    });
                    this.pendingItems = {};
                    this.saveStatus = 'Tersimpan ' + data.saved_at;
                    this.updateFilled();
                }
            } catch (e) {
                this.saveStatus = 'Gagal menyimpan';
            }
        },

        async uploadPhoto(event, templateId) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            try {
                const resp = await fetch(`/checksheet/${sessionId}/upload-photo/${templateId}`, {
                    method: 'POST',
                    body: formData,
                });
                const data = await resp.json();
                if (data.url) {
                    this.photos.push({ url: data.url });
                }
            } catch (e) {
                alert('Gagal upload foto. Coba lagi.');
            }
        },
    };
}

let abnormalIdx = {{ $session->abnormals->count() }};
function addAbnormalRow() {
    const container = document.getElementById('abnormal-rows');
    const div = document.createElement('div');
    div.className = 'grid grid-cols-5 gap-2 border border-gray-100 rounded-lg p-3 abnormal-row';
    div.innerHTML = `
        <input type="date" name="abnormals[${abnormalIdx}][tanggal]" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
        <input type="text" name="abnormals[${abnormalIdx}][abnormal_description]" placeholder="Deskripsi" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
        <input type="text" name="abnormals[${abnormalIdx}][penanganan]" placeholder="Penanganan" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
        <input type="date" name="abnormals[${abnormalIdx}][tgl_selesai]" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
        <input type="text" name="abnormals[${abnormalIdx}][pic]" placeholder="PIC" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
    `;
    container.appendChild(div);
    abnormalIdx++;
}
</script>
@endpush
@endsection

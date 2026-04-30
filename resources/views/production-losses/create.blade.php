@extends('layouts.app')

@section('title', 'Tambah Loss of Production')

@section('breadcrumb')
    <a href="{{ route('production-losses.index') }}" class="text-sm text-gray-500 hover:text-brand">Loss of Production</a>
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-sm font-semibold text-gray-700">Tambah</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-5" x-data="lopForm()">

    <div>
        <h1 class="text-xl font-bold text-gray-900">Tambah Loss of Production</h1>
        <p class="text-sm text-gray-500 mt-0.5">Catat gangguan yang menyebabkan kehilangan produksi kWh</p>
    </div>

    <form action="{{ route('production-losses.store') }}" method="POST" @submit="submitting = true">
        @csrf

        {{-- Lokasi & Waktu --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Lokasi & Waktu Gangguan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if(auth()->user()->isAdmin())
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Lokasi PLTS <span class="text-red-500">*</span></label>
                    <select name="location_id" x-model="locationId" @change="loadSectors()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ $loc->id == $locationId ? 'selected' : '' }}>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                @else
                    <input type="hidden" name="location_id" value="{{ $locationId }}">
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Sektor</label>
                    <select name="sector_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                        <option value="">— Semua Sektor —</option>
                        <template x-for="s in sectors" :key="s.id">
                            <option :value="s.id" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Waktu Mulai <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="started_at"
                        value="{{ old('started_at', now()->format('Y-m-d\TH:i')) }}"
                        x-model="startedAt" @change="computeDuration()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                    @error('started_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Waktu Selesai</label>
                    <input type="datetime-local" name="ended_at"
                        value="{{ old('ended_at') }}"
                        x-model="endedAt" @change="computeDuration()"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                    <p class="text-xs text-gray-400 mt-1" x-show="durationLabel" x-text="'Durasi: ' + durationLabel"></p>
                </div>
            </div>

            {{-- Trafo / Inverter / String --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Trafo</label>
                    <input type="text" name="trafo" value="{{ old('trafo') }}" placeholder="cth: T18"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Inverter</label>
                    <input type="text" name="inverter" value="{{ old('inverter') }}" placeholder="cth: 3"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">String</label>
                    <input type="text" name="string_info" value="{{ old('string_info') }}" placeholder="cth: All / 11.12"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Jumlah String</label>
                    <input type="number" name="affected_strings" value="{{ old('affected_strings') }}" min="0" placeholder="cth: 27"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
            </div>
        </div>

        {{-- Kategori & Deskripsi --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 mt-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Kategori & Keterangan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kategori Gangguan <span class="text-red-500">*</span></label>
                    <select name="category"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                        @foreach(\App\Models\ProductionLoss::$categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Terkait Work Order</label>
                    <select name="work_order_id"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                        <option value="">— Tidak ada —</option>
                        @foreach($openWorkOrders as $wo)
                            <option value="{{ $wo->id }}" {{ old('work_order_id') == $wo->id ? 'selected' : '' }}>
                                {{ $wo->wo_number }} — {{ Str::limit($wo->title, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Deskripsi Gangguan</label>
                <textarea name="description" rows="3" placeholder="Jelaskan gangguan yang terjadi..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 resize-none">{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- LOP Values --}}
        <div class="bg-white border border-red-100 rounded-xl p-5 space-y-4 mt-4">
            <h2 class="text-sm font-bold text-red-600 uppercase tracking-wider">Nilai Loss of Production</h2>
            <p class="text-xs text-gray-400">LOP (kWh) = Kapasitas Terdampak (kW) × Durasi (jam). Jika diisi manual, nilai manual yang digunakan.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kapasitas Terdampak (kW)</label>
                    <input type="number" name="affected_capacity_kw" step="0.01" min="0"
                        value="{{ old('affected_capacity_kw') }}"
                        x-model="capacityKw" @input="computeLop()"
                        placeholder="cth: 442.26"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-red-400 mb-1.5">LOP (kWh) — auto/manual</label>
                    <input type="number" name="lop_kwh" step="0.01" min="0"
                        value="{{ old('lop_kwh') }}"
                        x-model="lopKwh"
                        placeholder="auto dari kapasitas × durasi"
                        class="w-full border border-red-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 font-mono font-bold text-red-700">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan untuk auto-hitung</p>
                </div>
                <div class="flex items-end">
                    <div class="w-full bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                        <p class="text-xs text-red-400 font-semibold">LOP Auto-Kalkulasi</p>
                        <p class="text-lg font-black text-red-600 font-mono" x-text="autoLop ? Number(autoLop.toFixed(2)).toLocaleString('id') + ' kWh' : '—'"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-4">
            <a href="{{ route('production-losses.index') }}"
                class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" :disabled="submitting"
                class="px-6 py-2 bg-brand text-white rounded-lg text-sm font-semibold hover:bg-brand/90 transition-colors disabled:opacity-60">
                <span x-show="!submitting">Simpan LOP</span>
                <span x-show="submitting">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function lopForm() {
    return {
        locationId: {{ $locationId ?? 'null' }},
        sectors: @json($sectors),
        startedAt: '{{ now()->format('Y-m-d\TH:i') }}',
        endedAt: '',
        durationLabel: '',
        durationMinutes: 0,
        capacityKw: null,
        lopKwh: null,
        autoLop: null,
        submitting: false,

        computeDuration() {
            if (!this.startedAt || !this.endedAt) { this.durationLabel = ''; this.durationMinutes = 0; this.computeLop(); return; }
            const start = new Date(this.startedAt);
            const end   = new Date(this.endedAt);
            const diff  = Math.max(0, Math.floor((end - start) / 60000));
            this.durationMinutes = diff;
            const h = Math.floor(diff / 60);
            const m = diff % 60;
            this.durationLabel = m > 0 ? `${h} jam ${m} menit` : `${h} jam`;
            this.computeLop();
        },

        computeLop() {
            const kw  = parseFloat(this.capacityKw) || 0;
            const dur = this.durationMinutes / 60;
            if (kw > 0 && dur > 0) {
                this.autoLop = kw * dur;
            } else {
                this.autoLop = null;
            }
        },

        async loadSectors() {
            const r = await fetch(`/production-reports/sectors?location_id=${this.locationId}`);
            this.sectors = await r.json();
        },
    }
}
</script>
@endpush

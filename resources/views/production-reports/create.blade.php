@extends('layouts.app')

@section('title', 'Tambah Production Report')

@section('breadcrumb')
    <a href="{{ route('production-reports.index') }}" class="text-sm text-gray-500 hover:text-brand">Production Report</a>
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-sm font-semibold text-gray-700">Tambah</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-5" x-data="productionForm()">

    <div>
        <h1 class="text-xl font-bold text-gray-900">Tambah Laporan Produksi</h1>
        <p class="text-sm text-gray-500 mt-0.5">Input data produksi kWh harian dan kondisi cuaca</p>
    </div>

    <form action="{{ route('production-reports.store') }}" method="POST" @submit="submitting = true">
        @csrf

        {{-- Header Info --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Informasi Laporan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Lokasi PLTS</label>
                    <p class="text-sm font-semibold text-gray-900 py-2">{{ $locations->first()?->name ?? '-' }}</p>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Laporan <span class="text-red-500">*</span></label>
                    <input type="date" name="report_date" value="{{ old('report_date', today()->toDateString()) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                    @error('report_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan tambahan opsional..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 resize-none">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Weather --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 mt-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Kondisi Cuaca Hari Ini</h2>
            <p class="text-xs text-gray-400">Isi jam operasional per kondisi cuaca (total tidak harus 10 jam)</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-yellow-600 mb-1.5">☀️ Cerah (jam)</label>
                    <input type="number" name="cerah_hours" step="0.5" min="0" max="24"
                        value="{{ old('cerah_hours', 0) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">⛅ Berawan (jam)</label>
                    <input type="number" name="berawan_hours" step="0.5" min="0" max="24"
                        value="{{ old('berawan_hours', 0) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">🌥 Mendung (jam)</label>
                    <input type="number" name="mendung_hours" step="0.5" min="0" max="24"
                        value="{{ old('mendung_hours', 0) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-blue-500 mb-1.5">🌧 Hujan (jam)</label>
                    <input type="number" name="hujan_hours" step="0.5" min="0" max="24"
                        value="{{ old('hujan_hours', 0) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
            </div>
        </div>

        {{-- Production Entries --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 mt-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Data Produksi per Sektor</h2>
                    <p class="text-xs text-gray-400 mt-0.5">CF akan dihitung otomatis dari kWh Meter ÷ (kWp × Sun Hour)</p>
                </div>
                <div x-show="loading" class="text-xs text-gray-400">Memuat sektor...</div>
            </div>

            <template x-if="sectors.length === 0 && !loading">
                <div class="py-8 text-center border border-dashed border-gray-200 rounded-lg">
                    <p class="text-sm text-gray-400">Belum ada sektor untuk lokasi ini.</p>
                    <a href="{{ route('settings.index') }}" class="text-xs text-brand hover:underline mt-1 block">
                        Tambah sektor di Settings
                    </a>
                </div>
            </template>

            <template x-if="sectors.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border border-gray-100 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">Sektor</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400">Kapasitas (kWp)</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">kWh Trafo</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">kWh Meter</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">Sun Hour</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400">CF (auto)</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-400">PR (manual)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(sector, i) in sectors" :key="sector.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <input type="hidden" :name="`entries[${i}][sector_id]`" :value="sector.id">
                                        <span class="font-semibold text-gray-800" x-text="sector.name"></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 text-xs font-mono" x-text="sector.capacity_kwp ? Number(sector.capacity_kwp).toLocaleString('id') + ' kWp' : '-'"></td>
                                    <td class="px-4 py-3">
                                        <input type="number" :name="`entries[${i}][kwh_trafo]`"
                                            x-model="sector.kwh_trafo"
                                            step="0.01" min="0" placeholder="0.00"
                                            class="w-32 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" :name="`entries[${i}][kwh_meter]`"
                                            x-model="sector.kwh_meter"
                                            @input="computeCf(sector)"
                                            step="0.01" min="0" placeholder="0.00"
                                            class="w-32 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" :name="`entries[${i}][sun_hour]`"
                                            x-model="sector.sun_hour"
                                            @input="computeCf(sector)"
                                            step="0.01" min="0" max="24" placeholder="0.00"
                                            class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="hidden" :name="`entries[${i}][capacity_factor]`" :value="sector.cf">
                                        <span class="text-xs font-mono text-gray-500"
                                            x-text="sector.cf ? (sector.cf * 100).toFixed(2) + '%' : '-'"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" :name="`entries[${i}][performance_ratio]`"
                                            x-model="sector.pr_manual"
                                            step="0.0001" min="0" max="2" placeholder="0.75"
                                            class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                    </td>
                                </tr>
                            </template>
                            {{-- Total row --}}
                            <tr class="bg-brand-50/50 font-bold">
                                <td class="px-4 py-3 text-xs text-gray-700 uppercase tracking-wider" colspan="2">TOTAL</td>
                                <td class="px-4 py-3 font-mono text-gray-900 text-sm"
                                    x-text="totalTrafo > 0 ? Number(totalTrafo.toFixed(2)).toLocaleString('id') : '-'"></td>
                                <td class="px-4 py-3 font-mono text-brand text-sm"
                                    x-text="totalMeter > 0 ? Number(totalMeter.toFixed(2)).toLocaleString('id') : '-'"></td>
                                <td class="px-4 py-3" colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-4">
            <a href="{{ route('production-reports.index') }}"
                class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" :disabled="submitting"
                class="px-6 py-2 bg-brand text-white rounded-lg text-sm font-semibold hover:bg-brand/90 transition-colors disabled:opacity-60">
                <span x-show="!submitting">Simpan Laporan</span>
                <span x-show="submitting">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function productionForm() {
    return {
        locationId: {{ $locationId ?? 'null' }},
        sectors: @json($sectors),
        loading: false,
        submitting: false,

        get totalTrafo() {
            return this.sectors.reduce((s, sec) => s + (parseFloat(sec.kwh_trafo) || 0), 0);
        },
        get totalMeter() {
            return this.sectors.reduce((s, sec) => s + (parseFloat(sec.kwh_meter) || 0), 0);
        },

        computeCf(sector) {
            const kwh = parseFloat(sector.kwh_meter) || 0;
            const sh = parseFloat(sector.sun_hour) || 0;
            const kwp = parseFloat(sector.capacity_kwp) || 0;
            if (kwh > 0 && sh > 0 && kwp > 0) {
                sector.cf = (kwh / (kwp * sh)).toFixed(4);
            } else {
                sector.cf = null;
            }
        },

        async loadSectors() {
            this.loading = true;
            this.sectors = [];
            try {
                const r = await fetch(`/production-reports/sectors?location_id=${this.locationId}`);
                const data = await r.json();
                this.sectors = data.map(s => ({ ...s, kwh_trafo: null, kwh_meter: null, sun_hour: null, cf: null, pr_manual: null }));
            } catch (e) {}
            this.loading = false;
        },

        init() {
            this.sectors = this.sectors.map(s => ({
                ...s, kwh_trafo: null, kwh_meter: null, sun_hour: null, cf: null, pr_manual: null
            }));
        }
    }
}
</script>
@endpush

@extends('layouts.app')

@section('title', 'Edit Production Report')

@section('breadcrumb')
    <a href="{{ route('production-reports.index') }}" class="text-sm text-gray-500 hover:text-brand">Production Report</a>
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <a href="{{ route('production-reports.show', $productionReport) }}" class="text-sm text-gray-500 hover:text-brand">
        {{ $productionReport->report_date->format('d M Y') }}
    </a>
    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
    <span class="text-sm font-semibold text-gray-700">Edit</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-5" x-data="editProductionForm()">

    <div>
        <h1 class="text-xl font-bold text-gray-900">Edit Laporan Produksi</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $productionReport->report_date->translatedFormat('l, d F Y') }} — {{ $productionReport->location->name }}</p>
    </div>

    <form action="{{ route('production-reports.update', $productionReport) }}" method="POST" @submit="submitting = true">
        @csrf
        @method('PUT')

        {{-- Header Info --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Informasi Laporan</h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Lokasi PLTS</label>
                    <p class="text-sm font-semibold text-gray-900 py-2">{{ $productionReport->location->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Laporan <span class="text-red-500">*</span></label>
                    <input type="date" name="report_date"
                        value="{{ old('report_date', $productionReport->report_date->toDateString()) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                    @error('report_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Catatan</label>
                <textarea name="notes" rows="2" placeholder="Catatan tambahan opsional..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 resize-none">{{ old('notes', $productionReport->notes) }}</textarea>
            </div>
        </div>

        {{-- Weather --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 mt-4">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Kondisi Cuaca</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-yellow-600 mb-1.5">☀️ Cerah (jam)</label>
                    <input type="number" name="cerah_hours" step="0.5" min="0" max="24"
                        value="{{ old('cerah_hours', $productionReport->cerah_hours) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5">⛅ Berawan (jam)</label>
                    <input type="number" name="berawan_hours" step="0.5" min="0" max="24"
                        value="{{ old('berawan_hours', $productionReport->berawan_hours) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">🌥 Mendung (jam)</label>
                    <input type="number" name="mendung_hours" step="0.5" min="0" max="24"
                        value="{{ old('mendung_hours', $productionReport->mendung_hours) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-blue-500 mb-1.5">🌧 Hujan (jam)</label>
                    <input type="number" name="hujan_hours" step="0.5" min="0" max="24"
                        value="{{ old('hujan_hours', $productionReport->hujan_hours) }}"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
            </div>
        </div>

        {{-- Production Entries --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 mt-4">
            <div>
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Data Produksi per Sektor</h2>
                <p class="text-xs text-gray-400 mt-0.5">CF akan dihitung otomatis dari kWh Meter ÷ (kWp × Sun Hour)</p>
            </div>

            @if($sectors->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6 border border-dashed border-gray-200 rounded-lg">
                    Belum ada sektor untuk lokasi ini.
                </p>
            @else
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
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" x-data="editProductionForm()">
                            @foreach($sectors as $i => $sector)
                            @php $existing = $existingEntries[$sector->id] ?? null; @endphp
                            <tr class="hover:bg-gray-50" x-data="{
                                kwh_meter: {{ $existing?->kwh_meter ?? 'null' }},
                                sun_hour: {{ $existing?->sun_hour ?? 'null' }},
                                kwp: {{ $sector->capacity_kwp ?? 0 }},
                                cf: {{ $existing?->capacity_factor ?? 'null' }},
                                computeCf() {
                                    const kwh = parseFloat(this.kwh_meter) || 0;
                                    const sh = parseFloat(this.sun_hour) || 0;
                                    if (kwh > 0 && sh > 0 && this.kwp > 0) {
                                        this.cf = (kwh / (this.kwp * sh)).toFixed(4);
                                    } else { this.cf = null; }
                                }
                            }">
                                <td class="px-4 py-3">
                                    <input type="hidden" name="entries[{{ $i }}][sector_id]" value="{{ $sector->id }}">
                                    <span class="font-semibold text-gray-800">{{ $sector->name }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400 font-mono">
                                    {{ $sector->capacity_kwp ? number_format($sector->capacity_kwp, 0, ',', '.') . ' kWp' : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="entries[{{ $i }}][kwh_trafo]"
                                        value="{{ old("entries.{$i}.kwh_trafo", $existing?->kwh_trafo) }}"
                                        step="0.01" min="0" placeholder="0.00"
                                        class="w-32 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="entries[{{ $i }}][kwh_meter]"
                                        x-model="kwh_meter" @input="computeCf()"
                                        value="{{ old("entries.{$i}.kwh_meter", $existing?->kwh_meter) }}"
                                        step="0.01" min="0" placeholder="0.00"
                                        class="w-32 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="entries[{{ $i }}][sun_hour]"
                                        x-model="sun_hour" @input="computeCf()"
                                        value="{{ old("entries.{$i}.sun_hour", $existing?->sun_hour) }}"
                                        step="0.01" min="0" max="24" placeholder="0.00"
                                        class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/30 font-mono">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="entries[{{ $i }}][capacity_factor]" :value="cf">
                                    <span class="text-xs font-mono text-gray-500"
                                        x-text="cf ? (cf * 100).toFixed(2) + '%' : '-'"></span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-4">
            <a href="{{ route('production-reports.show', $productionReport) }}"
                class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" :disabled="submitting"
                class="px-6 py-2 bg-brand text-white rounded-lg text-sm font-semibold hover:bg-brand/90 transition-colors disabled:opacity-60">
                <span x-show="!submitting">Simpan Perubahan</span>
                <span x-show="submitting">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function editProductionForm() {
    return { submitting: false }
}
</script>
@endpush

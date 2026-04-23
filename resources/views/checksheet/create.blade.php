@extends('layouts.app')

@section('title', 'Buat Checksheet')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li><a href="{{ route('checksheet.index') }}" class="hover:text-gray-700">Checksheet</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">Buat Baru</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold text-gray-900">Buat Checksheet Baru</h1>

    <form action="{{ route('checksheet.store') }}" method="POST" class="bg-white rounded-lg border border-gray-200 p-6 space-y-5">
        @csrf

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Checksheet <span class="text-red-500">*</span></label>
            @php $allTypes = \App\Models\ChecksheetType::all(); @endphp
            <select name="checksheet_type_id" id="type_sel" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                <option value="">-- Pilih Tipe --</option>
                @foreach($allTypes as $t)
                    <option value="{{ $t->id }}" data-freq="{{ $t->frequency }}" {{ old('checksheet_type_id', $type->id ?? '') == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
            @error('checksheet_type_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- PLTS Location --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi PLTS <span class="text-red-500">*</span></label>
            <select name="plts_location" required
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                <option value="">-- Pilih Lokasi --</option>
                @foreach(['PLTS Pertiwi Lestari', 'PLTS Rengiat', 'PLTS Demo Site'] as $loc)
                    <option value="{{ $loc }}" {{ old('plts_location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                @endforeach
            </select>
            @error('plts_location') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Equipment Location --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Lokasi Peralatan</label>
            <input type="text" name="equipment_location" value="{{ old('equipment_location') }}"
                   placeholder="e.g. Gedung Utama, Panel Room"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
        </div>

        {{-- Year --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tahun <span class="text-red-500">*</span></label>
            <input type="number" name="year" value="{{ old('year', now()->year) }}" required min="2020" max="2030"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
        </div>

        {{-- Conditional period fields --}}
        <div id="field_week" style="display:none" class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bulan</label>
                <select name="month" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ old('month', now()->month) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Minggu</label>
                <select name="week_number" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                    @foreach([1,2,3,4] as $w)
                        <option value="{{ $w }}" {{ old('week_number', now()->weekOfMonth) == $w ? 'selected' : '' }}>W{{ $w }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="field_month" style="display:none">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Bulan</label>
            <select name="month_only" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ now()->month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromDate(null, $m, 1)->format('F') }}</option>
                @endforeach
            </select>
        </div>

        <div id="field_semester" style="display:none">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Semester</label>
            <select name="semester" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-gray-900">
                <option value="1">Semester 1 (Jan – Jun)</option>
                <option value="2">Semester 2 (Jul – Des)</option>
            </select>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 bg-brand-dark text-white font-bold text-sm font-medium py-2.5 rounded-lg hover:bg-gray-700 transition-colors">
                Buat & Mulai Isi
            </button>
            <a href="{{ url()->previous() }}" class="flex-1 text-center bg-white border border-gray-300 text-gray-700 text-sm font-medium py-2.5 rounded-lg hover:bg-opacity-90 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
const typeSel = document.getElementById('type_sel');
function updateFields() {
    const opt = typeSel.options[typeSel.selectedIndex];
    const freq = opt ? opt.dataset.freq : '';
    document.getElementById('field_week').style.display = freq === 'weekly' ? '' : 'none';
    document.getElementById('field_month').style.display = freq === 'monthly' ? '' : 'none';
    document.getElementById('field_semester').style.display = freq === 'semester' ? '' : 'none';
}
typeSel.addEventListener('change', updateFields);
updateFields();
</script>
@endpush
@endsection

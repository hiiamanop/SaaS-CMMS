@extends('layouts.app')

@section('title', 'Template Checksheet')

@section('content')
<div class="flex gap-6">

    {{-- Left: Daftar Jadwal --}}
    <div class="w-72 flex-shrink-0">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden sticky top-6">
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700">Daftar Jadwal</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pilih jadwal untuk kelola template</p>
            </div>
            <div class="overflow-y-auto max-h-[calc(100vh-200px)]">
                @php $grouped = $schedules->groupBy('category'); @endphp
                @foreach($grouped as $cat => $items)
                    <div class="px-3 py-2 bg-gray-50 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $cat }}</span>
                    </div>
                    @foreach($items as $sch)
                    @php $itemCount = $sch->checklistTemplates->count(); @endphp
                    <a href="{{ route('checksheet.templates.index', ['schedule_id' => $sch->id]) }}"
                       class="flex items-start gap-3 px-4 py-3 border-b border-gray-50 hover:bg-blue-50 transition-colors
                              {{ $activeSchedule?->id === $sch->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $sch->equipment_name }}</p>
                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                {{ count($sch->item_pekerjaan ?? []) }} pekerjaan
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 font-medium">
                                    {{ match ($sch->frequency) {
                                        'weekly' => 'Mingguan', 'monthly' => 'Bulanan',
                                        'quarterly' => 'Semesteran', 'annually' => 'Tahunan',
                                        default => $sch->frequency,
                                    } }}
                                </span>
                                @if($itemCount > 0)
                                    <span class="text-xs text-green-600 font-medium">✓ {{ $itemCount }} sub-item</span>
                                @else
                                    <span class="text-xs text-orange-500">Belum ada sub-item</span>
                                @endif
                            </div>
                        </div>
                    </a>
                    @endforeach
                @endforeach

                @if($schedules->isEmpty())
                    <div class="px-4 py-8 text-center text-sm text-gray-400">
                        Belum ada jadwal aktif.
                        <a href="{{ route('maintenance-schedules.create') }}" class="text-blue-600 hover:underline block mt-1">Buat jadwal dulu</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Template Editor --}}
    <div class="flex-1 min-w-0">

        @if(!$activeSchedule)
            <div class="h-64 flex items-center justify-center bg-white rounded-xl border border-dashed border-gray-300">
                <div class="text-center">
                    <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500">Pilih jadwal di sebelah kiri</p>
                </div>
            </div>
        @else

        @php
            $pekerjaanList = (array) ($activeSchedule->item_pekerjaan ?? []);
            $templatesByLokasi = $activeSchedule->checklistTemplates->groupBy('lokasi_inspeksi');
        @endphp

        {{-- Header jadwal aktif --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded font-medium">
                            {{ match ($activeSchedule->frequency) {
                                'weekly' => 'Mingguan', 'monthly' => 'Bulanan',
                                'quarterly' => 'Semesteran', 'annually' => 'Tahunan',
                                default => $activeSchedule->frequency,
                            } }}
                        </span>
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $activeSchedule->category }}</span>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $activeSchedule->equipment_name }}</h1>
                    @if(!empty($pekerjaanList))
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @foreach($pekerjaanList as $pek)
                        <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">{{ $pek }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if($activeSchedule->technician)
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Teknisi: {{ $activeSchedule->technician->name }}
                    </p>
                    @endif
                </div>
                <a href="{{ route('maintenance-schedules.edit', $activeSchedule) }}"
                   class="text-xs text-gray-500 hover:text-blue-600 flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:border-blue-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Jadwal
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('success') }}
        </div>
        @endif

        @if(empty($pekerjaanList))
        {{-- No item_pekerjaan defined yet --}}
        <div class="text-center py-12 bg-white rounded-xl border border-dashed border-orange-300 mb-5">
            <svg class="mx-auto w-10 h-10 text-orange-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-600 font-medium">Item Pekerjaan belum diisi</p>
            <p class="text-gray-400 text-sm mt-1">Tambahkan item pekerjaan di jadwal terlebih dahulu</p>
            <a href="{{ route('maintenance-schedules.edit', $activeSchedule) }}"
               class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-orange-500 text-white text-sm rounded-lg hover:bg-orange-600">
                Edit Jadwal → Tambah Item Pekerjaan
            </a>
        </div>
        @else

        {{-- One section per item_pekerjaan --}}
        @foreach($pekerjaanList as $pekIdx => $pekerjaan)
        @php
            $sectionItems = $templatesByLokasi->get($pekerjaan, collect());
        @endphp

        <div class="mb-5 bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm"
             x-data="{ addOpen: false }">

            {{-- Section Header --}}
            <div class="flex items-center justify-between px-5 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0 text-white text-xs font-bold">
                        {{ $pekIdx + 1 }}
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800 text-sm">{{ $pekerjaan }}</h3>
                        <p class="text-xs text-gray-500">{{ $sectionItems->count() }} sub-item</p>
                    </div>
                </div>
                <button @click="addOpen = !addOpen"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Sub-Item
                </button>
            </div>

            {{-- Add sub-item inline form --}}
            <div x-show="addOpen" x-cloak class="px-5 py-4 bg-blue-50 border-b border-blue-100">
                <form method="POST" action="{{ route('checksheet.templates.store-item', $activeSchedule) }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="lokasi_inspeksi" value="{{ $pekerjaan }}">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Item Inspeksi / Sub Pekerjaan <span class="text-red-500">*</span></label>
                            <input type="text" name="item_inspeksi"
                                   placeholder="cth: Cek kondisi fisik, ukur tegangan output..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required autofocus>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Metode Inspeksi</label>
                            <input type="text" name="metode_inspeksi" placeholder="cth: Visual, Pengukuran, Pengujian..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Standar / Ketentuan</label>
                            <input type="text" name="standar_ketentuan" placeholder="cth: Tidak ada kerusakan, tegangan > 380V..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Simpan Sub-Item</button>
                        <button type="button" @click="addOpen = false" class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-white">Batal</button>
                    </div>
                </form>
            </div>

            {{-- Sub-items table --}}
            @if($sectionItems->isEmpty())
            <div class="px-5 py-4 text-sm text-gray-400 text-center">
                Belum ada sub-item. Klik "Tambah Sub-Item" untuk menambahkan.
            </div>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-2 text-left w-8">#</th>
                        <th class="px-4 py-2 text-left">Item Inspeksi</th>
                        <th class="px-4 py-2 text-left w-40">Metode</th>
                        <th class="px-4 py-2 text-left w-48">Standar</th>
                        <th class="px-4 py-2 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($sectionItems as $i => $item)
                    <tr x-data="{ editing: false }" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 align-top" x-show="!editing">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 text-gray-800 align-top" x-show="!editing">{{ $item->item_inspeksi }}</td>
                        <td class="px-4 py-3 text-gray-500 align-top text-xs" x-show="!editing">{{ $item->metode_inspeksi ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 align-top text-xs" x-show="!editing">{{ $item->standar_ketentuan ?? '—' }}</td>
                        <td class="px-4 py-3 text-center align-top" x-show="!editing">
                            <div class="flex items-center justify-center gap-1">
                                <button @click="editing = true" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('checksheet.templates.destroy-item', $item) }}"
                                      onsubmit="return confirm('Hapus sub-item ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        {{-- Inline edit --}}
                        <td colspan="5" class="px-4 py-3 bg-blue-50" x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('checksheet.templates.update-item', $item) }}" class="space-y-3">
                                @csrf @method('PUT')
                                <input type="hidden" name="lokasi_inspeksi" value="{{ $pekerjaan }}">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Item Inspeksi</label>
                                        <input type="text" name="item_inspeksi" value="{{ $item->item_inspeksi }}"
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Metode Inspeksi</label>
                                        <input type="text" name="metode_inspeksi" value="{{ $item->metode_inspeksi }}"
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Standar / Ketentuan</label>
                                        <input type="text" name="standar_ketentuan" value="{{ $item->standar_ketentuan }}"
                                               class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">Simpan</button>
                                    <button type="button" @click="editing = false" class="px-4 py-1.5 bg-white border border-gray-300 text-gray-700 text-sm rounded-lg">Batal</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @endforeach

        {{-- Sub-items for templates not matching any item_pekerjaan (orphaned) --}}
        @php
            $orphaned = $activeSchedule->checklistTemplates->filter(fn($t) => !in_array($t->lokasi_inspeksi, $pekerjaanList));
        @endphp
        @if($orphaned->count())
        <div class="mb-5 bg-white rounded-xl border border-orange-200 overflow-hidden shadow-sm">
            <div class="px-5 py-3 bg-orange-50 border-b border-orange-100">
                <p class="text-sm font-semibold text-orange-700">Item tanpa Pekerjaan ({{ $orphaned->count() }})</p>
                <p class="text-xs text-orange-500">Item ini tidak terhubung ke item pekerjaan manapun</p>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach($orphaned as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-400 text-xs w-32">{{ $item->lokasi_inspeksi }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $item->item_inspeksi }}</td>
                        <td class="px-4 py-2 text-center w-16">
                            <form method="POST" action="{{ route('checksheet.templates.destroy-item', $item) }}"
                                  onsubmit="return confirm('Hapus?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Summary --}}
        <div class="mt-2 px-4 py-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between text-sm text-gray-500">
            <span>
                <span class="font-semibold text-gray-800">{{ $activeSchedule->checklistTemplates->count() }}</span> total sub-item ·
                <span class="font-semibold text-gray-800">{{ count($pekerjaanList) }}</span> item pekerjaan
            </span>
            <span class="text-xs">Template digunakan teknisi saat mengisi checksheet</span>
        </div>

        @endif {{-- end empty pekerjaanList --}}
        @endif {{-- end activeSchedule --}}
    </div>
</div>
@endsection

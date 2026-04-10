@extends('layouts.app')

@section('title', 'Checksheet — ' . $session->schedule->equipment_name)

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li><a href="{{ route('checksheet.index') }}" class="hover:text-gray-700">Checksheet</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">{{ $session->schedule->equipment_name }} — {{ $session->period_label }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-gray-900">Checksheet {{ $session->schedule->equipment_name }}</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $session->status === 'submitted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ ucfirst($session->status) }}
                </span>
            </div>
            <p class="text-sm text-gray-500">{{ $session->plts_location }} · {{ $session->period_label }} · {{ $session->year }}</p>
        </div>
        <div class="flex gap-2">
            @if($session->status === 'submitted')
            <a href="{{ route('checksheet.pdf', $session) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Export PDF
            </a>
            @endif
        </div>
    </div>

    {{-- Info card --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-gray-500">Tipe</p>
            <p class="font-medium text-gray-900">{{ $session->schedule->equipment_name }}</p>
        </div>
        <div>
            <p class="text-gray-500">Lokasi PLTS</p>
            <p class="font-medium text-gray-900">{{ $session->plts_location }}</p>
        </div>
        <div>
            <p class="text-gray-500">Periode</p>
            <p class="font-medium text-gray-900">{{ $session->period_label }}</p>
        </div>
        @if($session->submitted_at)
        <div>
            <p class="text-gray-500">Disubmit</p>
            <p class="font-medium text-gray-900">{{ $session->submitted_at->format('d M Y H:i') }}</p>
        </div>
        @endif
    </div>

    {{-- Results grouped --}}
    @php $grouped = $templates->groupBy('lokasi_inspeksi'); @endphp
    @foreach($grouped as $lokasi => $items)
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2.5 font-bold text-gray-900 border-b border-gray-200">{{ $lokasi }}</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Item Inspeksi</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium hidden md:table-cell">Metode</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium hidden md:table-cell">Standar</th>
                    <th class="px-4 py-2 text-center text-gray-600 font-medium">Hasil</th>
                    <th class="px-4 py-2 text-left text-gray-600 font-medium">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $template)
                @php $res = $results[$template->id] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-900">{{ $template->item_inspeksi }}</td>
                    <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $template->metode_inspeksi }}</td>
                    <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $template->standar_ketentuan }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($res?->result === 'P')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">P</span>
                        @elseif($res?->result === 'X')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">X</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($res?->notes)
                            <p class="text-gray-600 text-xs">{{ $res->notes }}</p>
                        @endif
                        @if($res?->photos)
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($res->photos as $photo)
                            <img src="{{ Storage::url($photo) }}" class="w-10 h-10 object-cover rounded border border-gray-200 cursor-pointer"
                                 onclick="window.open('{{ Storage::url($photo) }}','_blank')">
                            @endforeach
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    {{-- Abnormals --}}
    @if($session->abnormals->isNotEmpty())
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 px-4 py-2.5 font-bold text-gray-900 border-b border-gray-200">Catatan Abnormal</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Tanggal</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Deskripsi Abnormal</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Penanganan</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Tgl Selesai</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">PIC</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($session->abnormals as $ab)
                <tr>
                    <td class="px-4 py-3">{{ $ab->tanggal?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $ab->abnormal_description ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $ab->penanganan ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $ab->tgl_selesai?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $ab->pic ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Signatures --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5">
        <h3 class="font-bold text-gray-900 mb-4">Tanda Tangan</h3>
        <div class="grid grid-cols-3 gap-6">
            <div class="text-center border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-2">Dibuat oleh (Teknisi ONM)</p>
                <p class="font-medium text-gray-900">{{ $session->signed_by_teknisi ?? '—' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $session->signed_date_teknisi?->format('d M Y') ?? '' }}</p>
            </div>
            <div class="text-center border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-2">Diperiksa oleh (SPV ONM)</p>
                <p class="font-medium text-gray-900">{{ $session->signed_by_spv ?? '—' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $session->signed_date_spv?->format('d M Y') ?? '' }}</p>
            </div>
            <div class="text-center border border-gray-200 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-2">Disetujui oleh (PM)</p>
                <p class="font-medium text-gray-900">{{ $session->signed_by_pm ?? '—' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $session->signed_date_pm?->format('d M Y') ?? '' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

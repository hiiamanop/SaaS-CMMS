@extends('layouts.app')

@section('title', 'Checksheet')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">Checksheet</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Checksheet</h1>
            <p class="text-sm text-gray-500 mt-1">Inspeksi kondisi PLTS berkala</p>
        </div>
        <a href="{{ route('checksheet.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Buat Checksheet Baru
        </a>
    </div>

    {{-- Today's pending checksheets grouped by type --}}
    @php
        $typeLabels = ['weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'semester' => 'Semesteran', 'yearly' => 'Tahunan'];
        $typeColors = ['weekly' => 'blue', 'monthly' => 'purple', 'semester' => 'orange', 'yearly' => 'green'];
    @endphp

    @foreach($types as $type)
        @php $typeSessions = $pending[$type->frequency] ?? []; @endphp
        <div>
            <h2 class="text-base font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($type->frequency==='weekly') bg-blue-100 text-blue-800
                    @elseif($type->frequency==='monthly') bg-purple-100 text-purple-800
                    @elseif($type->frequency==='semester') bg-orange-100 text-orange-800
                    @else bg-green-100 text-green-800 @endif">
                    {{ $type->name }}
                </span>
                {{ count($typeSessions) }} sesi
            </h2>

            @if(empty($typeSessions))
                <div class="bg-white rounded-lg border border-gray-200 p-6 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                    <p class="text-sm">Tidak ada checksheet {{ $type->name }} saat ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($typeSessions as $session)
                    <a href="{{ $session->status === 'submitted' ? route('checksheet.show', $session) : route('checksheet.fill', $session) }}"
                       class="bg-white rounded-lg border border-gray-200 p-5 hover:shadow-md transition-shadow block min-h-[120px]">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-semibold text-gray-900 text-lg">{{ $session->plts_location }}</p>
                                <p class="text-sm text-gray-500">{{ $session->period_label }}</p>
                                @if($session->equipment_location)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $session->equipment_location }}</p>
                                @endif
                            </div>
                            @if($session->status === 'submitted')
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Submitted</span>
                            @else
                                @php
                                    $total = $session->type->templates()->count();
                                    $filled = $session->results()->whereNotNull('result')->count();
                                @endphp
                                @if($filled > 0)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Progress</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Not Started</span>
                                @endif
                            @endif
                        </div>
                        @if($session->status !== 'submitted')
                        @php $pct = $total > 0 ? round(($filled / $total) * 100) : 0; @endphp
                        <div class="mt-auto">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>{{ $filled }} / {{ $total }} item</span>
                                <span>{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : ($pct > 0 ? 'bg-blue-500' : 'bg-gray-300') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        @endif
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
@endsection

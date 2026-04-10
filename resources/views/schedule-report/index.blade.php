@extends('layouts.app')

@section('title', 'Schedule Report')

@section('breadcrumb')
<nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-sm text-gray-500">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-700">Dashboard</a></li>
        <li><span class="mx-2">/</span></li>
        <li class="text-gray-900 font-medium">Schedule Report</li>
    </ol>
</nav>
@endsection

@section('content')
<div x-data="{ tab: '{{ request('tab', 'schedule') }}' }" class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Schedule Report</h1>
    </div>

    {{-- Tab Navigation --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8 overflow-x-auto">
            @foreach([
                'schedule' => 'Schedule Maintenance',
                'mingguan' => 'Checksheet Mingguan',
                'bulanan' => 'Checksheet Bulanan',
                'semesteran' => 'Checksheet Semesteran',
                'tahunan' => 'Checksheet Tahunan',
            ] as $key => $label)
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}' ? 'border-b-2 border-gray-900 text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="py-3 px-1 text-sm whitespace-nowrap transition-colors">
                {{ $label }}
            </button>
            @endforeach
        </nav>
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="bg-white rounded-lg border border-gray-200 p-4">
        <input type="hidden" name="tab" :value="tab">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tahun</label>
                <select name="year" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:ring-2 focus:ring-gray-900">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi PLTS</label>
                <select name="plts_location" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:ring-2 focus:ring-gray-900">
                    <option value="">Semua Lokasi</option>
                    @foreach($pltsLocations as $loc)
                        <option value="{{ $loc }}" {{ $pltsLocation == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="tab === 'schedule'">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                <select name="category" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:ring-2 focus:ring-gray-900">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-1.5 bg-gray-900 text-white text-sm rounded-lg hover:bg-gray-700">
                Filter
            </button>
            <a :href="'/schedule-report/pdf/' + tab + '?year={{ $year }}&plts_location={{ urlencode($pltsLocation ?? '') }}'"
               target="_blank"
               class="px-4 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Export PDF
            </a>
        </div>
    </form>

    {{-- Tab: Schedule Maintenance --}}
    <div x-show="tab === 'schedule'" x-transition>
        @include('schedule-report.partials.schedule', compact('schedules', 'workOrders', 'sessions', 'year'))
    </div>

    {{-- Tab: Mingguan --}}
    <div x-show="tab === 'mingguan'" x-transition>
        @include('schedule-report.partials.mingguan', compact('sessions', 'year', 'pltsLocation'))
    </div>

    {{-- Tab: Bulanan --}}
    <div x-show="tab === 'bulanan'" x-transition>
        @include('schedule-report.partials.bulanan', compact('sessions', 'year', 'pltsLocation'))
    </div>

    {{-- Tab: Semesteran --}}
    <div x-show="tab === 'semesteran'" x-transition>
        @include('schedule-report.partials.semesteran', compact('sessions', 'year', 'pltsLocation'))
    </div>

    {{-- Tab: Tahunan --}}
    <div x-show="tab === 'tahunan'" x-transition>
        @include('schedule-report.partials.tahunan', compact('sessions', 'year', 'pltsLocation'))
    </div>

</div>
@endsection

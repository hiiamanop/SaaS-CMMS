@extends('layouts.app')
@section('title','Tools')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Tools</span>@endsection
@section('content')
<div class="space-y-5" x-data="{ selectedTool: null }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tools</h1>
            <p class="text-sm text-gray-500 mt-0.5">Measurement and hand tools management</p>
        </div>
        <div class="flex flex-col items-end gap-1" x-data="{ uploading: false }">
            <div class="flex items-center gap-2">
                {{-- Export CSV (Available for ALL users) --}}
                <a href="{{ route('tools.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all h-[38px]" title="Export to CSV">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export CSV</span>
                </a>

                @if(auth()->user()->isAdminOrSupervisor())
                <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data" class="hidden" id="import-form">
                    @csrf
                    <input type="hidden" name="type" value="tool">
                    <input type="file" name="file" id="import-file" @change="uploading = true; $el.form.submit()" accept=".csv, .xlsx, .xls">
                </form>

                <button @click="document.getElementById('import-file').click()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-900 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all h-[38px]"
                        :disabled="uploading">
                    <svg x-show="!uploading" class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242M12 12v9m-4-4l4 4 4-4"/></svg>
                    <svg x-show="uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="uploading ? 'Importing...' : 'Import Tools'"></span>
                </button>

                <a href="{{ route('tools.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-dark text-white rounded-lg text-sm font-bold hover:bg-gray-700 shadow-sm transition-all h-[38px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    <span>Add Tool</span>
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search tools..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
            <select name="condition" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Conditions</option>
                @foreach($conditions as $cond)<option value="{{ $cond }}" {{ request('condition')==$cond?'selected':'' }}>{{ ucfirst($cond) }}</option>@endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-dark text-white font-bold rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('tools.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($tools->isEmpty())
        <div class="py-16 text-center"><p class="text-gray-400">No tools found</p></div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Code</th><th class="px-5 py-3 text-left">Name</th><th class="px-5 py-3 text-left">Brand</th><th class="px-5 py-3 text-left">Condition</th><th class="px-5 py-3 text-left">Stock</th><th class="px-5 py-3 text-left">Location</th><th class="px-5 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($tools as $tool)
            <tr class="hover:bg-opacity-90 transition-colors">
                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $tool->tool_code }}</td>
                <td class="px-5 py-3 font-medium text-gray-900">
                    <button type="button" @click="selectedTool = {{ json_encode($tool) }}" class="text-left font-medium text-gray-900 hover:text-brand transition-colors">
                        {{ $tool->name }}
                    </button>
                </td>
                <td class="px-5 py-3 text-gray-500">{{ $tool->brand ?: '—' }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $tool->condition === 'good' ? 'bg-green-100 text-green-700' : ($tool->condition === 'damaged' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                        {{ ucfirst($tool->condition) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-600 font-medium">{{ $tool->qty_available }} / {{ $tool->qty_total }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $tool->location ?: '—' }}</td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                        {{-- View Details (Available for ALL users) --}}
                        <button type="button" @click="selectedTool = {{ json_encode($tool) }}" class="p-1.5 text-gray-400 hover:text-brand hover:bg-emerald-50 rounded-lg transition-colors" title="View Details">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        {{-- Edit & Delete (Admin & Supervisor) --}}
                        @if(auth()->user()->isAdminOrSupervisor())
                        <a href="{{ route('tools.edit', $tool) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('tools.destroy',$tool) }}',message:'Delete tool {{ addslashes($tool->name) }}?'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $tools->links() }}</div>
        @endif
    </div>

    {{-- Tool Quick Detail Modal --}}
    <div x-show="selectedTool" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-gray-100"
             @click.outside="selectedTool = null"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-gray-100 text-gray-600" x-text="selectedTool?.tool_code"></span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold capitalize"
                              :class="selectedTool?.condition === 'good' ? 'bg-green-100 text-green-700' : (selectedTool?.condition === 'damaged' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')"
                              x-text="selectedTool?.condition"></span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900" x-text="selectedTool?.name"></h3>
                </div>
                <button @click="selectedTool = null" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="bg-gray-50 p-3 rounded-xl">
                    <span class="text-xs font-medium text-gray-500 block">Brand / Merek</span>
                    <span class="font-semibold text-gray-900" x-text="selectedTool?.brand || '—'"></span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl">
                    <span class="text-xs font-medium text-gray-500 block">Kategori</span>
                    <span class="font-semibold text-gray-900" x-text="selectedTool?.category || '—'"></span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl">
                    <span class="text-xs font-medium text-gray-500 block">Ketersediaan Stok</span>
                    <span class="font-semibold text-gray-900"><span x-text="selectedTool?.qty_available"></span> / <span x-text="selectedTool?.qty_total"></span> Tersedia</span>
                </div>
                <div class="bg-gray-50 p-3 rounded-xl">
                    <span class="text-xs font-medium text-gray-500 block">Lokasi Penyimpanan</span>
                    <span class="font-semibold text-gray-900" x-text="selectedTool?.location || '—'"></span>
                </div>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl text-sm" x-show="selectedTool?.description">
                <span class="text-xs font-medium text-gray-500 block mb-1">Keterangan / Spesifikasi</span>
                <p class="text-gray-700 text-xs leading-relaxed" x-text="selectedTool?.description"></p>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <button type="button"
                        @click="printQrLabel({
                            title: selectedTool?.name,
                            code: selectedTool?.tool_code,
                            category: selectedTool?.category || 'Tool',
                            location: selectedTool?.location || 'Rak Tools',
                            qrValue: selectedTool?.tool_code
                        })"
                        class="px-3.5 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 rounded-lg text-xs font-bold shadow-sm flex items-center gap-1.5 transition-all">
                    <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    Print Label QR
                </button>
                <div class="flex gap-2">
                    <button @click="selectedTool = null" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50">Tutup</button>
                    @if(auth()->user()->isAdminOrSupervisor())
                    <a :href="'/tools/' + selectedTool?.id + '/edit'" class="px-4 py-2 bg-brand text-gray-900 rounded-lg text-xs font-bold hover:bg-brand-600">Edit Tool</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

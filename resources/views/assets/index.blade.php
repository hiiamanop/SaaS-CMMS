@extends('layouts.app')
@section('title','Assets')
@section('breadcrumb')<span class="text-gray-400">/</span><span class="text-gray-700 font-medium">Assets</span>@endsection
@section('content')
<div class="space-y-5" x-data="{
    showPrintModal: false,
    printScope: '{{ request('transformer_block') ? 'block' : 'selected' }}',
    printBlock: '{{ request('transformer_block') ?: (isset($blocks[0]) ? $blocks[0] : 'T01') }}',
    printCategory: '{{ request('category', '') }}',
    printFormat: 'roll_70x40',
    selectedIds: [],
    selectAll: false,
    toggleSelectAll() {
        if (this.selectAll) {
            this.selectedIds = [{{ $assets->pluck('id')->implode(',') }}];
        } else {
            this.selectedIds = [];
        }
    },
    triggerBulkPrint() {
        let url = '{{ route('labels.print') }}?type=asset&format=' + this.printFormat;
        if (this.printScope === 'selected') {
            if (this.selectedIds.length === 0) {
                alert('Pilih setidaknya 1 aset untuk dicetak.');
                return;
            }
            url += '&ids=' + this.selectedIds.join(',');
        } else if (this.printScope === 'block') {
            url += '&transformer_block=' + this.printBlock;
        } else if (this.printScope === 'category') {
            if (this.printCategory) url += '&category=' + encodeURIComponent(this.printCategory);
        }
        window.open(url, '_blank');
        this.showPrintModal = false;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Assets</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage your equipment, modules, and machinery</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Bulk Print QR Button --}}
            <button type="button" @click="showPrintModal = true"
                    class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 shadow-sm transition-all h-[38px]" title="Cetak Stiker Label QR dalam jumlah banyak">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="6" height="6" x="3" y="3" rx="1"/><rect width="6" height="6" x="15" y="3" rx="1"/><rect width="6" height="6" x="3" y="15" rx="1"/><path d="M15 15h2v2h-2zM19 19h2v2h-2zM15 19h2v2h-2zM19 15h2v2h-2z"/></svg>
                <span>Bulk Print Stiker QR</span>
                <span x-show="selectedIds.length > 0" class="px-1.5 py-0.2 bg-brand text-gray-900 text-[11px] font-extrabold rounded-full" x-text="selectedIds.length" style="display:none"></span>
            </button>

            @if(!auth()->user()->isTechnician())
            <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-brand-dark text-white rounded-lg text-sm font-bold hover:bg-gray-700 shadow-sm transition-all h-[38px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" y2="12" x2="19" y2="12"/></svg>Add Asset
            </a>
            @endif
        </div>
    </div>
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Search assets..." class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent">
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Categories</option>
                @foreach($categories as $cat)<option value="{{ $cat }}" {{ request('category')==$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
            </select>
            @if(isset($blocks) && count($blocks) > 0)
            <select name="transformer_block" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Transformer Blocks</option>
                @foreach($blocks as $blk)<option value="{{ $blk }}" {{ request('transformer_block')==$blk?'selected':'' }}>Block {{ $blk }}</option>@endforeach
            </select>
            @endif
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
                <option value="replaced" {{ request('status')=='replaced'?'selected':'' }}>Replaced</option>
                <option value="retired" {{ request('status')=='retired'?'selected':'' }}>Retired</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-dark text-white font-bold rounded-lg text-sm font-medium hover:bg-gray-700">Filter</button>
            <a href="{{ route('assets.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Reset</a>
        </form>
    </div>
    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        @if($assets->isEmpty())
        <div class="py-16 text-center"><svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg><p class="text-gray-400 font-medium">No assets found</p></div>
        @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                <th class="w-10 px-4 py-3 text-center">
                    <input type="checkbox" @change="toggleSelectAll()" x-model="selectAll" class="rounded border-gray-300 text-brand focus:ring-brand">
                </th>
                <th class="px-4 py-3 text-left">Asset Code</th>
                <th class="px-4 py-3 text-left">Name</th>
                <th class="px-4 py-3 text-left">Category</th>
                <th class="px-4 py-3 text-left">Location</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Brand / Model</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @php $lastLocation = null; @endphp
            @foreach($assets as $asset)
            @php 
                $sc=['active'=>'bg-green-100 text-green-700','inactive'=>'bg-gray-100 text-gray-600','replaced'=>'bg-amber-100 text-amber-700','retired'=>'bg-red-100 text-red-600'];
            @endphp

            @if($lastLocation !== $asset->location)
            <tr class="bg-gray-50/80">
                <td colspan="8" class="px-5 py-2 font-bold text-brand uppercase tracking-wider text-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Location: {{ $asset->location }}
                    </div>
                </td>
            </tr>
            @php $lastLocation = $asset->location; @endphp
            @endif

            <tr class="hover:bg-opacity-90 transition-colors">
                <td class="px-4 py-3 text-center">
                    <input type="checkbox" value="{{ $asset->id }}" x-model="selectedIds" class="rounded border-gray-300 text-brand focus:ring-brand">
                </td>
                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $asset->asset_code }}</td>
                <td class="px-4 py-3">
                    <a href="{{ route('assets.show', $asset) }}" class="font-medium text-gray-900 hover:text-brand">{{ $asset->name }}</a>
                    @if($asset->transformer_block)
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-50 text-amber-700 border border-amber-200">{{ $asset->transformer_block }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-500">{{ $asset->category }}</td>
                <td class="px-4 py-3 text-gray-500 italic text-xs">{{ $asset->location }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sc[$asset->status]??'bg-gray-100 text-gray-600' }}">{{ ucwords(str_replace('_',' ',$asset->status)) }}</span></td>
                <td class="px-4 py-3 text-gray-500">{{ $asset->brand }} {{ $asset->model }}</td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('assets.show', $asset) }}" class="p-1.5 text-gray-400 hover:text-brand hover:bg-emerald-50 rounded-lg transition-colors" title="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></a>
                        @if(!auth()->user()->isTechnician())
                        <a href="{{ route('assets.edit', $asset) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                        <button @click="$dispatch('open-delete',{action:'{{ route('assets.destroy',$asset) }}',message:'Delete asset {{ addslashes($asset->name) }}? This cannot be undone.'})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">{{ $assets->links() }}</div>
        @endif
    </div>

    {{-- Bulk Print Modal --}}
    <div x-show="showPrintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 space-y-4 border border-gray-100"
             @click.outside="showPrintModal = false"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0" x-transition:enter-end="scale-100 opacity-100">
            <div class="flex items-start justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Cetak Massal Stiker QR Aset</h3>
                        <p class="text-xs text-gray-500">Hasil PDF presisi untuk printer thermal / stiker barcode</p>
                    </div>
                </div>
                <button @click="showPrintModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4 text-sm">
                {{-- Pilihan Target Cetak --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">1. Pilih Target Aset</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center gap-1 transition-all"
                               :class="printScope === 'selected' ? 'border-brand bg-brand/5 ring-2 ring-brand/30' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="selected" x-model="printScope" class="hidden">
                            <span class="text-xs font-bold text-gray-900">Pilihan Centang</span>
                            <span class="text-[10px] text-gray-500"><span x-text="selectedIds.length"></span> dipilih</span>
                        </label>
                        <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center gap-1 transition-all"
                               :class="printScope === 'block' ? 'border-brand bg-brand/5 ring-2 ring-brand/30' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="block" x-model="printScope" class="hidden">
                            <span class="text-xs font-bold text-gray-900">Per Blok Trafo</span>
                            <span class="text-[10px] text-gray-500">T01 s/d T07</span>
                        </label>
                        <label class="cursor-pointer border rounded-xl p-2.5 text-center flex flex-col items-center gap-1 transition-all"
                               :class="printScope === 'category' ? 'border-brand bg-brand/5 ring-2 ring-brand/30' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="category" x-model="printScope" class="hidden">
                            <span class="text-xs font-bold text-gray-900">Per Kategori</span>
                            <span class="text-[10px] text-gray-500">Inverter/Modul/Trafo</span>
                        </label>
                    </div>
                </div>

                {{-- Opsi Detail Target --}}
                <div x-show="printScope === 'block'" class="bg-gray-50 p-3 rounded-xl">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Blok Transformer:</label>
                    <select x-model="printBlock" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-brand">
                        @if(isset($blocks))
                        @foreach($blocks as $blk)
                        <option value="{{ $blk }}">Blok {{ $blk }} (Semua String & Inverter)</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <div x-show="printScope === 'category'" class="bg-gray-50 p-3 rounded-xl" style="display:none">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Kategori Aset:</label>
                    <select x-model="printCategory" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs font-semibold focus:ring-brand">
                        <option value="">Semua Kategori</option>
                        @if(isset($categories))
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                {{-- Format Ukuran Printer Stiker --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">2. Format Kertas / Printer Stiker</label>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-all"
                               :class="printFormat === 'roll_70x40' ? 'border-brand bg-brand/5 ring-2 ring-brand/30' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="roll_70x40" x-model="printFormat" class="mt-0.5 text-brand focus:ring-brand">
                            <div>
                                <span class="font-bold text-gray-900 text-xs block">Stiker Roll 70 x 40 mm (Rekomendasi Printer Thermal)</span>
                                <span class="text-[11px] text-gray-500">1 stiker per halaman PDF. Cocok untuk Xprinter, Zebra, TSC, dll.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-all"
                               :class="printFormat === 'roll_50x30' ? 'border-brand bg-brand/5 ring-2 ring-brand/30' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="roll_50x30" x-model="printFormat" class="mt-0.5 text-brand focus:ring-brand">
                            <div>
                                <span class="font-bold text-gray-900 text-xs block">Stiker Roll 50 x 30 mm (Label Mini / String Tag)</span>
                                <span class="text-[11px] text-gray-500">Format ringkas untuk label kabel, konektor MC4, dan junction box.</span>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-all"
                               :class="printFormat === 'sheet_a4' ? 'border-brand bg-brand/5 ring-2 ring-brand/30' : 'border-gray-200 hover:bg-gray-50'">
                            <input type="radio" value="sheet_a4" x-model="printFormat" class="mt-0.5 text-brand focus:ring-brand">
                            <div>
                                <span class="font-bold text-gray-900 text-xs block">Kertas Stiker A4 Grid (24 Stiker per Lembar A4)</span>
                                <span class="text-[11px] text-gray-500">Tata letak 3 kolom x 8 baris dengan garis potong untuk printer kantor biasa.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" @click="showPrintModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-50">Batal</button>
                <button type="button" @click="triggerBulkPrint()" class="px-5 py-2 bg-brand text-gray-900 rounded-lg text-xs font-bold hover:bg-brand-600 shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    <span>Download / Cetak PDF Stiker</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

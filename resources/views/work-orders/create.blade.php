@extends('layouts.app')
@section('title','New Work Order')
@section('breadcrumb')<span class="text-gray-400">/</span><a href="{{ route('work-orders.index') }}" class="hover:text-gray-800">Work Orders</a><span class="text-gray-400">/</span><span class="text-gray-700 font-medium">New</span>@endsection
@section('content')
@php
    $rawAssetId = $selectedAssetId ?: (request('asset_id') ?: request('from_asset'));
    if (!$rawAssetId) {
        foreach (request()->query() as $k => $v) {
            if (str_starts_with($k, 'from_asset=')) {
                $rawAssetId = substr($k, strlen('from_asset='));
                break;
            } elseif (str_starts_with($k, 'asset_id=')) {
                $rawAssetId = substr($k, strlen('asset_id='));
                break;
            }
        }
    }
    if (!$rawAssetId && ($qs = request()->getQueryString())) {
        $decoded = urldecode($qs);
        if (preg_match('/(?:from_asset|asset_id)=(\d+)/', $decoded, $m)) {
            $rawAssetId = $m[1];
        }
    }

    $targetAsset = $selectedAsset ?? ($rawAssetId ? \App\Models\Asset::find($rawAssetId) : null);
    $targetAssetId = $targetAsset?->id ?? $rawAssetId;
    $defaultTitle = request('title') ?: ($targetAsset ? "Perbaikan {$targetAsset->category} " . ($targetAsset->hierarchy_code ?: $targetAsset->asset_code) : '');
    $defaultDesc = request('description') ?: ($targetAsset ? "Perbaikan kendala pada {$targetAsset->category} {$targetAsset->asset_code}" . ($targetAsset->transformer_block ? " (Blok {$targetAsset->transformer_block})" : "") . ". Lokasi fisik: {$targetAsset->location}." : '');
@endphp

<div class="max-w-none mx-auto pb-10">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('work-orders.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg></a>
        <h1 class="text-2xl font-bold text-gray-900">New Work Order</h1>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form action="{{ route('work-orders.store') }}" method="POST" class="space-y-6" x-data="{
            isExternal: false,
            items: [],
            itemOptions: {
                spare_part: @js($spareParts->map(fn($item) => ['id' => $item->id, 'code' => $item->part_code, 'name' => $item->name, 'unit' => $item->unit, 'stock' => $item->qty_actual])->values()),
                consumable: @js($consumables->map(fn($item) => ['id' => $item->id, 'code' => $item->item_code, 'name' => $item->name, 'unit' => $item->unit, 'stock' => $item->qty_actual])->values()),
                tool: @js($tools->map(fn($item) => ['id' => $item->id, 'code' => $item->tool_code, 'name' => $item->name, 'unit' => 'unit', 'stock' => $item->qty_available])->values())
            },
            addItem() { this.items.push({ item_type: 'spare_part', item_id: '', qty_used: 1 }); },
            removeItem(index) { this.items.splice(index, 1); },
            optionsFor(type) { return this.itemOptions[type] || []; },
            resetItem(row) { row.item_id = ''; row.qty_used = 1; }
        }">
            @csrf
            @if(request('from_finding'))
            <input type="hidden" name="from_finding" value="{{ request('from_finding') }}">
            @endif

            {{-- Selected PV Module / Asset Banner --}}
            @if($targetAsset)
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start justify-between gap-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-md">
                        {{ $targetAsset->category === 'PV Module' ? 'PV' : substr($targetAsset->category, 0, 3) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded uppercase tracking-wider">Aset Terpilih dari Peta</span>
                            @if($targetAsset->transformer_block)
                            <span class="text-xs font-bold text-gray-800 bg-white px-2 py-0.5 rounded border border-emerald-200">Blok {{ $targetAsset->transformer_block }}</span>
                            @endif
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">{{ $targetAsset->name }}</h3>
                        <p class="text-xs text-gray-600 font-mono mt-0.5">
                            Kode: <strong class="text-emerald-800 font-bold">{{ $targetAsset->hierarchy_code ?: $targetAsset->asset_code }}</strong>
                            @if($targetAsset->string_number && $targetAsset->module_slot)
                            · Inverter: INV{{ str_pad($targetAsset->string_number, 2, '0', STR_PAD_LEFT) }} · String: S{{ str_pad($targetAsset->module_slot, 2, '0', STR_PAD_LEFT) }}
                            @endif
                            · Lokasi: {{ $targetAsset->location }}
                        </p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-sm shrink-0">Otomatis Terpilih</span>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left Column: Main Details --}}
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Task Title <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title', $defaultTitle) }}" required placeholder="e.g. Inverter Repair" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                             <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" name="is_external_client" value="1" id="isExternal" x-model="isExternal" class="w-4 h-4 text-brand border-gray-300 rounded focus:ring-brand">
                                <label for="isExternal" class="text-sm font-semibold text-gray-700 cursor-pointer">Pekerjaan di Lokasi Client (Bukan PLTS Internal)</label>
                             </div>
                        </div>

                        <div x-show="!isExternal" x-transition:enter="transition ease-out duration-200" class="sm:col-span-2">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-sm font-medium text-gray-700">Asset Internal <span class="text-red-500">*</span></label>
                                @if($targetAsset)
                                <span class="text-xs text-emerald-700 font-semibold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                    Terpilih otomatis dari Peta PV
                                </span>
                                @endif
                            </div>
                            <select name="asset_id" id="assetSelect" :required="!isExternal" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand @error('asset_id') border-red-400 @enderror">
                                <option value="">Pilih Aset...</option>
                                @php
                                    $currentSelected = old('asset_id', $targetAssetId);
                                @endphp
                                @if($targetAsset)
                                <optgroup label="Aset Terpilih dari Peta PV">
                                    <option value="{{ $targetAsset->id }}" selected>
                                        ★ [TERPILIH] {{ $targetAsset->name }} ({{ $targetAsset->hierarchy_code ?: $targetAsset->asset_code }}){{ $targetAsset->transformer_block ? " — Blok {$targetAsset->transformer_block}" : '' }}
                                    </option>
                                </optgroup>
                                <optgroup label="Semua Aset Lainnya">
                                @endif
                                @foreach($assets as $a)
                                @if($targetAsset && $a->id === $targetAsset->id)
                                    @continue
                                @endif
                                <option value="{{ $a->id }}" {{ (string)$currentSelected === (string)$a->id ? 'selected' : '' }}>
                                    @if($a->category === 'PV Module')
                                        [PV {{ $a->transformer_block ? "Blok {$a->transformer_block}" : '' }}] {{ $a->hierarchy_code ?: $a->asset_code }} — {{ $a->name }}
                                    @else
                                        [{{ $a->category }}{{ $a->transformer_block ? " Blok {$a->transformer_block}" : '' }}] {{ $a->asset_code }} — {{ $a->name }}
                                    @endif
                                </option>
                                @endforeach
                                @if($targetAsset)
                                </optgroup>
                                @endif
                            </select>
                            @error('asset_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="isExternal" x-transition:enter="transition ease-out duration-200" class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Client / Lokasi Luar <span class="text-red-500">*</span></label>
                            <input name="client_name" value="{{ old('client_name') }}" :required="isExternal" placeholder="Masukkan nama client..." class="w-full px-3 py-2 border border-blue-300 bg-blue-50/30 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            @error('client_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                        </div>

                        {{-- Dynamic Items Selection --}}
                        <div class="sm:col-span-2 border-t border-gray-100 pt-5">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <label class="block text-sm font-bold text-gray-800">Items Terpakai</label>
                                    <p class="text-xs text-gray-500 mt-0.5">Pilih spare part, consumable, atau tool yang digunakan dalam pekerjaan ini.</p>
                                </div>
                                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors shadow-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                                    Tambah Item
                                </button>
                            </div>

                            <template x-if="items.length === 0">
                                <div class="text-center py-4 bg-gray-50/60 rounded-xl border border-dashed border-gray-200 text-xs text-gray-400">
                                    Belum ada item ditambahkan. Klik "Tambah Item" jika pekerjaan ini memerlukan spare part, consumable, atau tool.
                                </div>
                            </template>

                            <div class="space-y-2.5">
                                <template x-for="(row, index) in items" :key="index">
                                    <div class="grid grid-cols-1 sm:grid-cols-[140px_1fr_110px_36px] gap-2.5 items-end p-3 bg-gray-50/80 rounded-xl border border-gray-200">
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Kategori</label>
                                            <select :name="`items[${index}][item_type]`" x-model="row.item_type" @change="resetItem(row)" class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-brand focus:outline-none">
                                                <option value="spare_part">Spare Part</option>
                                                <option value="consumable">Consumable</option>
                                                <option value="tool">Tool</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Nama Item</label>
                                            <select :name="`items[${index}][item_id]`" x-model="row.item_id" required class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-brand focus:outline-none">
                                                <option value="">-- Pilih Item --</option>
                                                <template x-for="option in optionsFor(row.item_type)" :key="option.id">
                                                    <option :value="option.id" x-text="`${option.code || '-'} — ${option.name} (Stok: ${option.stock} ${option.unit})`"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Jumlah</label>
                                            <input type="number" min="1" :name="`items[${index}][qty_used]`" x-model="row.qty_used" required class="w-full px-2.5 py-2 border border-gray-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-brand focus:outline-none">
                                        </div>
                                        <button type="button" @click="removeItem(index)" class="w-9 h-9 flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Hapus item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign To (Multiple)</label>
                            <select name="assigned_to[]" id="assigneeSelect" multiple placeholder="Select technicians..." class="w-full">
                                @foreach($technicians as $t)<option value="{{ $t->id }}" {{ collect(old('assigned_to'))->contains($t->id)?'selected':'' }}>{{ $t->name }}</option>@endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Priority <span class="text-red-500">*</span></label>
                            <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                @foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" {{ old('priority','medium')==$p?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Work Type <span class="text-red-500">*</span></label>
                            <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="corrective" {{ old('type') == 'corrective' ? 'selected' : '' }}>Corrective</option>
                                <option value="preventive" {{ old('type') == 'preventive' ? 'selected' : '' }}>Preventive</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                        <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand resize-none">{{ old('description', request('description', $defaultDesc)) }}</textarea>
                    </div>
                </div>

                {{-- Right Column: Schedule & Tasks --}}
                <div class="space-y-6">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h4 class="text-xs font-bold text-gray-400 uppercase mb-4 tracking-wider">Schedule Info</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Due Date <span class="text-red-500">*</span></label>
                                <input name="due_date" type="date" value="{{ old('due_date', request('due_date')) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            </div>
                            <div class="pt-2">
                                <div class="flex items-start gap-3 p-3 bg-white border border-gray-200 rounded-lg">
                                    <input type="hidden" name="shutdown_required" value="0">
                                    <input type="checkbox" name="shutdown_required" value="1" id="shutdownRequired" {{ old('shutdown_required') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                    <div>
                                        <label for="shutdownRequired" class="text-sm font-medium text-gray-800 cursor-pointer">Shutdown Required</label>
                                        <p class="text-[10px] text-gray-500 mt-0.5 leading-tight">Downtime dihitung otomatis saat WO dikerjakan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6 border-t border-gray-100">
                <button type="submit" class="px-6 py-2.5 bg-brand text-gray-900 rounded-lg text-sm font-bold hover:bg-brand-600 shadow-sm transition-all">Submit Order</button>
                <a href="{{ route('work-orders.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-opacity-90">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        new TomSelect('#assigneeSelect', {
            plugins: ['remove_button'],
            maxItems: null,
        });
    });
</script>
@endpush

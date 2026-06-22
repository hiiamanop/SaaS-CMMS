{{-- PV Map Modal Component --}}
<div class="fixed inset-0 z-50 overflow-y-auto hidden" id="pvMapModal" x-show="showPvMapModal" @keydown.escape="showPvMapModal = false">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showPvMapModal = false"></div>

        <!-- Modal container -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Header -->
            <div class="bg-white px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Peta PV Module</h3>
                    <p class="text-sm text-gray-600 mt-1" x-text="'Lokasi: ' + locationName"></p>
                </div>
                <button @click="showPvMapModal = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Content -->
            <div class="bg-gray-50 px-6 py-6">
                <!-- Stage: Import or View -->
                <div x-show="pvMapStage === 'import'">
                    <div class="bg-white rounded-lg border border-gray-200 p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Upload File CSV</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-brand transition-colors"
                                 @drop="handleFileUpload($event)"
                                 @dragover.prevent
                                 @dragleave.prevent>
                                <input type="file" id="csvFile" class="hidden" accept=".csv,.txt" @change="handleFileUpload($event)">
                                <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <p class="text-sm text-gray-600">Klik atau drag file CSV di sini</p>
                                <p class="text-xs text-gray-500 mt-1">Format: transformer_block, string_number, module_slot, visual_row, visual_col</p>
                                <button @click="$refs.csvFile.click()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">Pilih File</button>
                            </div>
                        </div>

                        <div x-show="csvPreview" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm font-medium text-blue-900">Preview: <span x-text="csvPreview.transformer_block"></span></p>
                            <p class="text-xs text-blue-700 mt-1"><span x-text="csvPreview.modules?.length || 0"></span> modules ditemukan</p>
                        </div>
                    </div>
                </div>

                <!-- Stage: Edit/Preview -->
                <div x-show="pvMapStage === 'edit'">
                    <div class="space-y-4">
                        <!-- Grid Visualization -->
                        <div class="bg-white rounded-lg border border-gray-200 p-4 overflow-x-auto">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Grid Layout: <span x-text="editingMap?.transformer_block"></span></h4>
                            <div class="grid gap-1" style="grid-template-columns: repeat(24, minmax(0, 1fr)); grid-auto-rows: minmax(0, 1fr);"
                                 x-data="{ dragging: null, dragOffset: {x: 0, y: 0} }">
                                <template x-for="module in editingModules" :key="module.asset_code">
                                    <div class="aspect-square bg-blue-100 border border-blue-300 rounded-sm p-1 cursor-move hover:bg-blue-200 transition-colors text-center flex flex-col items-center justify-center select-none"
                                         :style="`grid-column: ${module.visual_col}; grid-row: ${module.visual_row};`"
                                         @mousedown="dragging = module; dragOffset = {x: $event.offsetX, y: $event.offsetY}"
                                         @mouseup="dragging = null"
                                         @mousemove="if(dragging) { /* update position */ }"
                                         @click="selectedModule = module">
                                        <span class="text-xs font-bold text-blue-900 truncate" :title="module.asset_code" x-text="module.asset_code.split('-')[1]"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Module Editor -->
                        <div x-show="selectedModule" class="bg-white rounded-lg border border-gray-200 p-4">
                            <h4 class="text-sm font-semibold text-gray-900 mb-3">Edit Posisi</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Module</label>
                                    <input type="text" :value="selectedModule?.asset_code" disabled class="w-full px-2 py-1 border border-gray-300 rounded text-xs bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                    <select @change="selectedModule.status = $event.target.value" :value="selectedModule?.status" class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak Aktif</option>
                                        <option value="replaced">Diganti</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Visual Row</label>
                                    <input type="number" min="1" max="24" @change="selectedModule.visual_row = parseInt($event.target.value)" :value="selectedModule?.visual_row" class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Visual Col</label>
                                    <input type="number" min="1" max="24" @change="selectedModule.visual_col = parseInt($event.target.value)" :value="selectedModule?.visual_col" class="w-full px-2 py-1 border border-gray-300 rounded text-xs">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-white px-6 py-4 border-t border-gray-200 flex justify-end gap-2">
                <button @click="showPvMapModal = false" class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Batal
                </button>
                <button @click="savePvMap()" x-show="pvMapStage === 'edit'" :disabled="loadingPvMap" class="px-4 py-2 text-sm text-white bg-brand-dark rounded-lg hover:bg-gray-700 disabled:opacity-50">
                    <span x-show="!loadingPvMap">Simpan Peta</span>
                    <span x-show="loadingPvMap">Menyimpan...</span>
                </button>
                <button @click="uploadCsv()" x-show="pvMapStage === 'import'" :disabled="!csvPreview || loadingPvMap" class="px-4 py-2 text-sm text-white bg-brand-dark rounded-lg hover:bg-gray-700 disabled:opacity-50">
                    <span x-show="!loadingPvMap">Lanjutkan Preview</span>
                    <span x-show="loadingPvMap">Loading...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof Alpine !== 'undefined') {
    Alpine.store('pvMap', {
        showPvMapModal: false,
        pvMapStage: 'import',
        locationId: null,
        locationName: '',
        csvPreview: null,
        editingMap: null,
        editingModules: [],
        selectedModule: null,
        loadingPvMap: false,

        async handleFileUpload(event) {
            const files = event.dataTransfer?.files || event.target.files;
            if (!files[0]) return;

            const formData = new FormData();
            formData.append('file', files[0]);

            try {
                const response = await fetch(`/settings/locations/${this.locationId}/pv-maps/upload`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error);

                this.csvPreview = {
                    transformer_block: data.transformer_block,
                    modules: data.modules
                };
            } catch (error) {
                alert('Error: ' + error.message);
            }
        },

        async uploadCsv() {
            if (!this.csvPreview) return;

            this.loadingPvMap = true;
            this.editingModules = this.csvPreview.modules;
            this.editingMap = { transformer_block: this.csvPreview.transformer_block };
            this.pvMapStage = 'edit';
            this.loadingPvMap = false;
        },

        async savePvMap() {
            if (!this.editingModules.length) return;

            this.loadingPvMap = true;
            try {
                const response = await fetch(`/settings/locations/${this.locationId}/pv-maps/save`, {
                    method: 'POST',
                    body: JSON.stringify({
                        transformer_block: this.editingMap.transformer_block,
                        modules: this.editingModules
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Error saving map');

                alert('Peta berhasil disimpan!');
                this.showPvMapModal = false;
                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                this.loadingPvMap = false;
            }
        },

    });

    // Listen for open event
    document.addEventListener('open-pv-map', (e) => {
        Alpine.store('pvMap').locationId = e.detail.location_id;
        Alpine.store('pvMap').locationName = e.detail.location_name;
        Alpine.store('pvMap').pvMapStage = 'import';
        Alpine.store('pvMap').csvPreview = null;
        Alpine.store('pvMap').selectedModule = null;
        Alpine.store('pvMap').showPvMapModal = true;
    });
}
</script>

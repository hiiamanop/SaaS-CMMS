@extends('layouts.app')
@section('title', 'Personal Notes')

@push('styles')
<style>
    .notes-list::-webkit-scrollbar { width: 6px; }
    .notes-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .note-content textarea:focus { outline: none; border: none; ring: 0; }
    .note-paper {
        background-color: #fff;
        background-image: 
            linear-gradient(90deg, transparent 79px, #abced4 79px, #abced4 81px, transparent 81px),
            linear-gradient(#eee .1em, transparent .1em);
        background-size: 100% 1.5em;
    }
</style>
@endpush

@section('breadcrumb')
<span class="text-gray-400">/</span>
<span class="text-gray-700 font-medium">Personal Notes</span>
@endsection

@section('content')
<div class="h-[calc(100vh-140px)] flex bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-xl mb-10"
     x-data="notesApp()">
    
    {{-- Middle Column: Notes List --}}
    <div class="w-80 border-r border-gray-200 flex flex-col bg-gray-50/50">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-white/80 backdrop-blur-md sticky top-0 z-10">
            <h2 class="text-xl font-bold text-gray-900">Notes</h2>
            <button @click="createNewNote()" 
                    class="p-2 text-brand hover:bg-brand-50 rounded-lg transition-colors" title="New Note">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7 M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto notes-list divide-y divide-gray-100">
            <template x-for="note in notes" :key="note.id">
                <div @click="selectNote(note)"
                     :class="selectedId === note.id ? 'bg-brand/10 border-l-4 border-brand' : 'hover:bg-gray-100 border-l-4 border-transparent'"
                     class="p-4 cursor-pointer transition-all duration-200 relative group">
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-bold text-sm text-gray-900 truncate pr-4" x-text="note.title || 'Untitled Note'"></h3>
                        <span class="text-[10px] text-gray-400 font-medium whitespace-nowrap" x-text="formatDate(note.report_date)"></span>
                    </div>
                    <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed" x-text="note.content || 'No additional text'"></p>
                    
                    <div x-show="note.photos && note.photos.length > 0" class="mt-2 flex gap-1 overflow-hidden">
                        <template x-for="p in note.photos.slice(0,3)" :key="p.id">
                            <div class="w-6 h-6 rounded bg-gray-200 overflow-hidden flex-shrink-0">
                                <img :src="p.url || '/storage/'+p.file_path" class="w-full h-full object-cover opacity-60">
                            </div>
                        </template>
                        <span x-show="note.photos.length > 3" class="text-[9px] text-gray-400 self-center">+<span x-text="note.photos.length - 3"></span></span>
                    </div>
                </div>
            </template>
            <div x-show="notes.length === 0" class="p-8 text-center text-gray-400 italic text-sm">
                No notes yet. Click the icon to create your first note.
            </div>
        </div>
    </div>

    {{-- Right Column: Editor --}}
    <div class="flex-1 flex flex-col bg-white overflow-hidden relative">
        <template x-if="selectedId || isCreating">
            <div class="flex flex-col h-full">
                {{-- Editor Toolbar --}}
                <div class="h-14 border-b border-gray-100 px-6 flex items-center justify-between bg-white/50 backdrop-blur-sm shadow-sm z-10">
                    <div class="flex items-center gap-4">
                        <input type="date" x-model="activeReport.report_date" 
                               class="text-sm font-bold text-gray-500 border-none focus:ring-0 bg-transparent p-0 w-36"
                               @change="autoSave()">
                        <div class="h-4 w-px bg-gray-200"></div>
                        <span x-show="isSaving" class="text-[10px] font-bold text-brand uppercase tracking-widest animate-pulse">Saving...</span>
                        <span x-show="lastSaved" x-text="'Saved at ' + lastSaved" class="text-[10px] font-medium text-gray-400 uppercase tracking-widest"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="$refs.photoInput.click()" 
                                class="p-2 text-gray-500 hover:text-brand hover:bg-brand-50 rounded-lg transition-colors" title="Attach Photo">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </button>
                        <input type="file" x-ref="photoInput" class="hidden" accept="image/*" @change="uploadPhoto($event)">
                        
                        <div class="h-4 w-px bg-gray-200"></div>
                        <button @click="autoSave()" class="text-xs font-bold text-brand hover:underline">Sync Now</button>
                    </div>
                </div>

                {{-- Editor Content --}}
                <div class="flex-1 overflow-y-auto p-1 zero-padding-mobile">
                    <div class="max-w-3xl mx-auto py-10 px-6 min-h-full flex flex-col">
                        <input type="text" x-model="activeReport.title" 
                               placeholder="Note Title..."
                               class="text-3xl font-black text-gray-900 border-none focus:ring-0 w-full mb-6 placeholder-gray-200"
                               @input="debounceSave()">
                        
                        {{-- Photo Gallery --}}
                        <div x-show="activeReport.photos && activeReport.photos.length > 0" class="mb-8 grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <template x-for="p in activeReport.photos" :key="p.id">
                                <div class="relative group aspect-video rounded-xl overflow-hidden bg-gray-100 border border-gray-200 shadow-sm transition-all hover:shadow-md">
                                    <img :src="p.url || '/storage/'+p.file_path" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-start justify-end p-2">
                                        <button @click="deletePhoto(p.id)" class="p-1.5 bg-white/20 backdrop-blur-md rounded-lg text-white hover:bg-red-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <textarea x-model="activeReport.content" 
                                  placeholder="Write your note here..."
                                  class="flex-1 text-base text-gray-700 border-none focus:ring-0 w-full resize-none bg-transparent leading-relaxed"
                                  @input="debounceSave()"></textarea>
                    </div>

                    {{-- Delete Button - Bottom Right --}}
                    <div class="absolute bottom-6 right-6">
                        <button @click="deleteNote(activeReport.id)" 
                                x-show="activeReport.id"
                                class="p-3 bg-red-50 text-red-500 hover:bg-red-100 rounded-full transition-all shadow-sm"
                                title="Delete Note">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
        <template x-if="!selectedId && !isCreating">
            <div class="flex-1 flex flex-col items-center justify-center text-gray-300 p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7 M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-400">Select a note to read or edit</h3>
                <p class="text-sm max-w-xs mt-2">All your personal notes are organized here. Your changes are saved automatically.</p>
                <button @click="createNewNote()" class="mt-6 px-6 py-2 bg-brand text-gray-900 font-bold rounded-xl shadow-sm hover:bg-brand-600 transition-all">
                    Create New Note
                </button>
            </div>
        </template>
    </div>
    
    {{-- Hidden form for deletion --}}
    <form id="delete-note-form" method="POST" class="hidden">
        @csrf @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script>
function notesApp() {
    return {
        notes: @json($reports),
        selectedId: {{ $selectedReport ? $selectedReport->id : 'null' }},
        isCreating: false,
        isSaving: false,
        lastSaved: '',
        activeReport: {
            id: null,
            report_date: '{{ date("Y-m-d") }}',
            title: '',
            content: '',
            photos: []
        },
        saveTimeout: null,

        init() {
            if (this.selectedId) {
                const note = this.notes.find(n => n.id === this.selectedId);
                if (note) this.selectNote(note);
            }
        },

        selectNote(note) {
            this.selectedId = note.id;
            this.isCreating = false;
            this.activeReport = { 
                ...note, 
                report_date: note.report_date.split('T')[0],
                photos: note.photos || []
            };
        },

        createNewNote() {
            const today = new Date().toISOString().split('T')[0];
            
            this.selectedId = null;
            this.isCreating = true;
            this.activeReport = {
                id: null,
                report_date: today,
                title: '',
                content: '',
                photos: []
            };
            this.lastSaved = '';
        },

        debounceSave() {
            this.isSaving = true;
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => this.autoSave(), 1500);
        },

        async autoSave() {
            if (!this.activeReport.report_date) return;
            
            this.isSaving = true;
            try {
                const response = await fetch('{{ route("daily-reports.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.activeReport)
                });
                
                const data = await response.json();
                if (data.success) {
                    this.activeReport.id = data.report.id;
                    this.selectedId = data.report.id;
                    this.lastSaved = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    // Update notes list
                    const idx = this.notes.findIndex(n => n.id === data.report.id);
                    if (idx !== -1) {
                        this.notes[idx] = data.report;
                    } else {
                        this.notes.unshift(data.report);
                    }
                    this.notes.sort((a,b) => new Date(b.report_date) - new Date(a.report_date));
                }
            } catch (e) {
                console.error('Autosave failed:', e);
            } finally {
                this.isSaving = false;
            }
        },

        async uploadPhoto(e) {
            if (!this.activeReport.id) {
                // Save first if new note
                await this.autoSave();
            }

            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                this.isSaving = true;
                const response = await fetch(`/daily-reports/${this.activeReport.id}/upload-photo`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    this.activeReport.photos.push(data.photo);
                    // Update main list
                    const note = this.notes.find(n => n.id === this.activeReport.id);
                    if (note) note.photos = this.activeReport.photos;
                }
            } catch (e) {
                alert('Upload failed');
            } finally {
                this.isSaving = false;
                e.target.value = '';
            }
        },

        async deletePhoto(photoId) {
            if (!confirm('Delete this photo?')) return;

            try {
                const response = await fetch(`/daily-reports/${this.activeReport.id}/photos/${photoId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await response.json();
                if (data.success) {
                    this.activeReport.photos = this.activeReport.photos.filter(p => p.id !== photoId);
                    const note = this.notes.find(n => n.id === this.activeReport.id);
                    if (note) note.photos = this.activeReport.photos;
                }
            } catch (e) {
                alert('Delete failed');
            }
        },

        async deleteNote(id) {
            if (!confirm('Delete this note?')) return;
            
            const form = document.getElementById('delete-note-form');
            form.action = `/daily-reports/${id}`;
            form.submit();
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            const today = new Date();
            if (date.toDateString() === today.toDateString()) return 'Today';
            
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
            
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }
    }
}
</script>
@endpush

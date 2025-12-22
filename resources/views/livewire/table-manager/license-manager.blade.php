{{-- Rimosso overflow-y-auto dal contenitore principale e aggiunta h-screen --}}
<div class="h-screen w-full flex flex-col bg-slate-100 overflow-hidden">
    
    <div class="max-w-6xl mx-auto w-full flex flex-col h-full p-4 sm:p-8">
        
        {{-- HEADER (FISSO) --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 shrink-0">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" 
                   class="p-3 bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 rounded-2xl shadow-sm transition-all active:scale-95 group">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none">Configurazione Turno</h1>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Imposta l'ordine di servizio giornaliero</p>
                </div>
            </div>

            <button wire:click="resetTable" 
                class="flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-xl font-black uppercase text-xs shadow-lg active:scale-95 transition-all hover:bg-rose-600 group">
                <svg class="w-4 h-4 transition-transform group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Svuota</span>
            </button>
        </div>

        {{-- AREA CONTENUTO (FLESSIBILE) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 flex-1 min-h-0 mb-6">
            
            {{-- COLONNA DISPONIBILI --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 flex flex-col overflow-hidden">
                <div class="p-8 pb-4 shrink-0">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Licenze Disponibili</h2>
                </div>
                
                {{-- AREA SCROLLABILE INDIPENDENTE --}}
                <div class="p-8 pt-0 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        @foreach($availableUsers as $user)
                            <button 
                                wire:key="avail-{{ $user['id'] }}"
                                wire:click="selectUser({{ $user['id'] }})" 
                                class="h-14 rounded-2xl border-2 bg-slate-50 text-slate-700 border-slate-100 font-black text-lg hover:border-indigo-500 hover:bg-indigo-50 transition-all active:scale-95">
                                {{ $user['license'] ?? $user['license_number'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- COLONNA ORDINE SERVIZIO --}}
            <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl text-white border border-slate-800 flex flex-col overflow-hidden">
                <div class="p-8 pb-4 shrink-0">
                    <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Ordine di Servizio</h2>
                </div>
                
                {{-- AREA SCROLLABILE INDIPENDENTE --}}
                <div class="p-8 pt-0 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="space-y-3">
                        @foreach($selectedUsers as $index => $item)
                            <div wire:key="selected-row-{{ $item['id'] }}"
                                class="flex items-center bg-white/5 p-4 rounded-2xl border border-white/10 transition-all hover:bg-white/10">
                                
                                <span class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black text-lg mr-4 shadow-lg">
                                    {{ $item['user']['license'] }}
                                </span>
                                
                                <div class="flex-1">
                                    <span class="block text-[9px] font-black text-indigo-400 uppercase leading-none">Posizione</span>
                                    <span class="text-xl font-black italic">{{ $index + 1 }}°</span>
                                </div>

                                <div class="flex gap-2">
                                    <button wire:click="moveUp({{ $item['id'] }})" @if($loop->first) disabled @endif
                                            class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-indigo-500 rounded-lg transition-all {{ $loop->first ? 'opacity-10 cursor-not-allowed' : '' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button wire:click="moveDown({{ $item['id'] }})" @if($loop->last) disabled @endif
                                            class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-indigo-500 rounded-lg transition-all {{ $loop->last ? 'opacity-10 cursor-not-allowed' : '' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button wire:click="removeUser({{ $item['id'] }})" class="p-2 text-rose-400 hover:bg-rose-500/20 rounded-lg transition-colors font-bold">✕</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- FOOTER FISSO (CONFIRMA) --}}
                @if(count($selectedUsers) > 0)
                    <div class="p-8 pt-4 bg-slate-900 border-t border-white/5 shrink-0">
                        <button wire:click="confirm" class="w-full py-5 bg-emerald-500 text-slate-900 rounded-[1.5rem] font-black text-xl uppercase tracking-tighter shadow-xl hover:bg-emerald-400 transition-all active:scale-95">
                            Inizia Lavoro →
                        </button>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@push('custom_css')
    <style>
        /* CSS per nascondere o rendere minimali le scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
        .bg-slate-900 .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }
    </style>
@endpush
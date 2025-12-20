<div class="h-full flex flex-col p-8 overflow-y-auto bg-slate-100">
    <div class="max-w-4xl mx-auto w-full">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Configurazione Turno</h1>
            <button wire:click="resetTable" class="px-6 py-2 bg-rose-500 text-white rounded-xl font-black uppercase text-xs shadow-lg active:scale-95 transition-all">
                Svuota
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- COLONNA DISPONIBILI --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-200">
                <h2 class="text-[10px] font-black text-slate-400 uppercase mb-6 tracking-widest">Licenze Disponibili</h2>
                <div class="grid grid-cols-4 gap-3">
                    @foreach($availableUsers as $user)
                        {{-- WIRE:KEY È FONDAMENTALE PER EVITARE IL BLOCCO --}}
                        <button 
                            wire:key="avail-{{ $user['id'] }}"
                            wire:click="selectUser({{ $user['id'] }})" 
                            class="h-14 rounded-2xl border-2 bg-slate-50 text-slate-700 border-slate-100 font-black text-lg hover:border-indigo-500 hover:bg-indigo-50 transition-all active:scale-95">
                            {{ $user['license'] ?? $user['license_number'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- COLONNA ORDINE SERVIZIO --}}
            <div class="bg-slate-900 rounded-[2.5rem] p-8 shadow-2xl text-white relative overflow-hidden">
                <h2 class="text-[10px] font-black text-slate-500 uppercase mb-6 tracking-widest">Ordine di Servizio</h2>
                
                <div class="space-y-3">
                    @foreach($selectedUsers as $index => $item)
                        {{-- USA ID DIVERSO PER LA LISTA SELEZIONATI --}}
                        <div 
                            wire:key="selected-row-{{ $item['id'] }}"
                            class="flex items-center bg-white/5 p-4 rounded-2xl border border-white/10">
                            
                            <span class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black text-lg mr-4 shadow-lg">
                                {{ $item['user']['license'] }}
                            </span>
                            
                            <div class="flex-1">
                                <span class="block text-[9px] font-black text-indigo-400 uppercase leading-none">Posizione</span>
                                <span class="text-xl font-black italic">{{ $index + 1 }}°</span>
                            </div>

                            <div class="flex gap-2">
                                {{-- Sposta Su --}}
                                <button wire:click="moveUp({{ $item['id'] }})" 
                                        @if($loop->first) disabled @endif
                                        class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-indigo-500 rounded-lg transition-all {{ $loop->first ? 'opacity-20 cursor-not-allowed' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </button>

                                {{-- Sposta Giù --}}
                                <button wire:click="moveDown({{ $item['id'] }})" 
                                        @if($loop->last) disabled @endif
                                        class="w-8 h-8 flex items-center justify-center bg-white/5 hover:bg-indigo-500 rounded-lg transition-all {{ $loop->last ? 'opacity-20 cursor-not-allowed' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <button wire:click="removeUser({{ $item['id'] }})" class="p-2 text-rose-400 hover:bg-rose-500 rounded-lg transition-colors">✕</button>
                            </div>

                        </div>
                    @endforeach
                </div>

                @if(count($selectedUsers) > 0)
                    <div class="mt-8 pt-6 border-t border-white/5">
                        <button wire:click="confirm" class="w-full py-5 bg-emerald-500 text-slate-900 rounded-[1.5rem] font-black text-xl uppercase tracking-tighter shadow-xl hover:bg-emerald-400 transition-all active:scale-95">
                            Inizia Lavoro →
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
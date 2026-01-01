{{-- Contenitore Principale: h-screen e overflow-hidden --}}
<div class="h-screen w-full flex flex-col bg-slate-100 overflow-hidden" x-data>

    <div class="max-w-6xl mx-auto w-full flex flex-col h-full p-4 sm:p-8">

        {{-- HEADER (FISSO) --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4 shrink-0">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}"
                   class="p-3 bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 rounded-2xl shadow-sm transition-all active:scale-95 group">
                    <x-icon name="home" class="w-6 h-6" />
                </a>
                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none text-balance">Configurazione Turno</h1>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">Gestione ordine di servizio del {{ today()->format('d/m/Y') }}</p>
                </div>
            </div>

            <button wire:click="resetTable"
                class="flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-xl font-black uppercase text-xs shadow-lg active:scale-95 transition-all hover:bg-rose-600 group">
                <x-icon name="trash" class="w-4 h-4 transition-transform group-hover:rotate-12" />
                <span>Svuota Tabella</span>
            </button>
        </div>

        {{-- AREA CONTENUTO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 flex-1 min-h-0 mb-6">

            {{-- COLONNA DISPONIBILI (Usa Computed Property availableUsers) --}}
            <div class="bg-white rounded-[2.5rem] shadow-xl border border-slate-200 flex flex-col overflow-hidden">
                <div class="p-8 pb-4 shrink-0">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Licenze Disponibili</h2>
                        <span class="px-2 py-1 bg-slate-100 rounded-md text-[9px] font-black text-slate-500">{{ $this->availableUsers->count() }} LIBERE</span>
                    </div>

                    {{-- CAMPO RICERCA --}}
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                            <x-icon name="search" class="w-4 h-4 group-focus-within:text-indigo-500 transition-colors" />
                        </div>
                        <input type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="CERCA LICENZA O NOME..."
                            class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase text-[10px] outline-none transition-all">
                    </div>
                </div>

                {{-- AREA SCROLLABILE LICENZE --}}
                <div class="p-8 pt-0 overflow-y-auto flex-1 custom-scrollbar">
                    @if($this->availableUsers->isNotEmpty())
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                            @foreach($this->availableUsers as $user)
                                <button
                                    wire:key="avail-{{ $user->id }}"
                                    wire:loading.attr="disabled"
                                    wire:click="selectUser({{ $user->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="selectUser({{ $user->id }})"
                                    class="h-16 rounded-2xl border-2 bg-slate-50 text-slate-700 border-slate-100 hover:border-indigo-500 hover:bg-indigo-50 hover:text-indigo-600 transition-all duration-200 active:scale-90 shadow-sm flex flex-col items-center justify-center leading-none disabled:opacity-50 disabled:cursor-not-allowed group">
                                    <div wire:loading.remove wire:target="selectUser({{ $user->id }})" class="flex flex-col items-center">
                                        {{-- Numero Licenza --}}
                                        <span class="font-black text-lg tracking-tighter">{{ $user->license_number }}</span>
                                        
                                        {{-- Nome e Cognome (Sotto) --}}
                                        <span class="text-[7px] font-black uppercase tracking-widest text-slate-400 group-hover:text-indigo-400 mt-0.5 truncate max-w-[60px]">
                                            {{ $user->name }}
                                        </span>
                                    </div>

                                    <span wire:loading wire:target="selectUser({{ $user->id }})">
                                        @livewire('ui.small-spinner')
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-center opacity-30 p-8">
                            <x-icon name="check" class="w-12 h-12 mb-2 text-indigo-500" />
                            <p class="text-[9px] font-black uppercase tracking-widest leading-relaxed">
                                {{ $search ? 'Nessun risultato trovato' : 'Tutte le licenze sono state assegnate' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- COLONNA ORDINE SERVIZIO (Usa Computed Property selectedUsers) --}}
            <div class="bg-slate-900 rounded-[2.5rem] shadow-2xl text-white border border-slate-800 flex flex-col overflow-hidden">
                <div class="p-8 pb-4 shrink-0 flex justify-between items-center">
                    <h2 class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Ordine di Servizio</h2>
                    <span class="px-2 py-1 bg-white/5 rounded-md text-[9px] font-black text-indigo-400">{{ $this->selectedUsers->count() }} IN TURNO</span>
                </div>

                <div class="p-8 pt-0 overflow-y-auto flex-1 custom-scrollbar">
                    <div class="space-y-3">
                        @forelse($this->selectedUsers as $index => $item)
                            <div wire:key="selected-row-{{ $item->id }}"
                                class="flex items-center bg-white/5 p-4 rounded-2xl border border-white/10 transition-all hover:bg-white/10 group">

                                {{-- Badge Licenza --}}
                                <span class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-black text-lg mr-4 shadow-lg shadow-indigo-500/20 group-hover:scale-110 transition-transform">
                                    {{ $item->user->license_number }}
                                </span>

                                <div class="flex-1 min-w-0">
                                    <span class="block text-[8px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1 truncate">
                                        {{ $item->user->name }} {{ $item->user->surname }}
                                    </span>
                                    <span class="text-xl font-black italic tracking-tighter">{{ $index + 1 }}° <span class="text-[10px] text-slate-500 not-italic ml-1 font-bold">POSTO</span></span>
                                </div>

                                {{-- Controlli --}}
                                <div class="flex items-center gap-1">
                                    <div class="flex flex-col gap-1 mr-2">
                                        <button wire:click="moveUp({{ $item->id }})"
                                                @if($loop->first) disabled @endif
                                                class="w-8 h-7 flex items-center justify-center bg-white/5 hover:bg-indigo-500 rounded-lg transition-all {{ $loop->first ? 'opacity-0 pointer-events-none' : '' }}">
                                            <x-icon name="move_up" class="w-3 h-3" />
                                        </button>
                                        <button wire:click="moveDown({{ $item->id }})"
                                                @if($loop->last) disabled @endif
                                                class="w-8 h-7 flex items-center justify-center bg-white/5 hover:bg-indigo-500 rounded-lg transition-all {{ $loop->last ? 'opacity-0 pointer-events-none' : '' }}">
                                            <x-icon name="move_down" class="w-3 h-3" />
                                        </button>
                                    </div>
                                    <button wire:click="removeUser({{ $item->id }})"
                                            class="w-10 h-10 flex items-center justify-center text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-all">
                                        <x-icon name="close" class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="h-64 flex flex-col items-center justify-center text-center opacity-20 border-2 border-dashed border-white/10 rounded-[2rem]">
                                <x-icon name="plus" class="w-12 h-12 mb-4" />
                                <p class="font-black uppercase text-[10px] tracking-[0.3em]">Nessuna licenza<br>in turno</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="p-8 pt-4 bg-slate-900 border-t border-white/5 shrink-0">
                    <button wire:click="confirm"
                        @if($this->selectedUsers->isEmpty()) disabled @endif
                        class="w-full py-5 bg-emerald-500 text-slate-900 rounded-[1.5rem] font-black text-xl uppercase tracking-tighter shadow-xl hover:bg-emerald-400 transition-all active:scale-95 disabled:opacity-20 disabled:grayscale disabled:pointer-events-none">
                        Inizia Lavoro →
                    </button>
                </div>
            </div>

        </div>
    </div>
    <div wire:loading.delay.longer>
        <livewire:ui.spinner/>
    </div>
</div>

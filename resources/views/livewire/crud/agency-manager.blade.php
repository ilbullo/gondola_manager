<div class="h-full flex flex-col overflow-hidden">
    {{-- HEADER FISSO (Titolo e Barra Ricerca) --}}
    <div class="shrink-0 p-4 sm:p-8 bg-slate-100">
        <div class="max-w-6xl mx-auto">
            {{-- TITOLO --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none">Gestione Agenzie</h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Configurazione anagrafica partner</p>
                </div>
            </div>

            {{-- BARRA AZIONI & RICERCA --}}
            <div class="bg-white rounded-[2.5rem] p-4 sm:p-6 shadow-xl border border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="relative flex-1 max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <x-icon name="search" class="w-5 h-5 group-focus-within:text-indigo-500 transition-colors" />
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="CERCA PER NOME O CODICE..."
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 placeholder-slate-300 transition-all uppercase text-xs outline-none">
                    </div>

                    <div class="flex items-center gap-3">
                        <button wire:click="toggleShowDeleted"
                            class="px-5 py-4 {{ $showDeleted ? 'bg-rose-100 text-rose-600 border border-rose-200' : 'bg-slate-100 text-slate-500 border border-transparent' }} font-black uppercase text-[10px] rounded-2xl transition-all active:scale-95">
                            {{ $showDeleted ? 'Nascondi Eliminate' : 'Mostra Eliminate' }}
                        </button>

                        <button wire:click="toggleCreateForm"
                            class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black uppercase text-[10px] rounded-2xl transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 active:scale-95">
                            <x-icon name="plus" class="w-4 h-4" />
                            Nuova Agenzia
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CORPO TABELLA (SCREVEVOLE INDIPENDENTE) --}}
    <div class="flex-1 overflow-hidden p-4 sm:p-8 pt-0 bg-slate-100">
        <div class="max-w-6xl mx-auto h-full flex flex-col bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden">

            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-900">
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">Nome Partner</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">Codice</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($agencies as $agency)
                            <tr class="{{ $agency->trashed() ? 'bg-rose-50/50' : 'hover:bg-slate-50/50' }} transition-colors">
                                <td class="px-8 py-5">
    <div class="flex items-center gap-4">
        {{-- QUADRATO COLORE AGENZIA --}}
        <div class="relative group">
            <div class="w-10 h-10 rounded-xl shadow-sm border border-slate-200 flex-shrink-0 transition-transform group-hover:scale-110"
                 style="background-color: {{ $agency->colour }};"
                 title="Colore: {{ $agency->colour }}">

                {{-- Opzionale: Iniziali all'interno con contrasto dinamico --}}
                <div class="flex items-center justify-center h-full font-black text-[10px] uppercase italic"
                     style="color: {{ $agency->contrast_text }};">
                    {{ substr($agency->name, 0, 2) }}
                </div>
            </div>

            {{-- Indicatore per agenzie eliminate --}}
            @if($agency->trashed())
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 border-2 border-white rounded-full"></div>
            @endif
        </div>

        <div class="flex flex-col">
            <span class="text-sm font-black {{ $agency->trashed() ? 'text-rose-400' : 'text-slate-700' }} uppercase italic leading-none">
                {{ $agency->name }}
            </span>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1">
                HEX: {{ $agency->colour }}
            </span>
        </div>
    </div>
</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 {{ $agency->trashed() ? 'bg-rose-100 text-rose-400' : 'bg-slate-100 text-slate-500' }} font-mono font-bold rounded-lg text-xs">{{ $agency->code }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex justify-end gap-2">
                                        @if ($agency->trashed())
                                            <button wire:click="restore({{ $agency->id }})"
                                                class="px-4 py-2 bg-emerald-500 text-white text-[9px] font-black uppercase rounded-xl hover:bg-emerald-400 transition-all shadow-md active:scale-95">
                                                Ripristina
                                            </button>
                                        @else
                                            <button wire:click="edit({{ $agency->id }})"
                                                class="px-4 py-2 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase rounded-xl hover:bg-indigo-600 hover:text-white transition-all active:scale-95">
                                                Modifica
                                            </button>
                                            <button wire:click="confirmDelete({{ $agency->id }})"
                                                class="px-4 py-2 bg-rose-50 text-rose-600 text-[9px] font-black uppercase rounded-xl hover:bg-rose-600 hover:text-white transition-all active:scale-95">
                                                Elimina
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center text-slate-300 font-black uppercase text-[10px] tracking-widest">
                                    Nessuna agenzia trovata
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINAZIONE (FISSA IN FONDO) --}}
            @if($agencies->hasPages())
                <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 shrink-0">
                    {{ $agencies->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODALE CREATE/EDIT (INVARATO NELLA LOGICA, AGGIUSTATO OVERLAY) --}}
    @if ($showCreateForm || $showEditForm)
        <div class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md"
        x-cloak
        x-init="document.body.classList.add('overflow-hidden')"
         x-on:destroy="document.body.classList.remove('overflow-hidden')"
        >
            <div class="w-full max-w-md bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-white/10 animate-in zoom-in duration-300" @click.away="$wire.closeForms()">
                <form wire:submit="{{ $showCreateForm ? 'create' : 'update' }}">
                    <div class="bg-slate-900 px-8 py-8 flex justify-between items-center text-white">
                        <div>
                            <h3 class="text-xl font-black uppercase italic tracking-tighter leading-none">
                                {{ $showCreateForm ? 'Nuova Agenzia' : 'Modifica Agenzia' }}
                            </h3>
                            <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-2">Dati Partner Commerciale</p>
                        </div>
                        <button type="button" wire:click="closeForms" class="text-slate-500 hover:text-white transition p-2">
                            <x-icon name="close" class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="p-8 space-y-6">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nome Partner</label>
                            <input type="text" wire:model="name"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase outline-none transition-all" placeholder="ES: AGENZIA UFFICIALE">
                            @error('name') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic tracking-tighter">! {{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Codice Identificativo</label>
                            <input type="text" wire:model="code" maxlength="10"
                                class="w-full px-5 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase tracking-widest font-mono outline-none transition-all" placeholder="COD001">
                            @error('code') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic tracking-tighter">! {{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Colore Identificativo</label>
                            <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <input type="color" wire:model="colour"
                                    class="w-12 h-12 bg-transparent border-none cursor-pointer p-0">

                                <div class="flex-1">
                                    <p class="text-[10px] font-black text-slate-700 uppercase">Seleziona Colore</p>
                                    <p class="text-[9px] text-slate-400 uppercase">Verrà usato nella matrice servizi</p>
                                </div>

                                @if($colour)
                                    <button type="button" wire:click="$set('colour', null)" class="text-[9px] font-black text-rose-500 uppercase hover:underline">
                                        Reset
                                    </button>
                                @endif
                            </div>
                            @error('colour') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic tracking-tighter">! {{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="px-8 pb-8 flex flex-col gap-3">
                        <button type="submit" class="w-full py-5 bg-indigo-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg shadow-indigo-200 active:scale-95 transition-all">
                            {{ $showCreateForm ? 'Conferma Inserimento' : 'Aggiorna Anagrafica' }}
                        </button>
                        <button type="button" wire:click="closeForms" class="text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">Torna Indietro</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('modals')
        <livewire:ui.modal-confirm />
    @endpush
</div>
@push('custom_css')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
</style>
@endpush

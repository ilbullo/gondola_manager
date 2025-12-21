{{-- resources/views/livewire/crud/agency-manager.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-8">

    {{-- HEADER TITOLO --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Gestione Agenzie</h1>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Configurazione anagrafica partner</p>
        </div>
        
        @if (session('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                 class="bg-emerald-500 text-white px-6 py-2 rounded-xl font-black uppercase text-[10px] shadow-lg animate-bounce">
                {{ session('message') }}
            </div>
        @endif
    </div>

    {{-- BARRA AZIONI & RICERCA --}}
    <div class="bg-white rounded-[2.5rem] p-6 mb-8 shadow-xl border border-slate-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="relative flex-1 max-w-md group">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-300 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="CERCA PER NOME O CODICE..."
                    class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 placeholder-slate-300 transition-all uppercase text-xs outline-none">
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="toggleShowDeleted"
                    class="px-5 py-4 {{ $showDeleted ? 'bg-rose-100 text-rose-600' : 'bg-slate-100 text-slate-500' }} font-black uppercase text-[10px] rounded-2xl transition-all">
                    {{ $showDeleted ? 'Nascondi Eliminate' : 'Mostra Eliminate' }}
                </button>

                <button wire:click="toggleCreateForm"
                    class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black uppercase text-[10px] rounded-2xl transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3"/></svg>
                    Nuova Agenzia
                </button>
            </div>
        </div>
    </div>

    {{-- MODALE CREATE/EDIT --}}
    @if ($showCreateForm || $showEditForm)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md" x-cloak>
            
            <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200" @click.away="$wire.closeForms()">
                <form wire:submit="{{ $showCreateForm ? 'create' : 'update' }}">
                    <div class="bg-slate-900 px-8 py-6 flex justify-between items-center text-white">
                        <h3 class="text-xl font-black uppercase italic tracking-tighter">
                            {{ $showCreateForm ? 'Nuova Agenzia' : 'Modifica Agenzia' }}
                        </h3>
                        <button type="button" wire:click="closeForms" class="text-slate-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3"/></svg>
                        </button>
                    </div>

                    <div class="p-8 space-y-6">
                        {{-- NOME --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nome Agenzia</label>
                            <input type="text" wire:model="name"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase outline-none">
                            @error('name') 
                                <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic tracking-tighter">! {{ $message }}</p> 
                            @enderror
                        </div>

                        {{-- CODICE --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Codice (Identificativo)</label>
                            <input type="text" wire:model="code" maxlength="10"
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase tracking-widest font-mono outline-none">
                            @error('code') 
                                <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic tracking-tighter">! {{ $message }}</p> 
                            @enderror
                        </div>
                    </div>

                    <div class="px-8 py-6 bg-slate-50 flex flex-col gap-3 text-center">
                        <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg active:scale-95 transition-all">
                            {{ $showCreateForm ? 'Salva Agenzia' : 'Aggiorna Dati' }}
                        </button>
                        <button type="button" wire:click="closeForms" class="text-slate-400 font-black uppercase text-[10px] hover:text-slate-600 transition-colors">Annulla</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- TABELLA AGENZIE --}}
    <div class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Nome Partner</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Codice</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($agencies as $agency)
                        <tr class="{{ $agency->trashed() ? 'bg-rose-50/50' : 'hover:bg-slate-50/50' }} transition-colors">
                            <td class="px-8 py-5">
                                <span class="text-sm font-black {{ $agency->trashed() ? 'text-rose-400' : 'text-slate-700' }} uppercase italic">{{ $agency->name }}</span>
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

        @if($agencies->hasPages())
            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100">
                {{ $agencies->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        <livewire:ui.modal-confirm />
    @endpush
</div>
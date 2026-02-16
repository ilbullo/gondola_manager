<div class="h-full flex flex-col overflow-hidden">
    {{-- HEADER FISSO --}}
    <div class="shrink-0 p-4 sm:p-8 bg-slate-100">
        <div class="max-w-6xl mx-auto">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none">Gestione Utenti</h1>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Amministrazione permessi e licenze</p>
                </div>
            </div>

            {{-- BARRA RICERCA --}}
            <div class="bg-white rounded-[2.5rem] p-4 sm:p-6 shadow-xl border border-slate-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="relative flex-1 max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-300">
                            <x-icon name="search" class="w-5 h-5 group-focus-within:text-indigo-500 transition-colors" />
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="CERCA UTENTE..."
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase text-xs outline-none">
                    </div>

                    <button wire:click="create" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black uppercase text-[10px] rounded-2xl transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 active:scale-95">
                        <x-icon name="plus" class="w-4 h-4" />
                        Nuovo Utente
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- CORPO TABELLA --}}
    <div class="flex-1 overflow-hidden p-4 sm:p-8 pt-0 bg-slate-100">
        <div class="max-w-6xl mx-auto h-full flex flex-col bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden">
            
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-slate-900">
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800 cursor-pointer group" wire:click="setSort('name')">
                                <div class="flex items-center gap-2">
                                    Utente
                                    <x-icon name="sort" class="w-3 h-3 {{ $sortField === 'name' ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }} transition-opacity" />
                                </div>
                            </th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">Email</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">Ruolo</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-800">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $user)
                            <tr wire:key="user-{{ $user->id }}" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center font-black text-[10px] text-slate-400 uppercase">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                        <span class="text-sm font-black text-slate-700 uppercase italic">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-xs font-bold text-slate-400 font-mono lowercase">{{ $user->email }}</td>
                                <td class="px-8 py-5">
                                    <span class="px-3 py-1 text-[9px] font-black uppercase rounded-lg {{ $user->role->value === 'admin' ? 'bg-rose-100 text-rose-600' : 'bg-indigo-100 text-indigo-600' }}">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="edit({{ $user->id }})" class="px-4 py-2 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase rounded-xl hover:bg-indigo-600 hover:text-white transition-all active:scale-95">Modifica</button>
                                        <button wire:click="confirmDelete({{ $user->id }})" class="px-4 py-2 bg-rose-50 text-rose-600 text-[9px] font-black uppercase rounded-xl hover:bg-rose-600 hover:text-white transition-all active:scale-95">Elimina</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-8 py-20 text-center text-slate-300 font-black uppercase text-[10px] tracking-widest">Nessun utente trovato</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 shrink-0">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODALE EDITING --}}
    @if($editing)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md" 
             x-cloak
             x-init="document.body.classList.add('overflow-hidden')" 
             x-on:destroy="document.body.classList.remove('overflow-hidden')">
            
            <form wire:submit.prevent="save" class="w-full max-w-lg bg-white rounded-[3rem] shadow-2xl overflow-hidden border border-white/10 flex flex-col max-h-[90vh] animate-in zoom-in duration-300" @click.away="$wire.resetForm()">
                
                {{-- Header Modale --}}
                <div class="bg-slate-900 px-8 py-6 flex justify-between items-center text-white shrink-0">
                    <div>
                        <h3 class="text-xl font-black uppercase italic tracking-tighter leading-none">
                            {{ $userId ? 'Modifica Profilo' : 'Nuovo Utente' }}
                        </h3>
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-2">Configurazione rapida</p>
                    </div>
                    <button type="button" wire:click="resetForm" class="text-slate-500 hover:text-white transition p-2">
                        <x-icon name="close" class="w-6 h-6" />
                    </button>
                </div>

                {{-- Corpo Scrollabile --}}
                <div class="p-8 overflow-y-auto bg-white custom-scrollbar flex-1" x-data="{ advanced: false }">
                    
                    {{-- SEZIONE PRINCIPALE: SEMPRE VISIBILE --}}
                    <div class="space-y-5 mb-6">
                        <div>
                            <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-2 ml-1">Numero Licenza</label>
                            <input type="text" placeholder="Es. 140" wire:model.live="license_number" 
                                class="w-full px-5 py-4 bg-indigo-50 border-2 border-indigo-100 rounded-2xl focus:border-indigo-500 font-black text-xl text-slate-700 uppercase outline-none transition-all">
                            @error('license_number') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic">! {{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Nome e Cognome</label>
                            <input type="text" placeholder="MARIO ROSSI" wire:model="name" 
                                class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase outline-none transition-all">
                            @error('name') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic">! {{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- SEZIONE SECONDARIA: ACCORDEON --}}
                    <div class="border-t border-slate-100 pt-4">
                        <button type="button" @click="advanced = !advanced" 
                            class="flex items-center justify-between w-full py-2 group">
                            <span class="text-[10px] font-black text-slate-400 group-hover:text-indigo-600 transition-colors uppercase tracking-[0.2em]">Impostazioni Avanzate</span>
                            <x-icon name="chevron-down" class="w-4 h-4 text-slate-300 transition-transform duration-300" x-bind:class="advanced ? 'rotate-180 text-indigo-600' : ''" />
                        </button>

                        <div x-show="advanced" x-collapse x-cloak class="pt-6 space-y-5">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email</label>
                                    <input type="email" wire:model="email" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-bold text-slate-600 text-xs outline-none transition-all">
                                    @error('email') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic">! {{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                                    <input type="password" wire:model="password" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-bold text-slate-600 text-xs outline-none transition-all">
                                    <p class="text-[8px] font-bold text-slate-300 uppercase italic mt-1">Default: "password"</p>
                                    @error('password') <p class="text-rose-600 text-[10px] font-black mt-2 uppercase italic">! {{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Ruolo</label>
                                    <select wire:model="role" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase text-[10px] outline-none cursor-pointer">
                                        @foreach($roles as $roleEnum)
                                            <option value="{{ $roleEnum->value }}">{{ $roleEnum->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tipo Licenza</label>
                                    <select wire:model="type" class="w-full px-5 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase text-[10px] outline-none cursor-pointer">
                                        @foreach($licenseTypes as $typeEnum)
                                            <option value="{{ $typeEnum->value }}">{{ $typeEnum->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-8 py-8 bg-slate-50 border-t flex flex-col gap-3 shrink-0">
                    <button type="submit" class="w-full py-5 bg-indigo-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg active:scale-95 transition-all">
                        {{ $userId ? 'Salva Modifiche' : 'Crea Utente' }}
                    </button>
                    <button type="button" wire:click="resetForm" class="text-slate-400 font-black uppercase text-[10px] tracking-widest hover:text-slate-600 transition-colors text-center">Annulla</button>
                </div>
            </form>
        </div>
    @endif
        
    @push('modals')
        <livewire:ui.modal-confirm />
    @endpush
</div>
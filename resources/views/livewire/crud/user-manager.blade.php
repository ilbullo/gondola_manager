{{-- resources/views/livewire/crud/user-manager.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-8">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter leading-none">Gestione Utenti</h1>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Amministrazione permessi e licenze</p>
        </div>
        
        @if (session('message'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                 class="bg-emerald-500 text-white px-6 py-2 rounded-xl font-black uppercase text-[10px] shadow-lg">
                {{ session('message') }}
            </div>
        @endif
    </div>

    {{-- BARRA RICERCA --}}
    <div class="bg-white rounded-[2.5rem] p-6 mb-8 shadow-xl border border-slate-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-slate-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="3"/></svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="CERCA UTENTE..."
                    class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase text-xs outline-none">
            </div>

            <button wire:click="create" class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-black uppercase text-[10px] rounded-2xl transition-all shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3"/></svg>
                Nuovo Utente
            </button>
        </div>
    </div>

    {{-- MODALE COMPATTO CON FIX TASTI E VALIDAZIONE --}}
    @if($editing)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md" x-cloak>
            {{-- Il tag FORM deve avvolgere TUTTO il contenuto per far funzionare il tasto submit --}}
            <form wire:submit.prevent="save" class="w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]" @click.away="$wire.resetForm()">
                
                {{-- Header --}}
                <div class="bg-slate-900 px-8 py-5 flex justify-between items-center text-white shrink-0">
                    <h3 class="text-xl font-black uppercase italic tracking-tighter">
                        {{ $userId ? 'Modifica Profilo' : 'Nuovo Utente' }}
                    </h3>
                    <button type="button" wire:click="resetForm" class="text-slate-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3"/></svg>
                    </button>
                </div>

                {{-- Corpo Scrollabile --}}
                <div class="p-8 overflow-y-auto space-y-5 bg-white custom-scrollbar">
                    
                    {{-- Nome --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Nome e Cognome</label>
                        <input type="text" wire:model="name" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 {{ $errors->has('name') ? 'ring-2 ring-rose-500' : 'focus:ring-indigo-100' }} font-black text-slate-700 uppercase outline-none">
                        @error('name') <p class="text-rose-600 text-[10px] font-black mt-1 uppercase italic">! {{ $message }}</p> @enderror
                    </div>

                    {{-- Email e Password --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 {{ $errors->has('email') ? 'ring-2 ring-rose-500' : 'focus:ring-indigo-100' }} font-black text-slate-700 outline-none">
                            @error('email') <p class="text-rose-600 text-[10px] font-black mt-1 uppercase italic">! {{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Password</label>
                            <input type="password" wire:model="password" placeholder="{{ $userId ? 'LASCIA VUOTA' : '••••••' }}" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 {{ $errors->has('password') ? 'ring-2 ring-rose-500' : 'focus:ring-indigo-100' }} font-black text-slate-700 outline-none">
                            @error('password') <p class="text-rose-600 text-[10px] font-black mt-1 uppercase italic">! {{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Ruolo e Licenza --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Ruolo</label>
                            <select wire:model="role" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase outline-none appearance-none">
                                @foreach($roles as $roleEnum)
                                    <option value="{{ $roleEnum->value }}">{{ $roleEnum->label() }}</option>
                                @endforeach
                            </select>
                            @error('role') <p class="text-rose-600 text-[10px] font-black mt-1 italic">! {{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Tipo Licenza</label>
                            <select wire:model="type" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 font-black text-slate-700 uppercase outline-none appearance-none">
                                @foreach($licenseTypes as $typeEnum)
                                    <option value="{{ $typeEnum->value }}">{{ $typeEnum->label() }}</option>
                                @endforeach
                            </select>
                            @error('type') <p class="text-rose-600 text-[10px] font-black mt-1 italic">! {{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Numero Licenza --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Numero Licenza</label>
                        <input type="text" wire:model="license_number" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 {{ $errors->has('license_number') ? 'ring-2 ring-rose-500' : 'focus:ring-indigo-100' }} font-black text-slate-700 uppercase outline-none">
                        @error('license_number') <p class="text-rose-600 text-[10px] font-black mt-1 uppercase italic">! {{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Footer Fisso (Dentro il tag FORM) --}}
                <div class="px-8 py-6 bg-slate-50 border-t flex flex-col gap-3 shrink-0">
                    <button type="submit" class="w-full py-4 bg-indigo-600 text-white font-black uppercase text-xs rounded-2xl shadow-lg active:scale-95 transition-all">
                        {{ $userId ? 'Salva Modifiche' : 'Crea Utente' }}
                    </button>
                    <button type="button" wire:click="resetForm" class="text-slate-400 font-black uppercase text-[10px] hover:text-slate-600 transition-colors text-center">Annulla</button>
                </div>
            </form>
        </div>
    @endif

    {{-- TABELLA --}}
    <div class="bg-white rounded-[2.5rem] shadow-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 cursor-pointer" wire:click="setSort('name')">Utente</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Email</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Ruolo</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5"><span class="text-sm font-black text-slate-700 uppercase italic">{{ $user->name }}</span></td>
                            <td class="px-8 py-5 text-xs font-bold text-slate-400">{{ $user->email }}</td>
                            <td class="px-8 py-5">
                                <span class="px-3 py-1 text-[9px] font-black uppercase rounded-lg {{ $user->isAdmin() ? 'bg-rose-100 text-rose-600' : 'bg-indigo-100 text-indigo-600' }}">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="edit({{ $user->id }})" class="px-4 py-2 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase rounded-xl hover:bg-indigo-600 hover:text-white transition-all">Modifica</button>
                                    <button wire:click="confirmDelete({{ $user->id }})" class="px-4 py-2 bg-rose-50 text-rose-600 text-[9px] font-black uppercase rounded-xl hover:bg-rose-600 hover:text-white transition-all">Elimina</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-8 py-20 text-center text-slate-300 font-black uppercase text-[10px]">Nessun utente trovato</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginazione --}}
        @if($users->hasPages())
            <div class="bg-slate-50 px-8 py-6 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @push('modals')
        <livewire:ui.modal-confirm />
    @endpush

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</div>
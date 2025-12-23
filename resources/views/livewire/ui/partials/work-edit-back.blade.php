{{-- resources/views/livewire/ui/partials/work-edit-back.blade.php --}}
<div class="h-full flex flex-col bg-white relative">

    {{-- Tasto Chiudi --}}
    <button type="button"
            @click="$wire.closeModal()"
            class="absolute top-4 right-4 z-50 w-10 h-10 flex items-center justify-center bg-black/5 hover:bg-black/10 rounded-full text-slate-400 transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Header (Fisso) --}}
    <div class="bg-slate-800 p-8 text-center text-white shrink-0">
        <h2 class="text-xl font-black uppercase italic tracking-wider">Impostazioni Lavoro</h2>
    </div>

    {{-- Form Body (Scorrevole) --}}
    <div class="p-8 space-y-6 flex-1 overflow-y-auto">
        
        {{-- Input Importo --}}
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Importo (€)</label>
            <input type="number" step="0.01" wire:model="amount"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 font-black text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-xl">
        </div>

        {{-- Input Voucher --}}
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Voucher / Note</label>
            <input type="text" wire:model="voucher"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 font-black text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all uppercase"
                placeholder="Codice o nota...">
        </div>

        {{-- Contenitore Toggle Group --}}
        <div class="space-y-3">
            {{-- FISSO ALLA LICENZA --}}
            <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-rose-500 flex items-center justify-center shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 text-white">
                            <path d="M8.5 1a.75.75 0 0 0-.75.75V6.5a.5.5 0 0 1-1 0V2.75a.75.75 0 0 0-1.5 0V7.5a.5.5 0 0 1-1 0V4.75a.75.75 0 0 0-1.5 0v4.5a5.75 5.75 0 0 0 11.5 0v-2.5a.75.75 0 0 0-1.5 0V9.5a.5.5 0 0 1-1 0V2.75a.75.75 0 0 0-1.5 0V6.5a.5.5 0 0 1-1 0V1.75A.75.75 0 0 0 8.5 1Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-black text-slate-700 uppercase block leading-none">Fisso alla licenza</span>
                        <span class="text-[9px] text-slate-400 uppercase">Blocca posizione</span>
                    </div>
                </div>
                <button type="button" @click="$wire.excluded = !$wire.excluded"
                    :class="$wire.excluded ? 'bg-rose-500' : 'bg-slate-300'"
                    class="w-12 h-6 rounded-full relative transition-colors duration-200">
                    <span :class="$wire.excluded ? 'translate-x-6' : 'translate-x-1'"
                          class="absolute top-1 left-0 w-4 h-4 bg-white rounded-full transition-transform shadow-md"></span>
                </button>
            </div>

            {{-- RIPARTISCI DAL PRIMO --}}
            <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-yellow-400 flex items-center justify-center shadow-sm shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 text-white">
                            <path d="M2.09 15a1 1 0 0 0 1-1V8a1 1 0 1 0-2 0v6a1 1 0 0 0 1 1ZM5.765 13H4.09V8c.663 0 1.218-.466 1.556-1.037a4.02 4.02 0 0 1 1.358-1.377c.478-.292.907-.706.989-1.26V4.32a9.03 9.03 0 0 0 0-2.642c-.028-.194.048-.394.224-.479A2 2 0 0 1 11.09 3c0 .812-.08 1.605-.235 2.371a.521.521 0 0 0 .502.629h1.733c1.104 0 2.01.898 1.901 1.997a19.831 19.831 0 0 1-1.081 4.788c-.27.747-.998 1.215-1.793 1.215H9.414c-.215 0-.428-.035-.632-.103l-2.384-.794A2.002 2.002 0 0 0 5.765 13Z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-black text-slate-700 uppercase block leading-none">Ripartisci dal primo</span>
                        <span class="text-[9px] text-slate-400 uppercase">Priorità 1° colonna</span>
                    </div>
                </div>
                <button type="button" @click="$wire.shared_from_first = !$wire.shared_from_first"
                    :class="$wire.shared_from_first ? 'bg-yellow-400' : 'bg-slate-300'"
                    class="w-12 h-6 rounded-full relative transition-colors duration-200">
                    <span :class="$wire.shared_from_first ? 'translate-x-6' : 'translate-x-1'"
                          class="absolute top-1 left-0 w-4 h-4 bg-white rounded-full transition-transform shadow-md"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Footer Azioni (Ancorato in basso) --}}
    <div class="p-6 bg-slate-50 border-t border-slate-100 shrink-0">
        <div class="grid grid-cols-2 gap-3">
            <button type="button" @click="flipped = false"
                class="py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black uppercase text-[10px] hover:bg-slate-50 transition-colors shadow-sm">
                Annulla
            </button>
            <button type="button" wire:click="save"
                class="py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase text-[10px] shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">
                Salva
            </button>
        </div>
    </div>
</div>
{{-- resources/views/livewire/ui/partials/work-edit-back.blade.php --}}
<div class="h-full flex flex-col bg-white relative">

    {{-- Tasto Chiudi (identico al fronte per coerenza) --}}
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
    <div class="p-8 space-y-5 flex-1 overflow-y-auto">

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Importo (€)</label>
            <input type="number" step="0.01" wire:model="amount"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 font-black text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
        </div>

        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Voucher / Note</label>
            <input type="text" wire:model="voucher"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 font-black text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all"
                placeholder="Inserisci codice o nota...">
        </div>

        <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <div>
                <span class="text-xs font-black text-slate-700 uppercase">Fisso alla licenza</span>
                <p class="text-[9px] text-slate-400 uppercase">anche dopo la ripartizione</p>
            </div>
            <button type="button" @click="$wire.excluded = !$wire.excluded"
                :class="$wire.excluded ? 'bg-rose-500' : 'bg-slate-300'"
                class="w-12 h-6 rounded-full relative transition-colors duration-200">
                <span :class="$wire.excluded ? 'translate-x-6' : 'translate-x-1'"
                      class="absolute top-1 left-0 w-4 h-4 bg-white rounded-full transition-transform"></span>
            </button>
        </div>

        <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
            <div>
                <span class="text-xs font-black text-slate-700 uppercase">Ripartisci dal primo</span>
                <p class="text-[9px] text-slate-400 uppercase">Ripartisce il lavoro partendo dal primo</p>
            </div>
            <button type="button" @click="$wire.shared_from_first = !$wire.shared_from_first"
                :class="$wire.shared_from_first ? 'bg-rose-500' : 'bg-slate-300'"
                class="w-12 h-6 rounded-full relative transition-colors duration-200">
                <span :class="$wire.shared_from_first ? 'translate-x-6' : 'translate-x-1'"
                      class="absolute top-1 left-0 w-4 h-4 bg-white rounded-full transition-transform"></span>
            </button>
        </div>
    </div>

    {{-- Footer Azioni (Ancorato in basso) --}}
    <div class="p-6 bg-slate-50 border-t border-slate-100 shrink-0">
        <div class="flex gap-3">
            <button type="button" @click="flipped = false"
                class="flex-1 py-4 bg-white border border-slate-200 text-slate-400 rounded-2xl font-black uppercase text-[10px] hover:bg-slate-50 transition-colors">
                Annulla
            </button>
            <button type="button" wire:click="save"
                class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase text-[10px] shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                Salva
            </button>
        </div>
    </div>
</div>

{{-- resources/views/livewire/ui/partials/work-edit-back.blade.php --}}
<div class="h-full flex flex-col bg-slate-50">
    <div class="bg-slate-800 p-6 text-white text-center shrink-0">
        <h3 class="text-xl font-black uppercase italic">Modifica Dati</h3>
    </div>

    <div class="p-6 space-y-4 flex-1 overflow-y-auto">
        <div>
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Importo</label>
            <input type="number" wire:model="amount" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-4 text-xl font-black text-emerald-600 outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="text-[10px] font-black text-slate-400 uppercase ml-2">Voucher</label>
            <input type="text" wire:model="voucher" class="w-full h-14 bg-white border border-slate-200 rounded-2xl px-4 text-sm font-black uppercase outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="flex flex-col gap-3 pt-2">
            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200">
                <span class="text-xs font-black text-slate-700 uppercase">Escludi da Rip.</span>
                <button type="button" @click="$wire.excluded = !$wire.excluded" :class="$wire.excluded ? 'bg-rose-500' : 'bg-slate-200'" class="w-10 h-5 rounded-full relative transition-colors">
                    <span :class="$wire.excluded ? 'translate-x-5' : 'translate-x-1'" class="absolute top-1 left-0 w-3 h-3 bg-white rounded-full transition-transform"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white border-t border-slate-100 flex gap-3">
        <button @click="flipped = false" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black uppercase text-xs">Annulla</button>
        <button wire:click="save" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase text-xs shadow-lg shadow-indigo-200">Salva</button>
    </div>
</div>
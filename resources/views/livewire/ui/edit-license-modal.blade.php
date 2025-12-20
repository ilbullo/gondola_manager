{{-- resources/views/livewire/ui/edit-license-modal.blade.php --}}
<div x-data x-show="$wire.show" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4">
    
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden animate-in zoom-in">
        <div class="bg-slate-800 p-6 text-white text-center">
            <h2 class="text-xl font-black uppercase italic">Impostazioni Licenza</h2>
        </div>

        <form wire:submit="save" class="p-8 space-y-6">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Turno Operativo</label>
                <select wire:model="turn" class="w-full bg-slate-100 border-none rounded-2xl p-4 font-black text-slate-700 focus:ring-2 focus:ring-indigo-500">
                    @foreach(\App\Enums\DayType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div>
                    <span class="text-xs font-black text-slate-700 uppercase">Solo Contanti (X)</span>
                    <p class="text-[9px] text-slate-400 uppercase">Esclude lavori agenzia</p>
                </div>
                <button type="button" @click="$wire.onlyCashWorks = !$wire.onlyCashWorks"
                    :class="$wire.onlyCashWorks ? 'bg-emerald-500' : 'bg-slate-300'"
                    class="w-12 h-6 rounded-full relative transition-colors duration-200">
                    <span :class="$wire.onlyCashWorks ? 'translate-x-6' : 'translate-x-1'"
                          class="absolute top-1 left-0 w-4 h-4 bg-white rounded-full transition-transform"></span>
                </button>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="$wire.show = false" class="flex-1 py-4 bg-slate-100 text-slate-400 rounded-2xl font-black uppercase">Annulla</button>
                <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase shadow-lg shadow-indigo-200">Salva</button>
            </div>
        </form>
    </div>
</div>
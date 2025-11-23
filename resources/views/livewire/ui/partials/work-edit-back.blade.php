<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-3">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-black text-white">Modifica Lavoro</h3>
            <button @click="flipped = false" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Form -->
    <div class="flex-1 p-5 space-y-4 overflow-y-auto">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-600">Tipo</label>
                <select wire:model.live="value" class="mt-1 block w-full rounded-lg border-gray-300 text-sm">
                    @foreach(\App\Enums\WorkType::cases() as $type)
                        @if($type->value != \App\Enums\WorkType::FIXED->value && $type->value != \App\Enums\WorkType::EXCLUDED->value )
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600">Importo</label>
                <input type="number" step="0.01" wire:model.live="amount" class="mt-1 block w-full rounded-lg border-gray-300 text-sm" />
            </div>
        </div>

        @if($value === 'A')
            <div>
                <label class="block text-xs font-semibold text-gray-600">Codice Agenzia</label>
                <input type="text" wire:model.live="agency_code" class="mt-1 block w-full rounded-lg border-gray-300 text-sm" />
            </div>
        @endif

        <div>
            <label class="block text-xs font-semibold text-gray-600">Voucher / Note</label>
            <input type="text" wire:model.live="voucher" class="mt-1 block w-full rounded-lg border-gray-300 text-sm" />
        </div>

        <!-- CHECKBOX MUTUAMENTE ESCLUSIVI -->
        <div class="flex items-center justify-between pt-3 border-t">
            <label class="flex items-center gap-2 text-xs cursor-pointer select-none">
                <input 
                    type="checkbox" 
                    x-model="excluded"
                    @change="
                        if ($event.target.checked) shared_from_first = false;
                        excluded = $event.target.checked;
                    "
                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300"
                />
                <span class="font-medium">Escluso da ripartizione</span>
            </label>

            <label class="flex items-center gap-2 text-xs cursor-pointer select-none">
                <input 
                    type="checkbox" 
                    x-model="shared_from_first"
                    @change="
                        if ($event.target.checked) excluded = false;
                        shared_from_first = $event.target.checked;
                    "
                    class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300"
                />
                <span class="font-medium">Ripartito dal primo</span>
            </label>
        </div>
    </div>

    <!-- Footer pulsanti -->
    <div class="bg-gray-50 px-5 py-3 flex justify-between border-t">
        <button @click="flipped = false" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-xl font-medium transition">
            Annulla
        </button>
        <button 
            wire:click="save" 
            class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition transform hover:scale-105 shadow-lg"
        >
            Salva Modifiche
        </button>
    </div>
</div>
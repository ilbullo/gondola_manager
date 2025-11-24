<div class="h-full flex flex-col bg-white">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-5">
        <div class="flex items-center justify-between">
            <h2 class="text-xl sm:text-2xl font-bold text-white">Modifica Lavoro</h2>
            <button @click="flipped = false" 
                    class="rounded-full p-2.5 text-white/80 hover:text-white hover:bg-white/20 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Corpo scrollabile -->
    <div class="flex-1 px-6 py-7 sm:px-8 overflow-y-auto space-y-6">
        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo Lavoro</label>
                <select wire:model.live="value" 
                        class="w-full px-5 py-3.5 text-base font-medium text-gray-900 bg-gray-50 border-2 border-gray-300 rounded-xl focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/20 transition-all">
                    @foreach(\App\Enums\WorkType::cases() as $type)
                        @if(!in_array($type->value, [\App\Enums\WorkType::FIXED->value, \App\Enums\WorkType::EXCLUDED->value]))
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Importo (€)</label>
                <div class="relative">
                    <input type="number" step="0.01" wire:model.live="amount"
                           class="w-full pl-11 pr-4 py-3.5 text-base font-medium text-gray-900 bg-gray-50 border-2 border-gray-300 rounded-xl focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/20 transition-all"
                           placeholder="90.00" />
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-gray-500">€</span>
                </div>
            </div>
        </div>

        @if($value === 'A')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Codice Agenzia</label>
                <input type="text" wire:model.live="agency_code"
                       class="w-full px-5 py-3.5 text-base font-medium text-gray-900 bg-gray-50 border-2 border-gray-300 rounded-xl focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/20 transition-all"
                       placeholder="es. AG123" />
            </div>
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Voucher / Note</label>
            <input type="text" wire:model.live="voucher"
                   class="w-full px-5 py-3.5 text-base font-medium text-gray-900 bg-gray-50 border-2 border-gray-300 rounded-xl focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/20 transition-all"
                   placeholder="Inserisci voucher o nota..." />
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-3.5">
                    <input type="checkbox" x-model="excluded"
                           @change="if ($event.target.checked) shared_from_first = false; excluded = $event.target.checked"
                           class="h-5 w-5 rounded border-2 border-gray-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-2 transition" />
                    <label class="text-base font-semibold text-gray-800 cursor-pointer select-none">
                        Escluso da ripartizione
                    </label>
                </div>
                <span class="text-xs font-medium text-gray-500 bg-white/80 px-2.5 py-1.5 rounded-full">
                    Non conta nel riepilogo
                </span>
            </div>

            <div class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-3.5">
                    <input type="checkbox" x-model="shared_from_first"
                           @change="if ($event.target.checked) excluded = false; shared_from_first = $event.target.checked"
                           class="h-5 w-5 rounded border-2 border-gray-300 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-2 transition" />
                    <label class="text-base font-semibold text-gray-800 cursor-pointer select-none">
                        Ripartito dal primo
                    </label>
                </div>
                <span class="text-xs font-medium text-gray-500 bg-white/80 px-2.5 py-1.5 rounded-full">
                    Ripartito dalla prima licenza
                </span>
            </div>
        </div>
    </div>

    <!-- Footer fisso -->
    <div class="px-6 py-5 sm:px-8 bg-gray-50 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row gap-3">
            <button @click="flipped = false"
                    class="order-2 sm:order-1 w-full px-6 py-3.5 text-base font-semibold-bold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-xl transition-all">
                Annulla
            </button>
            <button wire:click="save"
                    class="order-1 sm:order-2 w-full px-8 py-3.5 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 rounded-xl shadow-lg hover:shadow-xl focus:ring-4 focus:ring-emerald-500/50 transition-all">
                Salva Modifiche
            </button>
        </div>
    </div>
</div>
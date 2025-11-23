<div>
    @if($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div 
                class="relative w-full max-w-sm preserve-3d transition-all duration-700"
                x-data="{ flipped: false }"
                x-effect="$watch('open', () => flipped = true)"
            >
                <!-- Fronte: Dettagli (riutilizza il tuo modale esistente) -->
                <div 
                    x-show="!flipped"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="backface-hidden rotate-y-180"
                    x-transition:enter-end="backface-hidden rotate-y-0"
                    class="absolute inset-0 backface-hidden"
                >
                    <livewire:ui.work-live-info-modal :wire:key="'info-'.$work['id']" />
                </div>

                <!-- Retro: Form modifica (effetto flip) -->
                <div 
                    x-show="flipped"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="backface-hidden -rotate-y-180"
                    x-transition:enter-end="backface-hidden rotate-y-0"
                    class="absolute inset-0 backface-hidden bg-white rounded-2xl shadow-2xl overflow-hidden"
                    style="transform-style: preserve-3d; backface-visibility: hidden;"
                >
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

                    <div class="p-5 space-y-4">
                        <div class="grid grid-cols-2 gap-3">
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

                        <div class="flex items-center justify-between pt-3 border-t">
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" wire:model.live="excluded" class="rounded" />
                                <span>Escluso da ripartizione</span>
                            </label>
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" wire:model.live="shared_from_first" class="rounded" />
                                <span>Ripartito dal primo</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-5 py-3 flex justify-between">
                        <button @click="flipped = false" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-xl font-medium">
                            Annulla
                        </button>
                        <button wire:click="save" class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition transform hover:scale-105">
                            Salva Modifiche
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
<style>
    .preserve-3d { transform-style: preserve-3d; }
    .backface-hidden { backface-visibility: hidden; }
    .rotate-y-180 { transform: rotateY(180deg); }
    .-rotate-y-180 { transform: rotateY(-180deg); }
</style>
</div>



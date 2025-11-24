{{-- resources/views/livewire/modals/work-details-modal.blade.php --}}
<div>
    @if($isOpen)
        <div x-data="{ open: @entangle('isOpen') }"
             x-show="open"
             x-transition
             @keydown.escape.window="$wire.closeModal()"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70"
             role="dialog"
             aria-modal="true"
             aria-labelledby="work-modal-title"
             x-cloak>

            <div @click="$wire.closeModal()" class="absolute inset-0" aria-hidden="true"></div>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100"
                 @click.stop
                 class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl ring-1 ring-gray-900/10 overflow-hidden max-h-[90vh] flex flex-col">

                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-5">
                    <div class="flex items-center justify-between">
                        <h2 id="work-modal-title" class="text-2xl font-bold text-white">
                            Configura Lavoro
                        </h2>
                        <button @click="$wire.closeModal()"
                                class="p-3 rounded-full text-white hover:bg-white/20 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/50 transition"
                                aria-label="Chiudi modale configurazione lavoro">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form wire:submit="save" class="flex-1 flex flex-col p-6 space-y-6 overflow-y-auto">
                    <!-- Importo -->
                    <div>
                        <label for="amount" class="block text-base font-semibold text-gray-800 mb-2">
                            Importo (€)
                        </label>
                        <div class="relative">
                            <input type="number"
                                   id="amount"
                                   wire:model.live="amount"
                                   step="0.01"
                                   min="0"
                                   required
                                   placeholder="90.00"
                                   aria-describedby="amount-error"
                                   class="w-full pl-12 pr-4 py-4 text-lg font-medium text-gray-900 bg-gray-50 border-2 border-gray-400 rounded-2xl focus:border-blue-600 focus:bg-white focus:ring-4 focus:ring-blue-600/30 focus:outline-none transition"
                                   />
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-bold text-gray-700 pointer-events-none" aria-hidden="true">€</span>
                        </div>
                        @error('amount')
                            <p id="amount-error" class="mt-2 text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Caselle occupate -->
                    <div>
                        <label for="slotsOccupied" class="block text-base font-semibold text-gray-800 mb-2">
                            Caselle Occupate
                        </label>
                        <select id="slotsOccupied"
                                wire:model.live="slotsOccupied"
                                required
                                class="w-full px-5 py-4 text-lg font-medium bg-gray-50 border-2 border-gray-400 rounded-2xl focus:border-blue-600 focus:ring-4 focus:ring-blue-600/30 focus:outline-none transition cursor-pointer"
                                aria-describedby="slots-error">
                            <option value="" disabled>Seleziona...</option>
                            <option value="1">1 Casella</option>
                            <option value="2">2 Caselle</option>
                            <option value="3">3 Caselle</option>
                            <option value="4">4 Caselle</option>
                        </select>
                        @error('slotsOccupied')
                            <p id="slots-error" class="mt-2 text-sm font-medium text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Checkbox: Fisso alla licenza -->
                    <div class="flex items-center justify-between bg-gray-100 rounded-2xl p-5 border-2 border-gray-300">
                        <div class="flex items-center gap-4">
                            <input type="checkbox"
                                   id="excluded"
                                   wire:model.live="excluded"
                                   class="h-7 w-7 rounded border-2 border-gray-500 text-blue-600 focus:ring-blue-600 focus:ring-offset-2"/>
                            <label for="excluded" class="text-lg font-semibold text-gray-800 cursor-pointer select-none">
                                Fisso alla licenza
                            </label>
                        </div>
                        <span class="text-sm font-medium text-gray-600 bg-white px-3 py-1.5 rounded-full">
                            Non conta nel riepilogo
                        </span>
                    </div>

                    <!-- Checkbox: Ripartisci dal primo -->
                    <div class="flex items-center justify-between bg-gray-100 rounded-2xl p-5 border-2 border-gray-300">
                        <div class="flex items-center gap-4">
                            <input type="checkbox"
                                   id="sharedFromFirst"
                                   wire:model.live="sharedFromFirst"
                                   class="h-7 w-7 rounded border-2 border-gray-500 text-blue-600 focus:ring-blue-600 focus:ring-offset-2"/>
                            <label for="sharedFromFirst" class="text-lg font-semibold text-gray-800 cursor-pointer select-none">
                                Ripartisci dal primo
                            </label>
                        </div>
                        <span class="text-sm font-medium text-gray-600 bg-white px-3 py-1.5 rounded-full">
                            Dalla prima licenza
                        </span>
                    </div>

                    <!-- Pulsanti -->
                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <button type="button"
                                wire:click="closeModal"
                                class="py-4 text-lg font-semibold text-gray-800 bg-gray-200 hover:bg-gray-300 focus:bg-gray-300 rounded-2xl focus:outline-none focus:ring-4 focus:ring-gray-400 transition">
                            Annulla
                        </button>
                        <button type="submit"
                                class="py-4 text-lg font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 focus:from-emerald-700 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

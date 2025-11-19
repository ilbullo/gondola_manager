{{-- resources/views/livewire/modals/work-details-modal.blade.php --}}
<div>
    <div x-data="{ open: @entangle('isOpen') }" x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0"
        @keydown.escape.window="$wire.closeModal()"
        class="fixed inset-0 z-[100] overflow-y-auto bg-black bg-opacity-50 backdrop-blur-sm"
        aria-labelledby="work-modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0" @click="$wire.closeModal()" aria-hidden="true"></div>

        <!-- Contenitore centrato -->
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8">
            <div x-show="open" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-end="opacity-0 translate-y-8 scale-95" @click.stop
                class="relative w-full max-w-lg transform overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black ring-opacity-10">
                <!-- Header con gradiente -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 sm:px-8">
                    <div class="flex items-center justify-between">
                        <h2 id="work-modal-title" class="text-2xl font-bold text-white">
                            Configura Lavoro
                        </h2>
                        <button @click="$wire.closeModal()"
                            class="rounded-full p-2 text-white/80 hover:text-white hover:bg-white/20 focus:outline-none focus:ring-4 focus:ring-white/30 transition"
                            aria-label="Chiudi modale">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Corpo modale -->
                <div class="px-6 py-8 sm:px-10">
                    <form wire:submit="save" class="space-y-7">
                        <!-- Importo -->
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2.5">
                                Importo (€)
                            </label>
                            <div class="relative">
                                <input type="number" id="amount" wire:model.live="amount" step="0.01"
                                    min="0" required placeholder="90.00"
                                    class="w-full pl-12 pr-4 py-4 text-lg font-medium text-gray-900 bg-gray-50 border-2 border-gray-300 rounded-2xl focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/20 transition-all duration-200" />
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-bold text-gray-500">€</span>
                            </div>
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Caselle occupate -->
                        <div>
                            <label for="slotsOccupied" class="block text-sm font-semibold text-gray-700 mb-2.5">
                                Caselle Occupate
                            </label>
                            <select id="slotsOccupied" wire:model.live="slotsOccupied"
                                class="w-full px-5 py-4 text-lg font-medium text-gray-900 bg-gray-50 border-2 border-gray-300 rounded-2xl focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/20 transition-all duration-200 appearance-none cursor-pointer"
                                style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3E%3Cpath stroke=%22%236b7280%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3E%3C/svg%3E'); background-position: right 1rem center; background-repeat: no-repeat; background-size: 1.5em;">
                                <option value="1">1 Casella</option>
                                <option value="2">2 Caselle</option>
                                <option value="3">3 Caselle</option>
                                <option value="4">4 Caselle</option>
                            </select>
                            @error('slotsOccupied')
                                <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Checkbox Fisso alla Licenza -->
                        <div
                            class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-5 border-2 border-gray-200">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" id="excluded" wire:model.live="excluded"
                                    class="h-6 w-6 rounded border-2 border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-2 transition" />
                                <label for="excluded" class="text-base font-semibold text-gray-800 cursor-pointer">
                                    Fisso alla licenza
                                </label>
                            </div>
                            <span class="text-xs font-medium text-gray-500 bg-white/80 px-3 py-1.5 rounded-full">
                                Non conta nel riepilogo
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-gray-100 rounded-2xl p-5 border-2 border-gray-200">
                            <div class="flex items-center gap-4">
                                <input id="sharedFromFirst" type="checkbox" wire:model.live="sharedFromFirst"
                                    class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 focus:ring-2" />
                                <label for="sharedFromFirst"
                                    class="text-sm font-bold text-gray-800 cursor-pointer select-none">
                                    Ripartisci dal primo
                                </label>

                            </div>
                            <span class="text-xs font-medium text-gray-500 bg-white/80 px-3 py-1.5 rounded-full">
                                Ripartito dalla prima licenza
                            </span>
                        </div>


                        <!-- Pulsanti -->
                        <div class="flex flex-col sm:flex-row gap-4 pt-6">
                            <button type="button" wire:click="closeModal"
                                class="order-2 sm:order-1 w-full sm:w-auto px-8 py-4 text-base font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-2xl focus:ring-4 focus:ring-gray-300 transition-all duration-200">
                                Annulla
                            </button>
                            <button type="submit"
                                class="order-1 sm:order-2 w-full sm:w-auto px-10 py-4 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 rounded-2xl shadow-xl hover:shadow-2xl focus:ring-4 focus:ring-emerald-500/50 transition-all duration-200 flex items-center justify-center gap-3">
                                <span wire:target="save">Conferma Configurazione</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

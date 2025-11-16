<div>
    @if ($isOpen)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-[100] transition-opacity duration-300" x-data="{ open: true }" x-show="open" x-transition.opacity>
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4 sm:mx-6 md:mx-auto">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Configura Dettagli Lavoro</h2>

                <form wire:submit.prevent="save" class="space-y-4">
                    <!-- Importo -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Importo (€)</label>
                        <input type="number" id="amount" wire:model="amount"
                            class="mt-1 w-full h-10 px-3 text-sm border-2 border-gray-300 rounded-md focus:ring-2 focus:ring-blue-300 focus:outline-none transition-all duration-200"
                            min="0" step="0.01" required />
                        @error('amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Slot Occupati -->
                    <div>
                        <label for="slotsOccupied" class="block text-sm font-medium text-gray-700">Slot Occupati</label>
                        <select id="slotsOccupied" wire:model="slotsOccupied"
                            class="mt-1 w-full h-10 px-3 text-sm border-2 border-gray-300 rounded-md focus:ring-2 focus:ring-blue-300 focus:outline-none transition-all duration-200">
                            <option value="1">1 Casella</option>
                            <option value="2">2 Caselle</option>
                        </select>
                        @error('slotsOccupied') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Checkbox Excluded -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="excluded" wire:model="excluded"
                            class="h-4 w-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-300" />
                        <label for="excluded" class="text-sm font-medium text-gray-700">Fisso alla Licenza</label>
                    </div>

                    <!-- Pulsanti -->
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="closeModal"
                            class="h-10 px-4 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md focus:ring-2 focus:ring-gray-300 transition-all duration-200">
                            Annulla
                        </button>
                        <button type="submit"
                            class="h-10 px-4 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md focus:ring-2 focus:ring-blue-300 transition-all duration-200">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
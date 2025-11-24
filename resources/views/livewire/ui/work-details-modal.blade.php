{{-- resources/views/livewire/modals/work-details-modal.blade.php --}}
<div>
    @if($isOpen)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-data="{
                open: @entangle('isOpen'),
                excluded: @entangle('excluded').defer,
                sharedFromFirst: @entangle('sharedFromFirst').defer
            }"
            x-show="open"
            x-transition.opacity
            @keydown.escape.window="$wire.closeModal()"
            role="dialog"
            aria-modal="true"
            aria-labelledby="work-modal-title"
            aria-describedby="work-modal-form"
            x-trap.noscroll.inert="open"
            x-init="$nextTick(() => document.getElementById('amount').focus())"
            x-cloak
        >
            {{-- Backdrop Click per chiudere --}}
            <div @click="$wire.closeModal()" class="absolute inset-0" aria-hidden="true"></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop
                class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
            >

                <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                    <h2 id="work-modal-title" class="text-xl font-bold text-white">
                        Configura Lavoro
                    </h2>
                    <button @click="$wire.closeModal()"
                            class="text-white/80 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-white/50 rounded p-1"
                            aria-label="Chiudi modale">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit="save" id="work-modal-form" class="flex-1 flex flex-col p-6 space-y-5 overflow-y-auto">

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Importo (€)</label>
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">€</span>
                            </div>
                            <input type="number"
                                   id="amount"
                                   wire:model.live="amount"
                                   step="0.01"
                                   min="0"
                                   required
                                   placeholder="90.00"
                                   class="block w-full rounded-lg border-gray-300 pl-7 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition"
                                   aria-describedby="amount-error"
                            />
                        </div>
                        @error('amount')
                            <p id="amount-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="slotsOccupied" class="block text-sm font-medium text-gray-700">Caselle Occupate</label>
                        <select id="slotsOccupied"
                                wire:model.live="slotsOccupied"
                                required
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition cursor-pointer"
                                aria-describedby="slots-error">
                            <option value="" disabled>Seleziona...</option>
                            <option value="1">1 Casella</option>
                            <option value="2">2 Caselle</option>
                            <option value="3">3 Caselle</option>
                            <option value="4">4 Caselle</option>
                        </select>
                        @error('slotsOccupied')
                            <p id="slots-error" class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-3 pt-2">

                        {{-- Checkbox 1: Fisso/Escluso --}}
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input
                                    id="excluded"
                                    type="checkbox"
                                    x-model="excluded"
                                    @change="if(excluded) sharedFromFirst = false"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="excluded" class="font-medium text-gray-700 cursor-pointer">Fisso alla licenza</label>
                                <p class="text-gray-500 text-xs">Non conta nel riepilogo</p>
                            </div>
                        </div>

                        {{-- Checkbox 2: Ripartisci dal primo --}}
                        <div class="flex items-start">
                            <div class="flex h-5 items-center">
                                <input
                                    id="sharedFromFirst"
                                    type="checkbox"
                                    x-model="sharedFromFirst"
                                    @change="if(sharedFromFirst) excluded = false"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="sharedFromFirst" class="font-medium text-gray-700 cursor-pointer">Ripartisci dal primo</label>
                                <p class="text-gray-500 text-xs">Dalla prima licenza</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-4 mt-auto border-t border-gray-100">
                        <button type="button"
                                wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Annulla
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Conferma
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

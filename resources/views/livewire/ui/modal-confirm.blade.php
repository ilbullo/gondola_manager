{{-- resources/views/livewire/modals/confirm-modal.blade.php --}}
<div>
    <div
        x-data="{ open: @entangle('show') }"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="$wire.cancel()"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="confirm-modal-title"
        role="dialog"
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            @click="$wire.cancel()"
        ></div>

        <!-- Pannello modale -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5"
                @click.stop
            >
                <div class="bg-white px-6 pb-8 pt-10 sm:px-10">
                    <!-- Icona di avviso -->
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-100 mb-6">
                        <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Titolo e messaggio -->
                    <h3 id="confirm-modal-title" class="text-2xl font-bold text-gray-900 mb-3">
                        Attenzione
                    </h3>
                    <p class="text-gray-600 text-base leading-relaxed">
                        {{ $message ?? 'Sei sicuro di voler procedere con questa azione?' }}
                    </p>

                    <!-- Pulsanti -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-end">
                        <button
                            type="button"
                            wire:click="cancel"
                            class="order-2 sm:order-1 w-full sm:w-auto px-6 py-3 text-sm font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-xl focus:ring-4 focus:ring-gray-300 transition-all duration-200"
                        >
                            Annulla
                        </button>

                        <button
                            type="button"
                            wire:click="confirm"
                            class="order-1 sm:order-2 w-full sm:w-auto px-8 py-3 text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl shadow-lg hover:shadow-xl focus:ring-4 focus:ring-red-500/50 transition-all duration-200 flex items-center justify-center gap-2"
                        >
                            Conferma
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
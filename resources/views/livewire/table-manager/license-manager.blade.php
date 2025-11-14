<div class="min-h-screen bg-gray-50 p-3 sm:p-4">
    <!-- Componente modale di caricamento -->
    <livewire:ui.loading-overlay />

    <!-- Messaggi -->
    @if (session('success'))
        <div class="mb-3 p-3 bg-green-100 text-green-700 rounded-lg shadow-sm text-sm sm:text-base">
            {{ session('success') }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="mb-3 p-3 bg-red-100 text-red-700 rounded-lg shadow-sm text-sm sm:text-base">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Layout a due colonne con proporzioni 3/4 (sinistra) e 1/4 (destra) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 sm:gap-4 h-[calc(100vh-7rem)]">
        <!-- Sezione sinistra: Utenti disponibili (3/4, multi-colonne) -->
        <div class="md:col-span-3 bg-white p-3 sm:p-4 rounded-lg shadow-md overflow-y-auto">
            <h2 class="text-base sm:text-lg font-semibold mb-3 text-gray-800">{{__('licenses at disposition')}}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3">
                @forelse ($availableUsers as $user)
                    <button
                        wire:click="selectUser({{ $user['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="selectUser({{ $user['id'] }})"
                        class="p-2 sm:p-3 bg-blue-50 hover:bg-blue-100 active:bg-blue-200 rounded-lg flex flex-col items-start transition-colors focus:outline-none focus:ring-2 focus:ring-blue-300 disabled:opacity-50"
                    >
                        <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight">{{ $user['license_number'] ?? '' }}</p>
                        <p class="text-xs sm:text-sm text-gray-600 leading-tight">{{ $user['name'] }} {{ $user['surname'] ?? '' }}</p>
                    </button>
                @empty
                    <p class="text-gray-500 text-center py-4 col-span-full text-sm">Nessun utente disponibile.</p>
                @endforelse
            </div>
        </div>

        <!-- Sezione destra: Utenti selezionati (1/4, solo licenza) -->
        <div class="md:col-span-1 bg-white p-3 sm:p-4 rounded-lg shadow-md overflow-y-auto">
            <h2 class="text-base sm:text-lg font-semibold mb-3 text-gray-800">{{__('table')}}</h2>
            <div wire:sortable="updateOrder" class="space-y-2 sm:space-y-3">
                @forelse ($selectedUsers as $item)
                    <div
                        wire:sortable.item="{{ $item['id'] }}"
                        wire:key="selected-{{ $item['id'] }}"
                        class="p-2 sm:p-3 bg-green-50 rounded-lg flex items-center justify-between cursor-move select-none hover:bg-green-100 transition-colors"
                    >
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <svg wire:sortable.handle class="w-5 h-5 sm:w-6 sm:h-6 text-gray-500 cursor-grab active:cursor-grabbing" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                            </svg>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">{{ $item['user']['license'] }}</p>
                        </div>
                        <button
                            wire:click="removeUser({{ $item['id'] }})"
                            wire:loading.attr="disabled"
                            wire:target="removeUser({{ $item['id'] }})"
                            class="text-red-600 hover:text-red-800 active:text-red-900"
                        >
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4 text-sm">Nessun utente selezionato.</p>
                @endforelse
            </div>

            <!-- Bottone Conferma -->
            <div class="mt-3 sm:mt-4">
                <button
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                    wire:target="confirm"
                    class="w-full p-2 sm:p-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:bg-blue-800 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors disabled:opacity-50 text-sm sm:text-base font-semibold"
                >
                    <span>Conferma</span>
                </button>
            </div>
        </div>
    </div>
</div>

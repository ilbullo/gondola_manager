{{-- resources/views/livewire/table-manager/license-manager.blade.php --}}
<div class="min-h-screen bg-gray-100 py-4 px-3 sm:px-4">
    <livewire:ui.loading-overlay />

    <!-- Messaggi di stato (accessibili) -->
    @if (session('success'))
        @include('components.sessionMessage', ['message' => session('success')])
    @endif

    @if ($errorMessage)
        <div
            role="alert"
            aria-live="assertive"
            class="max-w-5xl mx-auto mb-4 px-4 py-3 bg-red-100 border-l-4 border-red-600 text-red-800 rounded-lg shadow-sm text-sm font-medium"
        >
            {{ $errorMessage }}
        </div>
    @endif

    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-110px)]">

        <!-- SINISTRA: Licenze disponibili -->
        <section
            aria-labelledby="available-licenses-heading"
            class="lg:col-span-8 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex flex-col"
        >
            <header class="bg-blue-600 text-white px-5 py-4 flex items-center justify-between">
                <h2 id="available-licenses-heading" class="text-lg font-bold">
                    Licenze disponibili
                </h2>
                <span
                    aria-live="polite"
                    class="bg-white/20 px-4 py-1.5 rounded-full text-sm font-medium backdrop-blur-sm"
                >
                    {{ count($availableUsers) }}
                </span>
            </header>

            <div class="flex-1 overflow-y-auto p-4" role="region" aria-label="Elenco licenze disponibili">
                @if(empty($availableUsers))
                    <p class="text-center text-gray-500 py-12 text-sm">
                        Nessuna licenza disponibile al momento.
                    </p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                        @foreach($availableUsers as $user)
                            <button
                                type="button"
                                wire:click="selectUser({{ $user['id'] }})"
                                wire:loading.attr="disabled"
                                wire:key="available-{{ $user['id'] }}"
                                class="group flex flex-col items-center justify-center p-4 border-2 rounded-xl transition-all
                                       focus:outline-none focus:ring-4 focus:ring-blue-300 focus:border-blue-500
                                       @if($user['license_number'] < 450)
                                           bg-blue-50 hover:bg-blue-100 border-blue-300 hover:border-blue-500
                                       @else
                                           bg-amber-50 hover:bg-amber-100 border-amber-300 hover:border-amber-500
                                       @endif
                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                aria-label="Aggiungi {{ $user['name'] }} {{ $user['surname'] ?? '' }} (licenza {{ $user['license_number'] }}) alla tabella"
                            >
                                <div class="font-black text-2xl leading-none text-blue-900">
                                    {{ $user['license_number'] }}
                                </div>
                                <div class="mt-2 text-xs text-gray-700 leading-tight text-center">
                                    {{ Str::limit($user['name'] . ' ' . ($user['surname'] ?? ''), 20) }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- DESTRA: Tabella del giorno (drag & drop accessibile) -->
        <aside
            aria-labelledby="daily-table-heading"
            class="lg:col-span-4 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex flex-col"
        >
            <header class="bg-emerald-600 text-white px-5 py-4 flex items-center justify-between">
                <h2 id="daily-table-heading" class="text-lg font-bold">
                    Tabella del giorno
                </h2>
                <span
                    aria-live="polite"
                    class="ml-auto bg-white/20 px-4 py-1.5 rounded-full text-sm font-medium backdrop-blur-sm"
                >
                    {{ count($selectedUsers) }}/25
                </span>
            </header>

            <!-- Lista ordinabile con wire:sortable (mantenuta identica, ma resa accessibile) -->
            <div
                wire:sortable="updateOrder"
                wire:sortable.options='{
                    "animation": 180,
                    "handle": ".sortable-handle",
                    "ghostClass": "bg-emerald-200 opacity-70",
                    "chosenClass": "ring-4 ring-emerald-400 ring-offset-2",
                    "forceFallback": true
                }'
                class="flex-1 overflow-y-auto px-4 py-3 space-y-3"
                role="region"
                aria-label="Tabella del giorno – trascina per riordinare"
            >
                @forelse($selectedUsers as $item)
                    <div
                        wire:sortable.item="{{ $item['id'] }}"
                        wire:key="selected-{{ $item['id'] }}"
                        role="listitem"
                        class="bg-emerald-50 border border-emerald-300 rounded-xl px-4 py-3 flex items-center justify-between
                               cursor-move hover:bg-emerald-100 focus-within:ring-4 focus-within:ring-emerald-300
                               transition select-none group"
                        tabindex="0"
                        aria-label="Licenza {{ $item['user']['license'] }} – trascina per spostare"
                    >
                        <div class="flex items-center gap-4">
                            <!-- Handle per il drag (accessibile da tastiera grazie a SortableJS fallback) -->
                            <div
                                class="sortable-handle cursor-grab active:cursor-grabbing text-gray-500 focus:outline-none"
                                aria-hidden="true"
                                title="Trascina per riordinare"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>

                            <span class="font-black text-2xl text-emerald-800">
                                {{ $item['user']['license'] }}
                            </span>
                        </div>

                        <!-- Bottone rimuovi (visibile al focus e hover) -->
                        <button
                            type="button"
                            wire:click="removeUser({{ $item['id'] }})"
                            class="opacity-0 group-hover:opacity-100 group-focus-within:opacity-100
                                   text-red-600 hover:bg-red-100 p-2 rounded-lg transition
                                   focus:outline-none focus:ring-2 focus:ring-red-400"
                            aria-label="Rimuovi licenza {{ $item['user']['license'] }} dalla tabella"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @empty
                    <p class="text-center text-gray-500 py-12 text-sm">
                        Nessuna licenza assegnata.<br>
                        Trascina una licenza dalla lista a sinistra.
                    </p>
                @endforelse
            </div>

            <!-- Pulsante Conferma -->
            <div class="p-4 border-t bg-gray-50">
                <button
                    type="button"
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400
                           text-white font-bold text-base rounded-xl shadow-lg transition
                           focus:outline-none focus:ring-4 focus:ring-blue-300"
                    aria-label="Conferma la tabella del giorno"
                >
                    <span wire:loading.remove wire:target="confirm">Conferma Tabella</span>
                </button>
            </div>
        </aside>
    </div>
</div>
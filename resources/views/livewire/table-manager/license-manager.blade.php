{{-- resources/views/livewire/license-assignment.blade.php --}}
<div class="min-h-screen bg-gray-100 py-4 px-3 sm:px-4">
    <livewire:ui.loading-overlay />

    <!-- Messaggi flash (molto compatti) -->
    @if (session('success'))
        <div class="max-w-5xl mx-auto mb-3 px-4 py-2 bg-green-100 text-green-800 text-sm font-medium rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if ($errorMessage)
        <div class="max-w-5xl mx-auto mb-3 px-4 py-2 bg-red-100 text-red-800 text-sm font-medium rounded-lg">
            {{ $errorMessage }}
        </div>
    @endif

    <!-- Layout compatto: 70/30 su tablet/desktop, stacked su mobile -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-4 h-[calc(100vh-110px)]">

        <!-- SINISTRA: Licenze disponibili -->
        <section class="lg:col-span-8 bg-white rounded-xl shadow overflow-hidden flex flex-col">
            <div class="bg-blue-600 text-white px-4 py-2.5 text-sm font-bold flex items-center justify-between">
                <span>Licenze disponibili</span>
                <span class="bg-white/25 px-3 py-1 rounded-full text-xs">{{ count($availableUsers) }}</span>
            </div>

            <div class="flex-1 overflow-y-auto p-3">
                @if(empty($availableUsers))
                    <p class="text-center text-gray-500 text-sm py-10">Nessuna licenza disponibile</p>
                @else
                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2.5">
                        @foreach($availableUsers as $user)
                            <button
                                wire:click="selectUser({{ $user['id'] }})"
                                wire:loading.attr="disabled"
                                class="p-3 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 hover:border-blue-400 transition text-center text-xs group"
                            >
                                <div class="font-black text-blue-900 text-lg leading-none">
                                    {{ $user['license_number'] }}
                                </div>
                                <div class="text-gray-700 mt-1 text-[10px] leading-tight">
                                    {{ Str::limit($user['name'] . ' ' . ($user['surname'] ?? ''), 14) }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <!-- DESTRA: Tabella del giorno -->
        <aside class="lg:col-span-4 bg-white rounded-xl shadow overflow-hidden flex flex-col">
            <div class="bg-emerald-600 text-white px-4 py-2.5 text-sm font-bold flex items-center justify-between">
                <span>Tabella del giorno</span>
                <span class="bg-white/25 px-3 py-1 rounded-full text-xs">{{ count($selectedUsers) }}/25</span>
            </div>

            <!-- Lista ordinabile (100% stabile) -->
            <div wire:sortable="updateOrder" class="flex-1 overflow-y-auto px-3 py-2 space-y-2">
                @forelse($selectedUsers as $item)
                    <div
                        wire:sortable.item="{{ $item['id'] }}"
                        wire:key="sel-{{ $item['id'] }}-{{ $loop->index }}"
                        class="bg-emerald-50 border border-emerald-300 rounded-lg px-3 py-2.5 flex items-center justify-between cursor-move hover:bg-emerald-100 transition group text-sm"
                    >
                        <div class="flex items-center gap-3">
                            <div wire:sortable.handle class="cursor-grab active:cursor-grabbing">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 8h16M4 16h16"/>
                                </svg>
                            </div>
                            <span class="font-black text-emerald-800 text-xl">
                                {{ $item['user']['license'] }}
                            </span>
                        </div>
                        <button
                            wire:click="removeUser({{ $item['id'] }})"
                            class="opacity-0 group-hover:opacity-100 text-red-600 hover:bg-red-100 p-1.5 rounded transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @empty
                    <p class="text-center text-gray-500 text-sm py-10">Nessuna licenza assegnata</p>
                @endforelse
            </div>

            <!-- Conferma -->
            <div class="p-3 border-t bg-gray-50">
                <button
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-lg transition shadow"
                >
                    <span  wire:target="confirm">Conferma Tabella</span>
                </button>
            </div>
        </aside>
    </div>
</div>

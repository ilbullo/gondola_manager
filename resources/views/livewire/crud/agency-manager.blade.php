{{-- resources/views/livewire/crud/agency-manager.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Gestione Agenzie') }}</h1>
        <p class="mt-2 text-gray-600">Aggiungi, modifica o elimina le agenzie del sistema.</p>
    </div>

    <!-- Messaggio di successo -->
    @if (session('message'))
        @include('components.sessionMessage', ['message' => session('message')])
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none" aria-hidden="true">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="search" wire:model.live.debounce.300ms="search"
                    placeholder="Cerca per nome o codice..."
                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition duration-150">
            </div>

            <div class="flex items-center gap-4">

                <button wire:click="toggleShowDeleted"
                    class="px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition shadow-sm flex items-center gap-2 border border-gray-300">
                    @if ($showDeleted)
                        Nascondi eliminate
                    @else
                        Mostra eliminate
                    @endif
                </button>

                <button wire:click="toggleCreateForm"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuova Agenzia
                </button>
            </div>
        </div>
    </div>


    @if ($showCreateForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-data="{ show: @entangle('showCreateForm') }" x-show="show" x-transition.opacity x-on:keydown.escape.window="$wire.closeForms()"
            x-on:click="$wire.closeForms()" style="display: none;" role="dialog" aria-modal="true"
            aria-labelledby="create-modal-title" x-init="$nextTick(() => document.getElementById('create-name').focus())">
            <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden" x-on:click.stop
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-end="scale-95 opacity-0" x-trap.noscroll.inert="show">
                <form wire:submit="create" class="p-0">

                    {{-- Intestazione --}}
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 id="create-modal-title" class="text-2xl font-bold text-white">Crea Nuova Agenzia</h3>
                    </div>

                    {{-- Corpo del Form --}}
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="create-name" class="block text-sm font-medium text-gray-700">Nome
                                Agenzia</label>
                            <input type="text" id="create-name" wire:model="name"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm" required>
                            @error('name')
                                <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="create-code" class="block text-sm font-medium text-gray-700">Codice (max 10
                                caratteri)</label>
                            <input type="text" id="create-code" wire:model="code" maxlength="10"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm uppercase tracking-wider font-mono"
                                required>
                            @error('code')
                                <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                        <button type="button" wire:click="closeForms"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                            Annulla
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md"
                            wire:loading.attr="disabled">
                            Crea Agenzia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    @if ($showEditForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-data="{ show: @entangle('showEditForm') }" x-show="show" x-transition.opacity x-on:keydown.escape.window="$wire.closeForms()"
            x-on:click="$wire.closeForms()" style="display: none;" role="dialog" aria-modal="true"
            aria-labelledby="edit-modal-title" x-init="$nextTick(() => document.getElementById('edit-name').focus())">
            <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden" x-on:click.stop
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-end="scale-95 opacity-0" x-trap.noscroll.inert="show">
                <form wire:submit="update" class="p-0">

                    {{-- Intestazione --}}
                    <div class="bg-blue-600 px-6 py-4">
                        <h3 id="edit-modal-title" class="text-2xl font-bold text-white">Modifica Agenzia</h3>
                    </div>

                    {{-- Corpo del Form --}}
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="edit-name" class="block text-sm font-medium text-gray-700">Nome
                                Agenzia</label>
                            <input type="text" id="edit-name" wire:model="name"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm" required>
                            @error('name')
                                <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="edit-code" class="block text-sm font-medium text-gray-700">Codice (max 10
                                caratteri)</label>
                            <input type="text" id="edit-code" wire:model="code" maxlength="10"
                                class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm uppercase tracking-wider font-mono"
                                required>
                            @error('code')
                                <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                        <button type="button" wire:click="closeForms"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                            Annulla
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md"
                            wire:loading.attr="disabled">
                            Aggiorna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <caption class="sr-only">Tabella di gestione e visualizzazione delle agenzie registrate.</caption>
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nome
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Codice
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Azioni</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($agencies as $agency)
                        <tr
                            class="{{ $agency->trashed() ? 'bg-red-50 opacity-75' : 'hover:bg-gray-50' }} transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $agency->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">{{ $agency->code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-4">
                                    @if ($agency->trashed())
                                        <button wire:click="restore({{ $agency->id }})"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg font-medium transition">
                                            Ripristina
                                        </button>
                                    @else
                                        <button wire:click="edit({{ $agency->id }})"
                                            class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                            Modifica <span class="sr-only">agenzia {{ $agency->name }}</span>
                                        </button>
                                        <button wire:click="confirmDelete({{ $agency->id }})"
                                            class="text-red-600 hover:text-red-800 font-medium flex items-center gap-1">
                                            Elimina <span class="sr-only">agenzia {{ $agency->name }}</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                Nessuna agenzia trovata.
                                @if ($showDeleted)
                                    (anche tra le eliminate)
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $agencies->links() }}
        </div>
    </div>

    @push('modals')
        <livewire:ui.modal-confirm />
    @endpush
</div>

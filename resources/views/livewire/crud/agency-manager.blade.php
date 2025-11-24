{{-- resources/views/livewire/crud/agency-manager.blade.php --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Gestione Agenzie') }}</h1>
        <p class="mt-2 text-gray-600">Aggiungi, modifica o elimina le agenzie del sistema.</p>
    </div>

    <!-- Messaggio di successo -->
    @if (session('message'))
        @include('components.sessionMessage',["message" => session('message')])
    @endif

    <!-- Barra di controllo -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

            <!-- Ricerca -->
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cerca per nome o codice..."
                    class="block w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-900 placeholder-gray-500" />
            </div>

            <!-- Pulsanti azioni -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button wire:click="toggleCreateForm"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                    @if ($showCreateForm)
                        Annulla
                    @else
                        Nuova Agenzia
                    @endif
                </button>

                <button wire:click="toggleShowDeleted"
                    class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center gap-2">
                    @if ($showDeleted)
                        Nascondi eliminate
                    @else
                        Mostra eliminate
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Form Creazione -->
    <div x-data="{ open: @entangle('showCreateForm') }" x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-4"
        class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Crea Nuova Agenzia</h2>

            <form wire:submit="create" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nome Agenzia</label>
                    <input wire:model="name" type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    @error('name')
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Codice (max 10 caratteri)</label>
                    <input wire:model="code" type="text" maxlength="10"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all uppercase tracking-wider font-mono">
                    @error('code')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>

                <div class="md:col-span-2 flex justify-end">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Crea Agenzia
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Modifica -->
    <div x-data="{ open: @entangle('showEditForm') }" x-show="open" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Modifica Agenzia</h2>

            <form wire:submit="update" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                    <input wire:model="name" type="text"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    @error('name')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Codice</label>
                    <input wire:model="code" type="text" maxlength="10"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all uppercase tracking-wider font-mono">
                    @error('code')
                        <x-input-error :messages="$message" class="mt-2" />
                    @enderror
                </div>

                <div class="md:col-span-2 flex justify-end gap-4">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Aggiorna
                    </button>
                    <button type="button" wire:click="closeForms"
                        class="px-8 py-3 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Annulla
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabella -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nome
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Codice
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Azioni
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($agencies as $agency)
                        <tr
                            class="{{ $agency->trashed() ? 'bg-red-50 opacity-75' : 'hover:bg-gray-50' }} transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $agency->name }}</td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ $agency->code }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if ($agency->trashed())
                                    <button wire:click="restore({{ $agency->id }})"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded-lg font-medium transition">
                                        Ripristina
                                    </button>
                                @else
                                    <div class="flex items-center gap-4">
                                        <button wire:click="edit({{ $agency->id }})"
                                            class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                            Modifica
                                        </button>
                                        <button wire:click="confirmDelete({{ $agency->id }})"
                                            class="text-red-600 hover:text-red-800 font-medium flex items-center gap-1">
                                            Elimina
                                        </button>
                                    </div>
                                @endif
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

        <!-- Paginazione -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $agencies->links() }}
        </div>
    </div>
    @push('modals')
    <livewire:ui.modal-confirm />
    @endpush
</div>

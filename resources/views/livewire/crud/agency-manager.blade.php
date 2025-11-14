<!-- resources/views/livewire/crud/agency-manager.blade.php -->
<div class="max-w-5xl mx-auto p-4 sm:p-6 md:p-8">
    <h1 class="text-2xl md:text-3xl font-bold mb-6 text-gray-800">Gestione Agenzie</h1>

    <!-- Messaggi di successo -->
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- Barra di ricerca e pulsanti -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <!-- Campo di ricerca -->
        <div class="relative w-full md:w-2/5">
            <input wire:model.live="search" type="text" placeholder="Cerca per nome o codice..."
                class="w-full pl-10 pr-4 py-2.5 border-gray-200 rounded-lg shadow-sm focus:ring-blue-400 focus:border-blue-400 bg-white text-gray-900 placeholder-gray-400">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400"
                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <!-- Pulsanti -->
        <div class="flex flex-col md:flex-row md:space-x-3 space-y-3 md:space-y-0">
            <button wire:click="toggleCreateForm"
                class="bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition duration-200 text-sm font-medium">
                {{ $showCreateForm ? 'Annulla' : 'Crea Nuova Agenzia' }}
            </button>
            <button wire:click="toggleShowDeleted"
                class="bg-gray-600 text-white px-4 py-2.5 rounded-lg hover:bg-gray-700 transition duration-200 text-sm font-medium">
                {{ $showDeleted ? 'Nascondi Eliminati' : 'Mostra Eliminati' }}
            </button>
        </div>
    </div>

    <!-- Form di creazione -->
    @if ($showCreateForm)
        <div class="bg-white shadow-lg rounded-lg p-4 md:p-6 mb-6 border border-gray-100">
            <h2 class="text-xl md:text-2xl font-semibold mb-4 text-gray-800">Crea Agenzia</h2>
            <form wire:submit.prevent="create" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input wire:model="name" type="text" id="name"
                        class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-blue-400 focus:border-blue-400 py-2 px-3">
                    @error('name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Codice</label>
                    <input wire:model="code" type="text" id="code"
                        class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-blue-400 focus:border-blue-400 py-2 px-3">
                    @error('code')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2.5 rounded-lg hover:bg-green-700 transition duration-200 text-sm font-medium">
                        Salva
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Form di modifica -->
    @if ($showEditForm)
        <div class="bg-white shadow-lg rounded-lg p-4 md:p-6 mb-6 border border-gray-100">
            <h2 class="text-xl md:text-2xl font-semibold mb-4 text-gray-800">Modifica Agenzia</h2>
            <form wire:submit.prevent="update" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input wire:model="name" type="text" id="edit_name"
                        class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-blue-400 focus:border-blue-400 py-2 px-3">
                    @error('name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="edit_code" class="block text-sm font-medium text-gray-700 mb-1">Codice</label>
                    <input wire:model="code" type="text" id="edit_code"
                        class="block w-full border-gray-200 rounded-lg shadow-sm focus:ring-blue-400 focus:border-blue-400 py-2 px-3">
                    @error('code')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>
                <div class="md:col-span-2 flex justify-end space-x-3">
                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2.5 rounded-lg hover:bg-green-700 transition duration-200 text-sm font-medium">
                        Aggiorna
                    </button>
                    <button wire:click="resetForm"
                        class="bg-gray-600 text-white px-4 py-2.5 rounded-lg hover:bg-gray-700 transition duration-200 text-sm font-medium">
                        Annulla
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Tabella dei record -->
    <div class="bg-white shadow-lg rounded-lg p-4 md:p-6 border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider md:px-6">
                            Nome</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider md:px-6">
                            Codice</th>
                        <th
                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider md:px-6">
                            Azioni</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($agencies as $agency)
                        <tr class="{{ $agency->trashed() ? 'bg-gray-100' : '' }}">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 md:px-6">{{ $agency->name }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 md:px-6">{{ $agency->code }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm md:px-6">
                                @if ($agency->trashed())
                                    <button wire:click="restore({{ $agency->id }})"
                                        class="text-green-600 hover:text-green-900 flex items-center text-sm">
                                        <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Ripristina
                                    </button>
                                @else
                                    <div class="flex flex-col md:flex-row md:space-x-3 space-y-2 md:space-y-0">
                                        <button wire:click="edit({{ $agency->id }})"
                                            class="text-blue-600 hover:text-blue-900 flex items-center text-sm">
                                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15.414a2 2 0 01-2.828 0l-1.414-1.414a2 2 0 010-2.828z" />
                                            </svg>
                                            Modifica
                                        </button>
                                       <button wire:click="confirmDelete({{ $agency->id }})"class="text-red-600 hover:text-red-900 flex items-center text-sm">
                                            <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0h4m-7 4h12" />
                                            </svg>
                                            Elimina
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500 md:px-6">Nessuna
                                agenzia trovata.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginazione -->
        <div class="mt-6">
            {{ $agencies->links() }}
        </div>
    </div>
</div>

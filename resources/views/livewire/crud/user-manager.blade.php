{{-- resources/views/livewire/crud/user-manager.blade.php (WCAG/ARIA Compliant) --}}
<div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ __('Gestione Utenti') }}</h1>
        <p class="mt-2 text-gray-600">Aggiungi, modifica o elimina gli utenti del sistema.</p>
    </div>

     <!-- Messaggio di successo -->
    @if (session('message'))
        @include('components.sessionMessage',["message" => session('message')])
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none" aria-hidden="true">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="Cerca per nome o email..."
                       class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition duration-150">
            </div>

            <div class="flex items-center gap-4">
                <button wire:click="create"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuovo Utente
                </button>
            </div>
        </div>
    </div>


    @if($editing)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-data="{ show: @entangle('editing') }"
        x-show="show"
        x-transition.opacity
        x-on:keydown.escape.window="$wire.resetForm()"
        x-on:click="$wire.resetForm()"
        style="display: none;"

        {{-- WCAG: Ruolo modale per gli screen reader --}}
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-title"
        aria-describedby="user-form"

        {{-- WCAG: Focus Trapping e Focus Iniziale --}}
        x-init="$nextTick(() => document.getElementById('name').focus())"
    >
        <div
            class="w-full max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden"
            x-on:click.stop
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-end="scale-95 opacity-0"

            {{-- WCAG: Assicura che la tabulazione rimanga all'interno della modale --}}
            x-trap.noscroll.inert="show"
        >
            <form wire:submit="save" class="p-0" id="user-form">

                {{-- Intestazione --}}
                <div class="bg-blue-600 px-6 py-4">
                    {{-- WCAG: Associa il titolo principale al div modale --}}
                    <h3 id="modal-title" class="text-2xl font-bold text-white">{{ $userId ? 'Modifica Utente' : 'Crea Nuovo Utente' }}</h3>
                </div>

                {{-- Corpo del Form --}}
                <div class="p-6 space-y-4">
                    <div>
                        {{-- WCAG: Etichetta esplicita collegata a 'name' --}}
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome e Cognome</label>
                        <input type="text" id="name" wire:model="name" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm" required autofocus>
                        @error('name') <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" wire:model="email" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm" required>
                        @error('email') <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password @if($userId)(Lascia vuoto per non cambiare)@else * @endif
                        </label>
                        <input type="password" id="password" wire:model="password" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm">
                        @error('password') <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Ruolo</label>
                            <select id="role" wire:model="role" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm">
                                @foreach($roles as $roleEnum)
                                    <option value="{{ $roleEnum->value }}">{{ $roleEnum->label() }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipo Licenza</label>
                            <select id="type" wire:model="type" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm">
                                <option value="">Nessun Tipo (Opzionale)</option>
                                @foreach($licenseTypes as $typeEnum)
                                    <option value="{{ $typeEnum->value }}">{{ $typeEnum->label() }}</option>
                                @endforeach
                            </select>
                            @error('type') <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700">Numero Licenza</label>
                        <input type="text" id="license_number" wire:model="license_number" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm">
                        @error('license_number') <span class="text-red-500 text-xs" aria-live="polite">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
                    <button type="button" wire:click="resetForm"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-md"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        {{ $userId ? 'Salva Modifiche' : 'Crea Utente' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            {{-- WCAG: Aggiunta di caption per descrivere la tabella --}}
            <table class="min-w-full divide-y divide-gray-200">
                <caption class="sr-only">Tabella di gestione e visualizzazione degli utenti registrati nel sistema.</caption>
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer"
                            wire:click="setSort('name')" aria-sort="{{ $sortField === 'name' ? $sortDirection : 'none' }}">
                            Nome
                            @if ($sortField === 'name')
                                <span class="ml-1" aria-hidden="true">
                                    @if ($sortDirection === 'asc') &uarr; @else &darr; @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider cursor-pointer"
                            wire:click="setSort('email')" aria-sort="{{ $sortField === 'email' ? $sortDirection : 'none' }}">
                            Email
                            @if ($sortField === 'email')
                                <span class="ml-1" aria-hidden="true">
                                    @if ($sortDirection === 'asc') &uarr; @else &darr; @endif
                                </span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Ruolo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                            Licenza
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Azioni</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50">
                            <th scope="row" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $user->name }}
                            </th>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($user->isAdmin()) bg-red-100 text-red-800
                                    @elseif($user->isBancale()) bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $user->role->label() ?? 'N/D' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->license_number ?? '—' }} ({{ $user->type->label() ?? 'N/D' }})
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-4">
                                    {{-- WCAG: Il testo del bottone è chiaro (Modifica) --}}
                                    <button wire:click="edit({{ $user->id }})"
                                            class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
                                        Modifica <span class="sr-only">utente {{ $user->name }}</span>
                                    </button>
                                    <button
                                            wire:click="confirmDelete({{ $user->id }})"
                                            class="text-red-600 hover:text-red-800 font-medium flex items-center gap-1">
                                        Elimina <span class="sr-only">utente {{ $user->name }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Nessun utente trovato.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
     @push('modals')
    <livewire:ui.modal-confirm />
    @endpush
</div>

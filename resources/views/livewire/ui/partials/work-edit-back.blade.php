{{-- resources/views/livewire/ui/partials/work-edit-back.blade.php --}}
<div class="h-full flex flex-col bg-white">

    <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
        <h3 class="text-2xl font-bold text-white">Modifica Lavoro</h3>
        <button @click="flipped = false" class="text-white/80 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-white/50 rounded">
            <span class="sr-only">Indietro</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-4">

        @if (session('message'))
            <div role="status" class="mb-4 p-3 bg-green-100 text-green-800 border-l-4 border-green-500 rounded text-sm font-medium">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tipo Lavoro</label>
                <select wire:model.live="value"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    @foreach(\App\Enums\WorkType::cases() as $type)
                        @if(!in_array($type->value, [\App\Enums\WorkType::FIXED->value, \App\Enums\WorkType::EXCLUDED->value]))
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Importo (€)</label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-500 sm:text-sm">€</span>
                    </div>
                    <input type="number" step="0.01" wire:model.live="amount" placeholder="0.00"
                           class="block w-full rounded-lg border-gray-300 pl-7 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
            </div>
        </div>

        @if($value === 'A')
            <div>
                <label class="block text-sm font-medium text-gray-700">Agenzia</label>
                <select wire:model.live="agency_code"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    <option value="">Seleziona Agenzia</option>
                    @foreach(\App\Models\Agency::select(['name','code'])->orderBy('name')->get() ?? [] as $agency)
                        <option value="{{ $agency['code'] }}">{{ $agency['name'] }} ({{ $agency['code'] }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700">Voucher / Nota</label>
            <input type="text" wire:model.live="voucher" placeholder="Facoltativo..."
                   class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        </div>
        
        @if($work->isAgency())
            <div class="pt-2 space-y-3">
                <div class="flex items-start">
                    <div class="flex h-5 items-center">
                        <input id="excluded" type="checkbox" x-model="excluded"
                            @change="if($event.target.checked) shared_from_first = false"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="excluded" class="font-medium text-gray-700">Escluso da ripartizione</label>
                        <p class="text-gray-500 text-xs">Il lavoro non verrà conteggiato nella ripartizione.</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="flex h-5 items-center">
                        <input id="shared_from_first" type="checkbox" x-model="shared_from_first"
                            @change="if($event.target.checked) excluded = false"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="shared_from_first" class="font-medium text-gray-700">Ripartito dal primo</label>
                        <p class="text-gray-500 text-xs">Assegnazione prioritaria dalla prima licenza.</p>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
        <button @click="flipped = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition">
            Annulla
        </button>
        <button wire:click="save"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-md transition"
                wire:loading.attr="disabled">
            Salva Modifiche
        </button>
    </div>
</div>

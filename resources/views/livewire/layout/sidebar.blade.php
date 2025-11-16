<div id="sidebar"
    class="fixed inset-y-0 left-0 w-48 md:w-56 bg-white shadow-md shadow-blue-500/10 p-2 transform -translate-x-full lg:static lg:translate-x-0 lg:w-56 transition-transform duration-300 lg:flex lg:flex-col overflow-y-auto z-30">
    @if (session('success'))
        <div id="successMessage"
            class="flex items-center gap-1 p-2 bg-gradient-to-r from-emerald-500 to-lime-500 rounded-md shadow-sm shadow-emerald-500/40">
            <span class="text-sm font-bold text-white">✓ {{ session('success') }}</span>
        </div>
    @endif

    <div class="space-y-2">
        <label class="block text-sm md:text-base font-bold text-gray-800 border-l-4 border-blue-500 pl-2">
            {{ __('work type') }}
        </label>
        <div class="grid gap-1.5">
            @foreach ($config['work_types'] as $button)
                <button id="{{ $button['id'] }}" wire:click="setWorkType('{{ $button['value'] }}')"
                    class="h-10 px-3 text-sm font-bold {{ $button['classes'] }} rounded-md shadow-sm focus:ring-2 focus:outline-none transition-all duration-200">
                    {{ $button['label'] }}
                </button>
            @endforeach
        </div>
        @if ($label)
            <div id="currentSelection"
                class="bg-gray-200 border-2 border-gray-500 rounded-md p-1.5 text-xs font-extrabold text-center">
                Selezione: <span id="selectionText">{{ $label }}{{ $workType === 'A' && $agencyName ? ' - ' . $agencyName : '' }}</span>
            </div>
        @endif
    </div>

    @if ($config['sections']['notes']['enabled'])
        <div class="mt-2 space-y-1.5">
            <label for="agencyNotes" class="block text-sm md:text-base font-bold text-gray-800 border-l-4 {{ $config['sections']['notes']['border_color'] }} pl-2">
                {{ $config['sections']['notes']['label'] }}
            </label>
            <input id="agencyNotes" type="text" placeholder="{{ $config['sections']['notes']['placeholder'] }}" wire:model.live="voucher"
                class="w-full h-10 px-2 text-sm font-medium text-gray-900 bg-white border-2 {{ $config['sections']['notes']['input_border'] }} rounded-md placeholder:text-gray-500 placeholder:font-medium focus:ring-2 focus:outline-none transition-all duration-200" />
        </div>
    @endif

    <div class="mt-2 flex items-center gap-1.5">
        <input id="sharedFromFirst" type="checkbox" wire:model.live="sharedFromFirst"
            class="h-4 w-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-300 focus:outline-none" />
        <label class="text-sm font-bold text-gray-800">
            RIPARTISCI DAL PRIMO 
        </label>
    </div>

    <div class="mt-2 grid gap-1.5">
        <button wire:click="openWorkDetailsModal"
            class="h-10 px-3 uppercase text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-300 shadow-blue-500/40 rounded-md shadow-sm focus:ring-2 focus:outline-none transition-all duration-200">
            Configura Lavoro
        </button>
        <!-- Nuovo tasto per toggle azioni -->
        <button wire:click="toggleActions"
            class="h-10 px-3 uppercase text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-300 shadow-blue-500/40 rounded-md shadow-sm focus:ring-2 focus:outline-none transition-all duration-200">
            Opzioni Tabella
        </button>

        <!-- Pulsanti azioni nascosti, mostrati con toggle -->
        <div x-data="{ show: @entangle('showActions') }" x-show="show" x-transition class="grid gap-1.5">
            @foreach ($config['sections']['actions'] as $action)
                <button id="{{ $action['id'] }}"
                    @if($action['wire']) wire:click="{{ $action['wire'] }}()" @endif
                    class="{{ $action['hidden'] ?? false ? 'hidden' : '' }} h-10 px-3 text-sm font-bold {{ $action['classes'] }} rounded-md shadow-sm focus:ring-2 focus:outline-none transition-all duration-200">
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <livewire:component.work-summary />
</div>
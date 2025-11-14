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
                Selezione: <span id="selectionText">{{ $label }}</span>
            </div>
        @endif
    </div>

   {{--  @if ($config['sections']['custom_input']['enabled'])
        <div id="customInputContainer" class="{{ $workType !== 'C' ? 'hidden' : '' }} mt-2 space-y-1.5">
            <label class="block text-sm md:text-base font-bold text-gray-800 border-l-4 {{ $config['sections']['custom_input']['border_color'] }} pl-2">
                {{ $config['sections']['custom_input']['label'] }}
            </label>
            <input id="customInput" type="text" placeholder="{{ $config['sections']['custom_input']['placeholder'] }}"
                class="w-full h-10 px-2 text-sm font-medium text-gray-900 bg-white border-2 {{ $config['sections']['custom_input']['input_border'] }} rounded-md placeholder:text-gray-500 placeholder:font-medium focus:ring-2 focus:outline-none transition-all duration-200" />
        </div>
    @endif --}}

    @if ($config['sections']['notes']['enabled'])
        <div class="mt-2 space-y-1.5">
            <label class="block text-sm md:text-base font-bold text-gray-800 border-l-4 {{ $config['sections']['notes']['border_color'] }} pl-2">
                {{ $config['sections']['notes']['label'] }}
            </label>
            <input id="agencyNotes" type="text" placeholder="{{ $config['sections']['notes']['placeholder'] }}" wire:model.live="voucher"
                class="w-full h-10 px-2 text-sm font-medium text-gray-900 bg-white border-2 {{ $config['sections']['notes']['input_border'] }} rounded-md placeholder:text-gray-500 placeholder:font-medium focus:ring-2 focus:outline-none transition-all duration-200" />
        </div>
    @endif

    <div class="mt-2 flex items-center gap-1.5">
        <input id="excludeSummary" type="checkbox" wire:model="excludeSummary"
            class="h-4 w-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-300 focus:outline-none" />
        <label class="text-sm font-bold text-gray-800">
            ESCLUDI RIEPILOGO
        </label>
    </div>

    <div class="mt-2 grid gap-1.5">
        @foreach ($config['sections']['actions'] as $action)
            <button id="{{ $action['id'] }}"
                class="{{ $action['hidden'] ?? false ? 'hidden' : '' }} h-10 px-3 text-sm font-bold {{ $action['classes'] }} rounded-md shadow-sm focus:ring-2 focus:outline-none transition-all duration-200">
                {{ $action['label'] }}
            </button>
        @endforeach
    </div>

    @if ($config['sections']['summary']['enabled'])
        <div class="mt-2 bg-gray-800 text-white p-2 rounded-md shadow-sm shadow-gray-500/40 space-y-1.5">
            @foreach ($config['sections']['summary']['counts'] as $count)
                <div class="flex justify-between text-xs font-bold">
                    <span>{{ $count['label'] }}:</span>
                    <span id="{{ $count['id'] }}">{{ $count['value'] }}</span>
                </div>
            @endforeach
            @if ($config['sections']['summary']['grand_total']['enabled'])
                <div id="grandTotal" class="flex justify-between text-xs font-bold">
                    <span>Totale:</span>
                    <span id="grandTotalValue">{{ $config['sections']['summary']['grand_total']['value'] }}</span>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-2 grid gap-1.5">
        <livewire:actions.logout-button />
    </div>
</div>
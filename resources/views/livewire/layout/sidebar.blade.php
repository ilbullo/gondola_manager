<div
    id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-white shadow-2xl shadow-blue-600/10 p-4 transform -translate-x-full lg:translate-x-0 lg:static lg:shadow-lg transition-transform duration-300 ease-in-out z-40 overflow-y-auto"
    x-data="{ actionsOpen: @entangle('showActions') }"
>
    {{-- Flash success --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2 p-3 bg-gradient-to-r from-emerald-500 to-lime-500 text-white rounded-lg shadow-lg animate-fade-in">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Selezione rapida lavoro --}}
    <div class="space-y-3">
        <h3 class="text-sm font-extrabold text-gray-800 border-l-4 border-blue-600 pl-3 uppercase tracking-wider">
            Tipo Lavoro
        </h3>

        <div class="grid gap-2">
            @foreach ($config['work_types'] as $btn)
                <button
                    wire:click="setWorkType('{{ $btn['value'] }}')"
                    id="{{ $btn['id'] }}"
                    class="h-11 px-4 text-sm font-bold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all duration-200 {{ $btn['classes'] }}
                           {{ $workType === $btn['value'] || ($workType === 'A' && $btn['value'] === 'A') ? 'ring-4 ring-white ring-opacity-60 scale-105' : '' }}"
                >
                    {{ $btn['label'] }}
                </button>
            @endforeach
        </div>

        {{-- RIEPILOGO TESTUALE MINIMALE – solo l’essenziale, altezza 42–48px --}}
@if($workType !== '' && $workType !== 'clear')
    <div class="mt-4 px-4 py-2 text-sm font-medium text-gray-800 border-l-4 border-blue-600 bg-gray-50 rounded-r-lg">

        <div class="flex items-center justify-between gap-3">

            <!-- Sinistra: tipo lavoro + agenzia -->
            <div class="flex-1 min-w-0">
                <span class="font-black">
                    {{ $label }}
                    @if($workType === 'A' && $agencyName)
                        → {{ $agencyName }}
                    @endif
                </span>

                <!-- Voucher se presente -->
                @if(trim($voucher))
                    <span class="ml-3 text-emerald-700 text-xs font-bold">
                        ({{ Str::limit($voucher, 20) }})
                    </span>
                @endif
            </div>

            <!-- Centro-destra: importo, caselle, opzioni, check -->
            <div class="flex items-center gap-3 text-xs font-bold">

                <span class="text-indigo-700">€{{ $amount }}</span>

                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    {{ $slotsOccupied }}
                </span>

                <!-- Opzioni speciali (solo se attive) -->
                @if($sharedFromFirst)
                    <span class="text-amber-600" title="Riparti dal primo">R1</span>
                @endif
                @if($excluded ?? false)
                    <span class="text-red-600" title="Escluso">X</span>
                @endif

                <!-- Check verde per confermare che è pronto -->
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000-16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>

            </div>
        </div>
    </div>
@endif

    </div>

    @if($workType != "")
    {{-- Note / Voucher --}}
    @if ($config['sections']['notes']['enabled'] ?? true)
        <div class="mt-5 space-y-2">
            <label class="block text-sm font-extrabold text-gray-800 border-l-4 border-emerald-600 pl-3">
                {{ $config['sections']['notes']['label'] }}
            </label>
            <input
                type="text"
                wire:model.live="voucher"
                placeholder="{{ $config['sections']['notes']['placeholder'] }}"
                class="w-full h-11 px-4 text-sm font-medium bg-white border-2 border-emerald-300 rounded-lg focus:border-emerald-600 focus:ring-4 focus:ring-emerald-300 focus:outline-none transition-all"
            />
        </div>
    @endif
    @endif

    {{-- Ripartizione dal primo --}}
  <!--  <div class="mt-4 flex items-center gap-3">
        <input
            id="sharedFromFirst"
            type="checkbox"
            wire:model.live="sharedFromFirst"
            class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 focus:ring-2"
        />
        <label for="sharedFromFirst" class="text-sm font-bold text-gray-800 cursor-pointer select-none">
            RIPARTISCI DAL PRIMO
        </label>
    </div>
-->
    {{-- Pulsanti principali --}}
    <div class="mt-6 space-y-3">
        @if($workType != "")
        <button
            wire:click="openWorkDetailsModal"
            class="w-full h-12 uppercase text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg shadow-lg focus:ring-4 focus:ring-blue-300 transition-all"
        >
            Configura Lavoro
        </button>
        @endif
{{-- Riepilogo lavori --}}
    <div class="mt-8 border-t-2 border-gray-200 pt-6">
        <livewire:component.work-summary />
    </div>
 <button
    wire:click="toggleActions"
    x-data="{ open: @entangle('showActions') }"
    type="button"
    class="w-full h-12 text-sm uppercase font-bold text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 rounded-lg shadow-lg focus:ring-4 focus:ring-purple-300 transition-all flex items-center justify-center gap-3"
>
    Opzioni Tabella

    <svg
        :class="open ? 'rotate-180' : ''"
        class="w-5 h-5 transition-transform duration-300"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
    >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
    </svg>
</button>

        {{-- Azioni avanzate (toggle) --}}
        <div
            x-show="actionsOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-4"
            class="grid gap-2 mt-3"
        >
            @foreach ($config['sections']['actions'] as $action)
                <button
                    id="{{ $action['id'] }}"
                    wire:click="{{ $action['wire'] ?? '' }}()"
                    class="{{ $action['hidden'] ?? false ? 'hidden' : '' }}
                           w-full h-11 text-sm font-bold rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all {{ $action['classes'] }}"
                >
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>
    </div>
</div>

{{-- resources/views/livewire/table-manager/work-assignment-table.blade.php --}}
<div wire:refresh class="h-full flex flex-col">

<div id="loading-modal" 
         {{-- Usa 'hidden' di default e rimuovi 'hidden' quando Livewire è in caricamento --}}
         wire:loading.class.remove="hidden" 
         
         {{-- Ora puoi lasciare 'flex' e le classi di centratura, perché 'hidden' ha la precedenza --}}
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[100] hidden"
         role="status" 
         aria-live="polite">
        
        <div class="bg-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-800 font-medium text-base">Elaborazione in corso...</span>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-6 h-[calc(100vh-2rem)]">

        <aside class="w-full md:w-auto flex-shrink-0">
            <livewire:layout.sidebar />
        </aside>

        <main class="flex-1 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex flex-col">
            <div class="overflow-auto flex-1 w-full">
                <div class="min-w-[1200px] w-full"> <table class="w-full border-collapse">

                        {{-- WCAG: Caption per screen reader --}}
                        <caption class="sr-only">Tabella di assegnazione lavori. Le righe rappresentano le licenze, le colonne gli slot orari.</caption>

                        {{-- Header --}}
                        <thead class="bg-gray-50 sticky top-0 z-20 shadow-sm">
                            <tr>
                                <th scope="col" class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider sticky left-0 bg-gray-50 border-b border-r border-gray-200 w-32 z-30">
                                    Licenza
                                </th>
                                @for ($i = 1; $i <= config('constants.matrix.total_slots'); $i++)
                                    <th scope="col" class="p-2 text-center text-xs font-bold text-gray-600 uppercase border-b border-gray-200 min-w-[3rem]">
                                        {{ $i }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        {{-- Body --}}
                        <tbody id="sortable" class="divide-y divide-gray-100">
                            @foreach($licenses as $license)
                                <tr class="hover:bg-gray-50/50 transition-colors">

                                    {{-- Colonna Licenza (Sticky Left) --}}
                                    <th scope="row"
                                        class="p-3 text-sm font-semibold text-gray-900 sticky left-0 bg-white border-r border-gray-200 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] 
                                               cursor-pointer hover:bg-gray-100 focus-within:bg-gray-100 transition-colors group"
                                        wire:click="openEditLicenseModal({{ $license['id'] }})"                                       
                                        wire:keydown.enter.prevent="$dispatch('openEditLicense', { id: {{ $license['id'] }} })"
                                        wire:keydown.space.prevent="$dispatch('openEditLicense', { id: {{ $license['id'] }} })"
                                        role="button"
                                        tabindex="0"
                                        aria-label="Modifica impostazioni licenza {{ $license['user']['license_number'] ?? 'N/A' }} (turno e contanti)">

                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2 flex-1">
                                                <span class="truncate font-bold text-gray-900">
                                                    {{ $license['user']['license_number'] ?? 'N/A' }}
                                                </span>
                                                <x-day-badge day="{{ $license['turn'] }}" />
                                                <x-no-agency-badge noAgency="{{ $license['only_cash_works'] }}" />
                                            </div>

                                            {{-- Icona matita (visibile solo su hover o focus) --}}
                                            <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 group-focus:opacity-100 transition-opacity"
                                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                 aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                    </th>

                                    {{-- Slot Lavorativi --}}
                                    @for ($slot = 0; $slot <= 24; $slot++)
                                        @php
                                            $index = $slot + 1;
                                            $workData = $license['worksMap'][$slot] ?? null;
                                            $hasWork = isset($workData);
                                            // Colore di sfondo basato sull'enum (preserviamo la tua logica)
                                            $bgClass = \App\Enums\WorkType::tryFrom($workData['value'] ?? '')?->colourClass() ?? '';

                                            // WCAG Label per screen reader
                                            $ariaLabel = $hasWork
                                                ? "Slot $index: Occupato da {$workData['value']}. Clicca per dettagli."
                                                : "Slot $index: Vuoto. Clicca per assegnare.";
                                        @endphp

                                        <td
                                            class="p-1 text-center border-r border-gray-100 cursor-pointer outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all duration-75 relative {{ $bgClass }}"

                                            {{-- Attributi WCAG per rendere la cella interattiva accessibile --}}
                                            role="button"
                                            tabindex="0"
                                            aria-label="{{ $ariaLabel }}"

                                            {{-- Gestione Click (Logica invariata) --}}
                                            wire:click="@if ($hasWork) openInfoBox(@js($workData['id'] ?? null), {{ $index }}) @else assignWork({{ $license['id'] }}, {{ $index }}) @endif"

                                            {{-- Gestione Tastiera (INVIO = Click) per accessibilità --}}
                                            wire:keydown.enter.prevent="@if ($hasWork) openInfoBox(@js($workData['id'] ?? null), {{ $index }}) @else assignWork({{ $license['id'] }}, {{ $index }}) @endif"
                                        >
                                            @if ($hasWork)
                                                <span class="text-xs font-bold text-gray-900 block leading-tight">
                                                    @if ($workData['value'] === 'A')
                                                        {{ $workData['agency_code'] ?? 'N/A' }}
                                                        @if ($workData['voucher'])
                                                            <span class="block text-[10px] font-normal text-gray-600">
                                                                ({{ Str::limit($workData['voucher'], 4, '') }})
                                                            </span>
                                                        @endif
                                                    @elseif ($workData['value'] === 'X')
                                                        X
                                                        @if ($workData['voucher'])
                                                            <span class="block text-[10px] font-normal text-gray-600">
                                                                ({{ Str::limit($workData['voucher'], 4, '') }})
                                                            </span>
                                                        @endif
                                                    @elseif (in_array($workData['value'], ['P', 'N']))
                                                        {{ $workData['value'] }}
                                                    @else
                                                        {{ $workData['value'] }}
                                                    @endif
                                                    @if($workData['excluded'])
                                                            <span class="inline-block px-1.5 py-0.5 mt-1 text-[9px] font-bold rounded-full bg-red-100 text-red-700">
                                                                F
                                                            </span>
                                                    @endif
                                                    @if($workData['shared_from_first'])
                                                            <span class="inline-block px-1.5 py-0.5 mt-1 text-[9px] font-bold rounded-full bg-emerald-100 text-emerald-700">
                                                                R
                                                            </span>
                                                    @endif
                                                </span>
                                            @else
                                                <div role="gridcell" aria-label="Cella vuota" class="p-1 text-center ...">
                                                    <span class="text-gray-300 text-xs">–</span>
                                                </div>
                                            @endif
                                                  
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-gray-50 border-t border-gray-200 p-4 flex justify-between items-center">
    <div class="text-sm text-gray-600">
        @if($errorMessage)
            <span class="text-red-600">{{ $errorMessage }}</span>
        @else
            Tabella aggiornata al {{ now()->format('H:i') }}
        @endif
    </div>
</div>
            {{-- Footer / Modali aggiuntivi --}}
            <div class="bg-gray-50 border-t border-gray-200 p-2">
                @livewire('component.rules-modal')
            </div>
                @livewire('ui.edit-license-modal')
        </main>
    </div>
</div>

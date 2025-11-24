{{-- resources/views/livewire/table-manager/work-assignment-table.blade.php --}}
<div wire:refresh class="h-full flex flex-col">

    <div id="loading-modal"
         wire:loading.flex
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[100]"
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
                                @for ($i = 1; $i <= 25; $i++)
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
                                    <th scope="row" class="p-3 text-sm font-semibold text-gray-900 sticky left-0 bg-white border-r border-gray-200 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center gap-2">
                                            <span class="truncate">{{ $license['user']['license_number'] ?? 'N/A' }}</span>
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
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs select-none">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer / Modali aggiuntivi --}}
            <div class="bg-gray-50 border-t border-gray-200 p-2">
                @livewire('component.rules-modal')
            </div>
        </main>
    </div>
</div>

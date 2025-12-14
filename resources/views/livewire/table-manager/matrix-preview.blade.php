{{-- resources/views/livewire/table-manager/table-splitter.blade.php --}}
<div class="h-screen flex flex-col bg-gray-50">

    {{-- MODALE COSTO BANCALE --}}
    @if ($showBancaleModal)
        <div>
            <div x-data="{ open: @entangle('showBancaleModal') }" x-show="open" x-transition.opacity
                @keydown.escape.window="$wire.closeBancaleModal()"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                role="dialog" aria-modal="true" aria-labelledby="bancale-modal-title" x-trap.noscroll.inert="open"
                x-init="$nextTick(() => document.getElementById('bancale-input')?.focus())" x-cloak>

                {{-- Backdrop che chiude al click --}}
                <div @click="$wire.closeBancaleModal()" class="absolute inset-0" aria-hidden="true"></div>

                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                    {{-- Header blu come nel work-details-modal --}}
                    <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
                        <h2 id="bancale-modal-title" class="text-xl font-bold text-white">
                            Costo Bancale Giornaliero
                        </h2>
                        <button @click="$wire.closeBancaleModal()"
                            class="text-white/80 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-white/50 rounded p-1"
                            aria-label="Chiudi modale">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Corpo modale --}}
                    <div class="flex-1 flex flex-col p-6 space-y-6 overflow-y-auto">
                        <div class="text-center">
                            <div
                                class="w-20 h-20 mx-auto mb-4 bg-amber-100 rounded-full flex items-center justify-center">
                                <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z M6 12h12" />
                                </svg>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                Inserisci il costo del bancale che verrà <strong>sottratto automaticamente</strong><br>
                                dal totale contanti nella stampa PDF.
                            </p>
                        </div>

                        <div>
                            <label for="bancale-input" class="block text-sm font-medium text-gray-700 mb-2">
                                Importo (€)
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 text-lg">€</span>
                                </div>
                                <input type="number" id="bancale-input" wire:model.live="bancaleCost" step="0.01"
                                    min="0"
                                    class="block w-full pl-12 pr-4 py-4 text-3xl font-bold text-center text-blue-600 bg-blue-50 border-2 border-blue-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all"
                                    placeholder="0.00" required @keydown.enter="$wire.confirmBancaleCost()" />
                            </div>
                            @error('bancaleCost')
                                <p class="mt-2 text-sm text-red-600 text-center">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pulsanti in fondo, stile identico --}}
                        <div class="grid grid-cols-1 gap-3 pt-4 border-t border-gray-100">
                            <button type="button" wire:click="confirmBancaleCost"
                                class="w-full px-6 py-3 text-lg font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all">
                                Conferma e Carica Tabella
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Loading overlay --}}
    <div id="loading-modal" wire:loading.class.remove="flex"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm items-center justify-center z-[100] hidden" role="status"
        aria-live="polite">
        <div class="bg-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4">
            <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
            <span class="text-gray-800 font-medium">Elaborazione...</span>
        </div>
    </div>

    <main class="{{ $showBancaleModal ? 'blur-sm pointer-events-none' : '' }} flex-1 flex flex-col p-4 gap-4">

        {{-- Header azioni --}}
        <header class="bg-white rounded-2xl shadow-md border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <label class="text-sm font-medium text-gray-700">Costo Bancale (€):</label>
                    <input type="number" step="1" min="0" wire:model.live.debounce.300ms="bancaleCost"
                        class="w-28 px-3 py-2 text-sm text-right border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="printSplitTable"
                        class="px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        Stampa Tabella
                    </button>
                    <button wire:click="printAgencyReport"
                        class="px-4 py-2.5 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition">
                        Report Agenzie
                    </button>
                    <button wire:click="$dispatch('goToAssignmentTable')"
                        class="px-4 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition">
                        Torna alla tabella
                    </button>
                </div>
            </div>
        </header>

        {{-- Tabella matrice --}}
        <div class="flex-1 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden flex flex-col">
            @if ($unassignedWorks)
                <div class="bg-amber-50 border border-amber-400 rounded-xl p-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-amber-900">
                            Lavori da assegnare ({{ count($unassignedWorks) }})
                        </span>
                        @if ($selectedWork)
                            <span class="text-xs font-medium text-blue-700">
                                → Selezionato: <strong>{{ strtoupper($selectedWork['value']) }}</strong>
                                <button wire:click="deselectWork"
                                    class="ml-1 underline text-blue-600">deseleziona</button>
                            </span>
                        @endif
                    </div>
                    <div class="grid grid-cols-8 sm:grid-cols-10 lg:grid-cols-12 gap-2">
                        @foreach ($unassignedWorks as $index => $work)
                            @php $type = \App\Enums\WorkType::tryFrom($work['value']); @endphp
                            <button wire:click="selectUnassignedWork({{ $index }})"
                                wire:key="unassigned-{{ $index }}"
                                class="p-2 rounded-lg border text-center text-xs font-medium transition-all hover:scale-105 focus:ring-2 focus:ring-blue-400
                                               {{ $selectedWork && data_get($selectedWork, 'id') == data_get($work, 'id')
                                                   ? 'bg-blue-600 text-white border-blue-800 ring-2 ring-blue-400 shadow-md'
                                                   : $type?->colourClass() ?? 'bg-gray-100 border-gray-300' }}">
                                <div class="font-bold text-sm">
                                    {{ $work['value'] === 'A' ? $work['agency_code'] ?? 'A' : strtoupper($work['value']) }}
                                </div>
                                <div class="text-[10px] opacity-80">
                                    {{ \Carbon\Carbon::parse($work['timestamp'])->format('H:i') }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="flex-1 overflow-auto">
                <div class="min-w-[1450px]">
                    <table class="w-full border-collapse text-xs">

                        <caption class="sr-only">Matrice finale di assegnazione lavori</caption>

                        <thead class="bg-gray-50 sticky top-0 z-20 shadow-sm">
                            <tr>
                                <th scope="col"
                                    class="p-3 text-left font-bold text-gray-600 uppercase tracking-wider sticky left-0 bg-gray-50 border-b border-r border-gray-200 w-32 z-30">
                                    Licenza
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-center font-bold text-gray-600 uppercase border-b border-gray-200">
                                    N</th>
                                <th scope="col"
                                    class="px-4 py-3 text-center font-bold text-gray-600 uppercase border-b border-gray-200">
                                    P</th>
                                <th scope="col"
                                    class="px-4 py-3 text-center font-bold text-gray-600 uppercase border-b border-gray-200">
                                    Capacità</th>
                                <th scope="col"
                                    class="px-4 py-3 text-center font-bold text-gray-600 uppercase border-b border-gray-200">
                                    Contanti</th>
                                @for ($i = 1; $i <= config('constants.matrix.total_slots'); $i++)
                                    <th scope="col"
                                        class="p-2 text-center font-bold text-gray-600 uppercase border-b border-gray-200 min-w-[3rem]">
                                        {{ $i }}
                                    </th>
                                @endfor
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @foreach ($matrix as $licenseKey => $license)
                                @php
                                    $works = $license['worksMap'];
                                    $nCount = collect($works)->where('value', 'N')->count();
                                    $pCount = collect($works)->where('value', 'P')->count();
                                    $occupied = collect($works)->filter()->count();
                                    $capacity = $license['slots_occupied'] ?? config('constants.matrix.total_slots');
                                    $cashTotal = collect($works)->where('value', 'X')->sum('amount') ?? 0;
                                    $walletBalance = $license['wallet'] - $nCount * 90;
                                    $cashNet = $cashTotal - $bancaleCost - $walletBalance;

                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">

                                    {{-- Licenza --}}
                                    <td class="text-center py-3">
                                        <div class="space-y-1">
                                            <div class="text-lg font-bold">
                                                {{ $license['user']['license_number'] ?? '—' }}
                                                <x-day-badge day="{{ $license['turn'] }}" />
                                                <x-no-agency-badge noAgency="{{ $license['only_cash_works'] }}" />
                                            </div>
                                            <button
                                                wire:click="$dispatch('open-license-receipt', {
                license: {{ \Illuminate\Support\Js::from($license) }},
                bancaleCost: {{ $bancaleCost }},
            })"
                                                class="text-[11px] px-2 py-0.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                                                Scontrino
                                            </button>
                                        </div>
                                    </td>

                                    {{-- N --}}
                                    <td class="px-4 py-3 text-center font-bold text-yellow-700 bg-yellow-50">
                                        {{ $nCount }}
                                    </td>

                                    {{-- P --}}
                                    <td class="px-4 py-3 text-center font-bold text-red-700 bg-red-50">
                                        {{ $pCount }}
                                    </td>

                                    {{-- Capacità --}}
                                    <td
                                        class="px-4 py-3 text-center font-bold {{ $occupied >= $capacity ? 'text-red-700 bg-red-50' : 'text-gray-800' }}">
                                        {{ $occupied }} / {{ $capacity }}
                                        @if ($occupied >= $capacity)
                                            <span class="block text-xs">Piena</span>
                                        @endif
                                    </td>

                                    {{-- Contanti --}}
                                    <td class="px-4 py-3 text-center font-bold text-green-700 bg-green-50">
                                        {{ number_format($cashNet, 0) }} €
                                    </td>

                                    {{-- Slot 1-25 - INIZIO MODIFICA PER COLSPAN E MULTI-SLOT --}}
                                    @php
                                        $skipSlots = 0; // Variabile per saltare le celle
                                    @endphp

                                    @foreach ($works as $slotIndex => $work)

                                        {{-- 1. Se stiamo saltando, decrementa il contatore e continua il ciclo --}}
                                        @if ($skipSlots > 0)
                                            @php
                                                $skipSlots--;
                                                continue;
                                            @endphp
                                        @endif

                                        @php
                                            $isEmpty = is_null($work);
                                            $type = $work ? \App\Enums\WorkType::tryFrom($work['value']) : null;
                                            $bgClass = $type?->colourClass() ?? '';

                                            // 2. Determina quanti slot occupa il lavoro. Predefinito: 1.
                                            // Assicurati che $work['slots_occupied'] sia popolato correttamente nel trait
                                            $workSlots = $work['slots_occupied'] ?? 1;

                                            // 3. Se il lavoro occupa più di 1 slot, imposta il contatore per saltare i successivi
                                            if ($workSlots > 1) {
                                                $skipSlots = $workSlots - 1;
                                            }

                                            // 4. Calcolo della colonna span (colspan)
                                            $colspan = $workSlots > 1 ? "colspan={$workSlots}" : '';
                                        @endphp

                                        <td {!! $colspan !!} {{-- Aggiunto l'attributo colspan --}}
                                            class="px-1 py-3 text-center border-r border-gray-100 cursor-pointer outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all {{ $bgClass }}
                                                   {{ $isEmpty ? 'hover:bg-green-50' : 'hover:bg-red-50' }}
                                                   {{ $selectedWork && $isEmpty ? 'ring-2 ring-blue-400 ring-inset' : '' }}"
                                            role="button" tabindex="0"
                                            aria-label="{{ $isEmpty ? 'Slot vuoto' : 'Slot occupato da ' . ($type?->label() ?? $work['value']) }}"
                                            wire:click="{{ $isEmpty ? 'assignToSlot(' . $licenseKey . ', ' . $slotIndex . ')' : 'removeWork(' . $licenseKey . ', ' . $slotIndex . ')' }}"
                                            wire:keydown.enter.prevent="{{ $isEmpty ? 'assignToSlot(' . $licenseKey . ', ' . $slotIndex . ')' : 'removeWork(' . $licenseKey . ', ' . $slotIndex . ')' }}">
                                            @if ($work)
                                                <div class="flex flex-col justify-center h-14 leading-tight relative">

                                                    {{-- Valore principale --}}
                                                    <span class="font-bold text-sm">
                                                        {{ $work['value'] === 'A' ? $work['agency_code'] ?? 'A' : strtoupper($work['value']) }}
                                                    </span>

                                                    {{-- Ora --}}
                                                    {{-- Badge F (excluded) --}}
                                                    @if ($work['excluded'] ?? false)
                                                        <span
                                                            class="absolute top-0 right-0 inline-block px-1.5 py-0.5 mt-1 mr-1 text-[9px] font-bold rounded-full bg-red-100 text-red-700">
                                                            F
                                                        </span>
                                                    @endif

                                                    {{-- Badge R (shared_from_first) --}}
                                                    @if ($work['shared_from_first'] ?? false)
                                                        <span
                                                            class="absolute top-0 right-0 inline-block px-1.5 py-0.5 mt-1 mr-1 text-[9px] font-bold rounded-full bg-emerald-100 text-emerald-700">
                                                            R
                                                        </span>
                                                    @endif

                                                    {{-- Se entrambi i badge, li spostiamo leggermente per non sovrapporsi --}}
                                                    @if (($work['excluded'] ?? false) && ($work['shared_from_first'] ?? false))
                                                        <style>
                                                            td .bg-red-100 {
                                                                top: 2px;
                                                                right: 14px;
                                                            }

                                                            td .bg-emerald-100 {
                                                                top: 2px;
                                                                right: 2px;
                                                            }
                                                        </style>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-300 text-lg leading-none">–</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    {{-- FINE MODIFICA PER COLSPAN E MULTI-SLOT --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer (Il pezzo che mancava) --}}
            <footer class="bg-gray-50 border-t border-gray-200 p-4 space-y-4">
                <div class="text-xs text-gray-600 grid grid-cols-5 gap-4">
                    <div><span class="inline-block w-4 h-4 rounded bg-green-100 mr-2"></span>Contanti (X)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-blue-100 mr-2"></span>Agenzia (A)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-yellow-50 mr-2"></span>Notifiche (N)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-red-50 mr-2"></span>Pre-assegnati (P)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-gray-100 mr-2"></span>Altro/Non mappato</div>
                </div>
            </footer>
        </div>
    </main>
    <livewire:ui.license-receipt-modal />

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('work-selected', () => {
                document.querySelectorAll('td[wire\\:click*="assignToSlot"]').forEach(el => {
                    el.classList.add('ring-2', 'ring-blue-400', 'ring-inset');
                });
            });
            Livewire.on('work-deselected', () => {
                document.querySelectorAll('td').forEach(el => {
                    el.classList.remove('ring-2', 'ring-blue-400', 'ring-inset');
                });
            });
        });
    </script>
</div>


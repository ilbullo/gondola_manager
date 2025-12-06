{{-- resources/views/livewire/table-manager/table-splitter.blade.php --}}
<div class="h-screen flex flex-col bg-gray-50">

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

    <main class="flex-1 flex flex-col p-4 gap-4">

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
                                    $walletBalance = $license['wallet'] - ($nCount * 90);
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

                                    {{-- Slot 1-25 --}}
                                    @foreach ($works as $slotIndex => $work)
                                        @php
                                            $isEmpty = is_null($work);
                                            $type = $work ? \App\Enums\WorkType::tryFrom($work['value']) : null;
                                            $bgClass = $type?->colourClass() ?? '';
                                        @endphp

                                        <td class="p-1 text-center border-r border-gray-100 cursor-pointer outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-all {{ $bgClass }}
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
                                                    <!-- <span class="text-[10px] text-gray-600">
                                                        {{--  --}}
                                                    </span>-->

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
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="bg-gray-50 border-t border-gray-200 p-4 space-y-4">
                <div class="text-xs text-gray-600 grid grid-cols-5 gap-4">
                    <div><span class="inline-block w-4 h-4 rounded bg-green-100 mr-2"></span>Contanti (X)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-indigo-100 mr-2"></span>Agenzie (A)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-yellow-100 mr-2"></span>Nolo (N)</div>
                    <div><span class="inline-block w-4 h-4 rounded bg-red-100 mr-2"></span>Perdi Volta (P)</div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-block px-1.5 py-0.5 text-[9px] font-bold rounded-full bg-red-100 text-red-700">F</span>
                        <span>Escluso</span>
                        <span
                            class="inline-block px-1.5 py-0.5 ml-2 text-[9px] font-bold rounded-full bg-emerald-100 text-emerald-700">R</span>
                        <span>Ripetuto</span>
                    </div>
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

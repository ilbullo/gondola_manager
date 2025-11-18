<div>
    <h2 class="text-2xl font-bold mb-4 text-blue-800">Ripartizione Lavori Giornaliera</h2>

    {{-- Dettagli e Azioni --}}
    <div class="mb-6 p-4 bg-gray-50 border rounded-lg flex flex-wrap items-center justify-between shadow-sm">
        {{-- Input per il Costo del Bancale --}}
        <div class="flex items-center space-x-4 mb-2 md:mb-0">
            <label for="bancaleCost" class="font-medium text-gray-700 whitespace-nowrap">Costo del Bancale (€):</label>
            <input
                type="number"
                id="bancaleCost"
                wire:model.live.debounce.300ms="bancaleCost"
                min="0"
                step="0.01"
                placeholder="0.00"
                class="border rounded-lg shadow-inner p-2 w-32 text-right focus:ring-blue-500 focus:border-blue-500"
            >
        </div>
        {{-- Pulsanti Stampa PDF (NUOVI) --}}
        <div class="flex space-x-3">
            <button
                wire:click="printSplitTable"
                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-150 text-sm"
            >
                Stampa Tabella (PDF)
            </button>
            <button
                wire:click="printAgencyReport"
                class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 transition duration-150 text-sm"
            >
                Stampa Report Agenzie (PDF)
            </button>

            {{-- Pulsante per tornare alla vista di assegnazione standard --}}
            <button
                wire:click="$dispatch('goToAssignmentTable')"
                class="px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-600 transition duration-150 text-sm"
            >
                <svg class="w-5 h-5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                Torna ad Assegnazione Manuale
            </button>
        </div>

        {{-- Pulsante per tornare alla vista di assegnazione standard --}}
        <button
            wire:click="$dispatch('goToAssignmentTable')"
            class="px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-600 transition duration-150 text-sm"
        >
            <svg class="w-5 h-5 inline mr-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
            Torna ad Assegnazione Manuale
        </button>
    </div>

    @if (empty($splitTable))
        <p class="text-lg text-red-500 font-semibold p-4 border border-red-300 bg-red-50 rounded-lg">
            ⚠️ Nessuna licenza in servizio o nessun lavoro da ripartire per oggi.
        </p>
    @else
        <div class="overflow-x-auto shadow-xl rounded-lg border">
            <table class="min-w-full divide-y divide-gray-300">
                <thead>
                    <tr class="bg-blue-600 text-white sticky top-0 z-10">
                        <th class="px-4 py-3 text-left text-sm font-semibold uppercase w-[200px] lg:w-[250px]">
                            Licenza / Operatore
                        </th>

                        {{-- Colonne Riepilogative (Punto 7) --}}
                        <th class="px-3 py-3 text-right text-sm font-semibold uppercase bg-blue-700 lg:w-[200px]">Contanti Dovuti (€)</th>
                        <th class="px-3 py-3 text-center text-sm font-semibold uppercase bg-yellow-700">Tot. N</th>
                        <th class="px-3 py-3 text-center text-sm font-semibold uppercase bg-red-700">Tot. P</th>

                        {{-- Colonne Slot (1 a 25) --}}
                        @for ($i = 1; $i <= 25; $i++)
                            <th class="px-2 py-3 text-center text-xs font-semibold uppercase border-l border-blue-500">{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($splitTable as $row)
                        <tr class="hover:bg-blue-50 transition duration-100">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white border-r border-gray-200 z-20">
                                {{ $row['license'] }} ({{ Str::limit($row['user_name'], 15) }})

                                {{-- Checkbox di Esclusione Lavori A (Punto 1) --}}
                                <div class="mt-1">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleExcludeFromA({{ $row['license_table_id'] }})"
                                            @checked(in_array($row['license_table_id'], $excludedFromA))
                                            class="rounded-sm border-gray-400 text-red-600 shadow-sm focus:ring-red-500 h-4 w-4"
                                        />
                                        <span class="ml-1 text-xs text-red-600 font-bold hover:text-red-700">Escludi Agenzie</span>
                                    </label>
                                </div>
                            </td>

                            {{-- Dati riepilogativi --}}
                            <td class="px-3 py-2 text-right whitespace-nowrap text-sm text-gray-900 font-extrabold bg-blue-50 border-r border-gray-300">
                                € {{ number_format($row['cash_due'], 2) }}
                            </td>
                            <td class="px-3 py-2 text-center whitespace-nowrap text-sm text-gray-900 font-bold bg-yellow-50 border-r border-gray-300">
                                {{ $row['n_count'] }}
                            </td>
                            <td class="px-3 py-2 text-center whitespace-nowrap text-sm text-gray-900 font-bold bg-red-50 border-r border-gray-300">
                                {{ $row['p_count'] }}
                            </td>

                            {{-- Colonne Assegnazione Lavoro (Slot) --}}
                            @php
                                $assignments = $row['assignments'];
                            @endphp

                            @for ($slot = 1; $slot <= 25; $slot++)
                                @php
                                    $work = $assignments[$slot] ?? null;
                                    $isMainWork = $work && isset($work->slot) && $work->slot === $slot;
                                    $colSpan = $isMainWork ? $work->slots_occupied : 1;

                                    // Proprietà per l'evidenziazione (disponibili solo su oggetti WorkAssignment)
                                    $isSFF = $isMainWork && ($work->shared_from_first ?? false);
                                    $isExcluded = $isMainWork && ($work->excluded ?? false);

                                    // 1. Colori base per tipo
                                    $colorClasses = match ($work->value ?? null) {
                                        'A' => 'bg-indigo-200 text-indigo-900 font-semibold',
                                        'X' => 'bg-green-200 text-green-900 font-semibold',
                                        'P' => 'bg-red-300 text-red-900 font-bold',
                                        'N' => 'bg-yellow-300 text-yellow-900 font-bold',
                                        default => 'bg-gray-100 text-gray-500', // Slot vuoto o placeholder
                                    };

                                    // 2. Evidenziazione Lavori Excluded (fissi)
                                    if ($isExcluded) {
                                        $colorClasses = 'bg-gray-500 text-white font-extrabold';
                                    }

                                    // 3. Evidenziazione Lavori Shared From First (SFF)
                                    $sffMarker = $isSFF ? '*' : '';
                                    $sffClass = $isSFF ? 'border-2 border-purple-700' : '';

                                    // Evita di stampare la cella se è un placeholder
                                    if (!$work || $isMainWork)
                                @endphp

                                @if ($isMainWork)
                                    <td colspan="{{ $colSpan }}"
                                        class="text-xs text-center border-l border-gray-300 p-0.5 whitespace-nowrap {{ $colorClasses }}"
                                        title="Lavoro: {{ $work->value }} - Slot: {{ $colSpan }}">
                                        {{ $work->value == 'A' ? $work->agency->code : $work->value }}
                                    </td>
                                @elseif (!$work)
                                    <td class="text-xs text-center border-l border-gray-300 p-0.5"></td>
                                @endif
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
                {{-- RIGA DI RIEPILOGO TOTALE --}}
                @php
                    // Converto l'array in una Collection per usare i metodi di aggregazione
                    $collection = collect($splitTable);
                    $totalCashDue = $collection->sum('cash_due');
                    $totalN = $collection->sum('n_count');
                    $totalP = $collection->sum('p_count');
                @endphp
                <tfoot class="sticky bottom-0 bg-gray-800 text-white shadow-inner">
                    <tr>
                        {{-- 1. Colonna Etichetta + Slots (Colspan 1 + 25 = 26 colonne totali) --}}
                        <td class="px-4 py-3 text-right text-lg font-extrabold uppercase border-r border-gray-700">
                            Totale Generale:
                        </td>

                        {{-- 2. Totale Contanti Dovuti --}}
                        <td class="px-3 py-3 text-right text-lg font-extrabold bg-blue-600 border-r border-gray-700">
                            € {{ number_format($totalCashDue, 2) }}
                        </td>

                        {{-- 3. Totale N --}}
                        <td class="px-3 py-3 text-center text-lg font-extrabold bg-yellow-600 border-r border-gray-700">
                            {{ $totalN }}
                        </td>

                        {{-- 4. Totale P --}}
                        <td class="px-3 py-3 text-center text-lg font-extrabold bg-red-600 border-r border-gray-700">
                            {{ $totalP }}
                        </td>

                         {{-- 5. Le 25 colonne slot unite --}}
                        <td colspan="25" class="px-3 py-3 text-center text-sm font-semibold bg-gray-700">
                            (Riepilogo Totale Slot Lavori)
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('printPdf', (eventPayload) => {
            // Livewire v3 passa il payload nell'array eventPayload[0]
            const params = eventPayload[0];

            // 1. Crea un form dinamico per l'invio POST (necessario per il download)
            const form = document.createElement('form');
            form.method = 'POST';
            // Assicurati che l'action corrisponda ESATTAMENTE alla tua rotta
            form.action = '/generate-pdf';
            form.target = '_blank'; // Consigliato per non interrompere la sessione Livewire

            // 2. Aggiungi il token CSRF di Laravel (FONDAMENTALE)
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            // 3. Aggiungi i parametri ricevuti dal componente
            for (const key in params) {
                if (params.hasOwnProperty(key)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;

                    // Cruciale: I dati complessi (l'array della tabella) DEVONO essere serializzati in JSON
                    if (key === 'data') {
                        input.value = JSON.stringify(params[key]);
                    } else {
                        input.value = params[key];
                    }
                    form.appendChild(input);
                }
            }

            // 4. Invia e pulisci il form
            document.body.appendChild(form);
            form.submit();
            form.remove(); // Rimuove il form dinamico dal DOM
        });
    });
</script>

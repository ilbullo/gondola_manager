@php
    use App\Enums\DayType;
    use Illuminate\Support\Str;
    use App\Models\WorkAssignment;
@endphp

<div>
    <h2 class="text-2xl font-bold mb-4 text-blue-800">Ripartizione Lavori Giornaliera</h2>

    {{-- ALERT VALIDAZIONE: Appare solo se i conti non tornano --}}
    @if(!empty($validationStats) && $validationStats['hasDiscrepancy'])
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded shadow-md">
            <h3 class="font-bold text-lg mb-2">⚠️ Attenzione: Discrepanza Lavori</h3>
            <p class="mb-2">Alcuni lavori non sono stati assegnati (probabilmente per limiti di orario/turni o slot esauriti).</p>
            <ul class="list-disc list-inside text-sm">
                @foreach($validationStats['diff'] as $type => $diff)
                    @if($diff != 0)
                        <li>
                            Tipo <strong>{{ $type }}</strong>: 
                            Previsti {{ $validationStats['expected'][$type] }} - 
                            Assegnati {{ $validationStats['actual'][$type] }} 
                            (Differenza: <strong>{{ $diff }}</strong>)
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Dettagli e Azioni --}}
    <div class="mb-6 p-4 bg-gray-50 border rounded-lg flex flex-wrap items-center justify-between shadow-sm">
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

        <div class="flex space-x-3">
            <button wire:click="printSplitTable"
                class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700">
                Stampa Tabella (PDF)
            </button>

            <button wire:click="printAgencyReport"
                class="px-4 py-2 bg-purple-600 text-white font-semibold rounded-lg shadow-md hover:bg-purple-700">
                Report Agenzie (PDF)
            </button>
            <button wire:click="$dispatch('goToAssignmentTable')"
            class="px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg shadow-md hover:bg-yellow-600">
            ← Torna ad Assegnazione Manuale
        </button>
        </div>
    </div>
    
    {{-- Tabella --}}
    @if (!empty($splitTable))
        @php
            $totalCashDue = collect($splitTable)->sum('cash_due');
            $totalN = collect($splitTable)->sum('n_count');
            $totalP = collect($splitTable)->sum('p_count');
        @endphp

        <div class="overflow-x-auto shadow-xl rounded-lg border">
            <table class="min-w-full divide-y divide-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th scope="col" class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider sticky left-0 bg-gray-200 border-r border-gray-300 z-20 w-48">
                            Licenza / Turno
                        </th>
                        <th scope="col" class="px-3 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300 w-20 bg-blue-100">
                            Cash Dovuto (€)
                        </th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300 w-12 bg-yellow-100">
                            N
                        </th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-r border-gray-300 w-12 bg-red-100">
                            P
                        </th>
                        @for ($i = 1; $i <= 25; $i++)
                            <th scope="col" class="text-center text-xs font-semibold text-gray-600 tracking-wider w-8">
                                {{ $i }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach ($splitTable as $row)
                        <tr class="hover:bg-blue-50 transition duration-100">
                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-0 bg-white border-r border-gray-200 z-20">
                                {{ $row['license'] }} ({{ Str::limit($row['user_name'], 15) }})

                                <div class="mt-2 flex flex-col space-y-1">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleExcludeFromA({{ $row['license_table_id'] }})"
                                            @checked(in_array($row['license_table_id'], $excludedFromA))
                                            class="rounded-sm border-gray-400 text-red-600 shadow-sm focus:ring-red-500 h-4 w-4"
                                        />
                                        <span class="ml-1 text-xs text-red-600 font-bold hover:text-red-700">No Agenzie (A)</span>
                                    </label>

                                    <select 
                                        wire:model.live="shifts.{{ $row['license_table_id'] }}"
                                        class="text-xs border-gray-300 rounded p-1 pr-6 focus:ring-blue-500 focus:border-blue-500 mt-1"
                                    >
                                        @foreach(DayType::cases() as $type)
                                            <option value="{{ $type->value }}" 
                                                @selected(($shifts[$row['license_table_id']] ?? DayType::FULL->value) === $type->value)>
                                                {{ $type->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>

                            <td class="px-3 py-2 text-right whitespace-nowrap text-sm text-gray-900 font-extrabold bg-blue-50 border-r border-gray-300">
                                € {{ number_format($row['cash_due'], 2) }}
                            </td>
                            <td class="px-3 py-2 text-center whitespace-nowrap text-sm text-gray-900 font-bold bg-yellow-50 border-r border-gray-300">
                                {{ $row['n_count'] }}
                            </td>
                            <td class="px-3 py-2 text-center whitespace-nowrap text-sm text-gray-900 font-bold bg-red-50 border-r border-gray-300">
                                {{ $row['p_count'] }}
                            </td>

                            @php
                                $assignments = $row['assignments'];
                            @endphp

                            @for ($slot = 1; $slot <= 25; $slot++)
                                @php
                                    $work = $assignments[$slot] ?? null;
                                    $isMainWork = $work && ($work instanceof WorkAssignment || $work instanceof \stdClass) && ($work->slot ?? 0) === $slot;
                                    $colSpan = $isMainWork ? ($work->slots_occupied ?? 1) : 1;
                                    $workTypeEnum = \App\Enums\WorkType::class;
                                    $isSFF = $isMainWork && ($work->shared_from_first ?? false);
                                    $isExcluded = $isMainWork && ($work->excluded ?? false);
                                    $colorClasses =  $workTypeEnum::tryFrom($work->value ?? "")?->colourClass() ?? 'bg-gray-100 text-gray-500' . " font-bold";
                                    if ($isExcluded) {
                                        $colorClasses = $workTypeEnum::EXCLUDED->colourClass();
                                    }

                                    $sffClass = $isSFF ? 'bg-teal-300 text-teal-900' : '';
                                @endphp

                                @if ($isMainWork)
                                    <td colspan="{{ $colSpan }}"
                                        class="text-xs text-center border-l border-gray-300 p-0.5 whitespace-nowrap {{ $colorClasses }} {{ $sffClass }}"
                                        title="Lavoro: {{ $work->value }} - Slot: {{ $colSpan }} - Time: {{ \Carbon\Carbon::parse($work->timestamp ?? now())->format('H:i') }}">
                                        {{ $work->value == 'A' ? ($work->agency->code ?? 'AG') : $work->value }}
                                    </td>
                                @elseif (!$work)
                                    <td class="text-xs text-center border-l border-gray-300 p-0.5"></td>
                                @endif
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
                
                 <tfoot class="sticky bottom-0 bg-gray-800 text-white shadow-inner">
                    <tr>
                        <td class="px-4 py-3 text-right text-md font-extrabold uppercase border-r border-gray-700">
                            Totale Generale:
                        </td>
                        <td class="px-3 py-3 text-right text-md font-extrabold bg-blue-600 border-r border-gray-700">
                            € {{ number_format($totalCashDue, 2) }}
                        </td>
                        <td class="px-3 py-3 text-center text-md font-extrabold bg-yellow-500 border-r border-gray-700">
                            {{ $totalN }}
                        </td>
                        <td class="px-3 py-3 text-center text-md font-extrabold bg-red-600 border-r border-gray-700">
                            {{ $totalP }}
                        </td>
                        <td colspan="25" class="px-3 py-3 text-center text-sm font-semibold bg-gray-700">
                            Totale Slot Lavorativi Giornalieri: 25
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
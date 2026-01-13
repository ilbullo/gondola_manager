{{-- resources/views/livewire/table-manager/matrix-preview.blade.php --}}
<div class="h-screen flex flex-col bg-slate-200 overflow-hidden">

    {{-- MODALE COSTO BANCALE --}}
    @if ($showBancaleModal)
        <div x-data="{ open: @entangle('showBancaleModal') }" x-show="open"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md">

            <div x-show="open" x-transition.scale.95
                class="relative w-full max-w-sm bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-slate-200">

                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
                    <h2 class="text-lg font-black uppercase italic tracking-tighter">Costo Bancale</h2>
                    <button @click="$wire.closeBancaleModal()" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12" stroke-width="3" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="w-12 h-12 shrink-0 bg-amber-100 rounded-xl flex items-center justify-center">
                            <span class="text-xl font-black text-amber-500">€</span>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-tight">
                            Verrà sottratto dal totale contanti odierno (X).
                        </p>
                    </div>

                    <div class="relative">
                        <input type="number" id="bancale-input" wire:model.live="bancaleCost" step="0.01"
                            class="block w-full py-4 text-4xl font-black text-center text-slate-800 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 transition-all"
                            placeholder="0.00" autofocus @keydown.enter="$wire.confirmBancaleCost()" />
                    </div>

                    @if (app()->isLocal())
                        <div class="mt-4 p-3 bg-red-50 border border-red-100 rounded-xl">
                            <p class="text-[8px] font-black text-red-400 uppercase mb-2">Simulazione Bug (Dev Only)</p>
                            <select wire:model="testScenario"
                                class="w-full text-[10px] font-bold border-red-200 rounded-lg bg-white focus:ring-red-500">
                                <option value="">Nessun Errore (Normale)</option>
                                <option value="count">Errore Contatore (Count)</option>
                                <option value="overflow">Errore Capacità (Overflow)</option>
                                <option value="duplicate">Errore Duplicati</option>
                                <option value="wrong_shift">Errore Turno (Incursione Oraria)</option>
                            </select>
                        </div>
                    @endif

                    <button type="button" wire:click="confirmBancaleCost"
                        class="w-full py-4 text-lg font-black uppercase tracking-tighter text-white bg-indigo-600 hover:bg-indigo-500 rounded-2xl shadow-lg shadow-indigo-100 transition-all active:scale-95">
                        Conferma →
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- HEADER AZIONI - PULSANTI LARGHI COME ORIGINALE --}}
    <header class="bg-slate-900 text-white p-2 md:p-4 shadow-2xl z-50 flex flex-nowrap items-center justify-between gap-2 md:gap-4 overflow-x-auto no-scrollbar">
    <div class="flex items-center gap-2 md:gap-6 shrink-0">
        <h1 class="text-lg md:text-2xl font-black uppercase italic tracking-tighter border-r border-white/10 pr-2 md:pr-6 shrink-0">
            Splitter
        </h1>
        <div class="flex items-center gap-2 bg-white/5 p-1 md:p-2 rounded-xl border border-white/10 shrink-0">
            <span class="hidden xs:block text-[7px] md:text-[8px] font-black text-slate-500 uppercase leading-none">Costo<br>Bancale</span>
            <input type="number" step="1" wire:model.live.debounce.300ms="bancaleCost"
                class="w-12 md:w-20 bg-transparent border-none text-base md:text-xl font-black text-emerald-400 p-0 focus:ring-0">
        </div>
    </div>

    <div class="flex gap-1 md:gap-2 items-center shrink-0">
        <div class="flex bg-indigo-600 rounded-xl shadow-lg overflow-hidden border border-indigo-700 shrink-0">
            <button wire:click="printSplitTable"
                class="flex items-center gap-2 px-2 md:px-4 py-3 md:py-4 hover:bg-indigo-500 text-white transition-all active:bg-indigo-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span class="hidden lg:inline text-[10px] font-black uppercase">Stampa Tabella</span>
            </button>
            <button wire:click="downloadSplitTablePdf" title="Scarica PDF"
                class="flex items-center px-2 md:px-3 py-3 md:py-4 bg-indigo-700/50 hover:bg-indigo-500 text-indigo-100 border-l border-indigo-800/50">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden xl:inline ml-2 text-[10px] font-black uppercase">PDF</span>
            </button>
        </div>

        <div class="flex bg-purple-600 rounded-xl shadow-lg overflow-hidden border border-purple-700 shrink-0">
            <button wire:click="printAgencyReport"
                class="flex items-center gap-2 px-2 md:px-4 py-3 md:py-4 hover:bg-purple-500 text-white transition-all active:bg-purple-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="hidden lg:inline text-[10px] font-black uppercase">Report Agenzie</span>
            </button>
            <button wire:click="downloadAgencyPdf" title="Scarica PDF Report"
                class="flex items-center px-2 md:px-3 py-3 md:py-4 bg-purple-700/50 hover:bg-purple-500 text-purple-100 border-l border-purple-800/50">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span class="hidden xl:inline ml-2 text-[10px] font-black uppercase">PDF</span>
            </button>
        </div>

        <button wire:click="$dispatch('goToAssignmentTable')"
            class="flex items-center justify-center p-3 md:px-4 md:py-4 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-xl transition-all shadow-lg active:scale-95 shrink-0">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span class="hidden sm:inline ml-2 text-[10px] font-black uppercase">Indietro</span>
        </button>
    </div>
</header>

    <main class="flex-1 flex flex-col overflow-hidden {{ $showBancaleModal ? 'blur-sm pointer-events-none' : '' }}">

        {{-- AREA LAVORI DA ASSEGNARE --}}
        @if ($unassignedWorks && count($unassignedWorks) > 0)
            <div class="bg-white p-4 border-b border-slate-300 shrink-0 z-40">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Lavori In Sospeso
                        ({{ count($unassignedWorks) }})</h2>
                    @if ($selectedWork)
                        <div class="flex items-center gap-3 animate-pulse">
                            <span class="text-[10px] font-black text-indigo-600 uppercase italic">Selezionato:
                                {{ strtoupper($selectedWork['value']) }}</span>
                            <button wire:click="deselectWork"
                                class="text-[9px] font-bold text-slate-400 underline uppercase">ANNULLA</button>
                        </div>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($unassignedWorks as $index => $work)
                        <button wire:key="unassigned-{{ $work['id'] ?? $index }}"
                            wire:click="selectUnassignedWork({{ $index }})"
                            class="group relative h-12 w-16 rounded-xl border-2 transition-all hover:scale-105 flex flex-col items-center justify-center
                                {{ $selectedWork && data_get($selectedWork, 'id') == data_get($work, 'id') ? 'border-indigo-600 bg-indigo-50 shadow-lg' : 'border-slate-100 bg-slate-50' }}">
                            <span
                                class="text-xs font-black {{ $selectedWork && data_get($selectedWork, 'id') == data_get($work, 'id') ? 'text-indigo-600' : 'text-slate-700' }}">
                                {{ $work['value'] === 'A' ? $work['agency_code'] ?? 'A' : strtoupper($work['value']) }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- MATRICE SPLITTER --}}
        <div class="flex-1 bg-white overflow-hidden flex flex-col">
            <div class="flex-1 overflow-auto bg-slate-200">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-40">
                        <tr
                            class="bg-slate-50 border-b border-slate-300 h-10 uppercase text-[9px] font-black text-slate-400">
                            <th
                                class="sticky-column px-4 border-r border-slate-200 sticky left-0 bg-slate-50 z-50 w-32 text-left shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                Licenza</th>
                            <th class="px-2 border-r border-slate-200 text-center w-12">N</th>
                            <th class="px-2 border-r border-slate-200 text-center w-12">X</th>
                            <th class="px-2 border-r border-slate-200 text-center w-12">P</th>
                            <th class="px-2 border-r border-slate-200 text-center w-20 italic">Capacità</th>
                            <th class="px-4 border-r border-slate-200 text-center w-24 text-emerald-600 italic">Netto
                            </th>
                            @for ($i = 1; $i <= config('app_settings.matrix.total_slots'); $i++)
                                <th class="w-13 text-center border-r border-slate-100 min-w-[3.25rem]">
                                    S.{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($matrixTable->rows as $licenseKey => $row)
                            <tr wire:key="row-{{ $row->id }}" class="flex-nowrap hover:bg-indigo-50/20 group">

                                {{-- COLONNA LICENZA STICKY --}}
                                <td
                                    class="sticky-column bg-white px-3 py-2 border-r border-slate-200 sticky left-0 z-30 group-hover:bg-indigo-50/20 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 overflow-hidden">
                                            <span
                                                class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-slate-800 text-white rounded-lg font-black text-xs">
                                                {{ $row->user['license_number'] }}
                                            </span>
                                            <div class="flex flex-col leading-tight">
                                                <p
                                                    class="font-black text-slate-700 text-[10px] uppercase truncate w-20">
                                                    {{ Str::limit($row->user['name'], 12, '...') }}
                                                </p>
                                                <button
                                                    wire:click="$dispatch('open-license-receipt', { license: {{ \Illuminate\Support\Js::from($row) }}, bancaleCost: {{ $bancaleCost }} })"
                                                    class="text-[10px] font-black uppercase text-indigo-600 hover:text-indigo-800 text-left mt-0.5 tracking-tighter">Scontrino</button>
                                            </div>
                                        </div>
                                        @if ($row->only_cash_works)
                                            <span
                                                class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded font-black border border-emerald-200">X</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- CONTEGGI --}}
                                <td
                                    class="text-center font-black text-xs text-amber-600 bg-amber-50/20 border-r border-slate-100">
                                    @number($row->liquidation->counts['n'] ?? 0)</td>
                                <td
                                    class="text-center font-black text-xs text-emerald-600 bg-emerald-50/20 border-r border-slate-100">
                                    @number($row->liquidation->counts['x'] ?? 0)</td>
                                <td
                                    class="text-center font-black text-xs text-rose-600 bg-rose-50/20 border-r border-slate-100">
                                    @number($row->liquidation->counts['p'] ?? 0)</td>

                                <td class="text-center border-r border-slate-100">
                                    <span
                                        class="text-xs font-black {{ $row->slots_occupied >= $row->target_capacity ? 'text-rose-600' : 'text-slate-500' }}">
                                        {{ $row->slots_occupied }}/{{ $row->target_capacity }}
                                    </span>
                                </td>

                                <td
                                    class="text-center font-black text-xs text-emerald-700 italic bg-emerald-50/10 border-r border-slate-200">
                                    {{ $row->liquidation->netto() }}
                                </td>

                                {{-- GRIGLIA SERVIZI --}}
                                @for ($slotIndex = 1; $slotIndex <= config('app_settings.matrix.total_slots'); $slotIndex++)
                                    @php
                                        $work = $row->worksMap[$slotIndex] ?? null;
                                        $isEmpty = is_null($work);
                                    @endphp
                                    <td class="p-1.5 border-r border-slate-100 w-13 h-13 min-w-[3.25rem]">
                                        @if ($work)
                                            <div wire:key="work-{{ $row->id }}-{{ $slotIndex }}"
                                                wire:click="removeWork({{ $licenseKey }}, {{ $slotIndex }})"
                                                class="job-pill relative cursor-pointer w-11 h-11 rounded-xl text-white shadow-md flex flex-col items-center justify-center transition-transform active:scale-90 {{ \App\Enums\WorkType::tryFrom($work['value'])?->colourButtonsClass() }}">

                                                @if ($work['excluded'] ?? false)
                                                    <x-badge name="excluded" />
                                                @endif
                                                @if ($work['shared_from_first'] ?? false)
                                                    <x-badge name="shared_ff" />
                                                @endif

                                                <span class="text-[10px] font-black leading-none uppercase">
                                                    {{ $work['value'] === 'A' ? Str::limit($work['agency_code'] ?? 'A', 4, '') : $work['value'] }}
                                                </span>

                                                @if ($work['prev_license_number'] ?? null)
                                                    <span
                                                        class="text-[6px] font-black opacity-80 italic mt-0.5 leading-none tracking-tighter">DA:
                                                        {{ $work['prev_license_number'] }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <div wire:click="assignToSlot({{ $licenseKey }}, {{ $slotIndex }})"
                                                class="w-11 h-11 border border-slate-200 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 text-xl font-light hover:border-indigo-400 hover:bg-indigo-50 cursor-pointer transition-all">
                                                +
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- FOOTER --}}
            <footer class="bg-slate-50 p-3 border-t border-slate-200">
                <div class="flex flex-wrap gap-5 justify-center items-center">
                    {{-- LEGENDA COLORI --}}
                    <div class="flex items-center gap-2"><span
                            class="w-3 h-3 rounded bg-emerald-500 shadow-sm"></span><span
                            class="text-[9px] font-black text-slate-500 uppercase">Contanti</span></div>
                    <div class="flex items-center gap-2"><span
                            class="w-3 h-3 rounded bg-indigo-500 shadow-sm"></span><span
                            class="text-[9px] font-black text-slate-500 uppercase">Agenzie</span></div>
                    <div class="flex items-center gap-2"><span
                            class="w-3 h-3 rounded bg-amber-500 shadow-sm"></span><span
                            class="text-[9px] font-black text-slate-500 uppercase">Nolo</span></div>
                    <div class="flex items-center gap-2"><span
                            class="w-3 h-3 rounded bg-rose-500 shadow-sm"></span><span
                            class="text-[9px] font-black text-slate-500 uppercase">Perdi Volta</span></div>

                    <div class="h-4 w-[1px] bg-slate-300 mx-1"></div>

                    {{-- LEGENDA ICONE (Allineata ai Badge) --}}
                    <div class="flex items-center gap-2">
                        <div class="relative w-4 h-4 flex items-center justify-center">
                            <x-badge name="excluded" />
                        </div>
                        <span class="text-[9px] font-black text-slate-500 uppercase">Fisso (Escluso)</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="relative w-4 h-4 flex items-center justify-center">
                            <x-badge name="shared_ff" />
                        </div>
                        <span class="text-[9px] font-black text-slate-500 uppercase">Condiviso</span>
                    </div>
                </div>
            </footer>
        </div>
    </main>
    <livewire:ui.license-receipt-modal />
</div>

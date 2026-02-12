<div class="h-full flex flex-col overflow-hidden">
    <livewire:layout.sidebar />
    <main class="flex-1 overflow-auto bg-slate-200">
        <div class="inline-block min-w-full">
            {{-- HEADER --}}
            <div class="flex items-center bg-slate-50 border-b border-slate-300 sticky top-0 z-40 h-10 uppercase text-[9px] font-black text-slate-400">
                <div class="sticky-column bg-slate-50 px-4 border-r border-slate-200 flex items-center justify-between">
                    <span>Licenza</span>
                    <span class="mr-2">Solo X</span>
                </div>
                <div class="service-grid px-4">
                    @for($s=1; $s<=25; $s++)
                        <div class="w-12 text-center">S.{{ $s }}</div>
                    @endfor
                </div>
            </div>

            {{-- BODY --}}
            <div class="bg-white divide-y divide-slate-100">
                @foreach($licenses as $license)
                    <div class="flex items-center hover:bg-indigo-50/20" wire:key="license-row-{{ $license['id'] }}">

                        {{-- COLONNA STICKY SUPER COMPATTA --}}
                        <div class="sticky-column bg-white flex items-center justify-between px-2 py-2 border-r border-slate-200 z-30">
                            <div class="flex items-center gap-1.5 overflow-hidden">
                                {{-- Numero Licenza --}}
                                <span class="w-7 h-7 flex-shrink-0 flex items-center justify-center bg-slate-800 text-white rounded-lg font-black text-[10px]">
                                    {{ $license['user']['license_number'] }}
                                </span>

                                {{-- Nome (Abbreviato o Nascosto su schermi piccoli se necessario) --}}
                                <p class="font-black text-slate-700 text-[10px] uppercase truncate w-16">
                                    {{ Str::limit($license['user']['name'], 10, '...') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- SELETTORE TURNO CICLICO (F -> M -> P) --}}
                                @php
                                    $currentTurn = $license['turn'] ?? $license->turn ?? 'full';

                                    // Gestione Enum: estraiamo il valore stringa
                                    $shiftKey = $currentTurn instanceof \App\Enums\DayType ? $currentTurn->value : $currentTurn;

                                    $shiftStyles = [
                                        'full'      => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'label' => 'F'],
                                        'morning'   => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'M'],
                                        'afternoon' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'label' => 'P'],
                                    ];

                                    $currentStyle = $shiftStyles[$shiftKey] ?? $shiftStyles['full'];
                                @endphp
                                <button wire:click="cycleTurn({{ $license['id'] }})"
                                        class="w-5 h-5 flex items-center justify-center rounded-md {{ $currentStyle['bg'] }} {{ $currentStyle['text'] }} text-[9px] font-black border border-black/5 shadow-sm active:scale-90 transition-all">
                                    {{ $currentStyle['label'] }}
                                </button>

                                {{-- Toggle Solo X (Contanti) --}}
                                <button type="button"
                                    wire:click="toggleOnlyCash({{ $license['id'] }})"
                                    class="relative flex items-center h-3.5 w-7 flex-shrink-0 cursor-pointer rounded-full border transition-all duration-200 focus:outline-none {{ $license['only_cash_works'] ? 'bg-emerald-500 border-emerald-600' : 'bg-slate-200 border-slate-300' }}">
                                    <span class="pointer-events-none inline-block h-2.5 w-2.5 transform rounded-full bg-white shadow-sm transition-transform duration-200 {{ $license['only_cash_works'] ? 'translate-x-3.5' : 'translate-x-0.5' }}"></span>
                                </button>
                            </div>
                        </div>

                        {{-- GRIGLIA SERVIZI --}}
                        <div class="service-grid p-2 px-4">
                            @for($i=0; $i<25; $i++)
                                @php
                                    $index = $i + 1;
                                    $work = $license['worksMap'][$i] ?? null;
                                    $type = $work ? \App\Enums\WorkType::tryFrom($work['value']) : null;
    
                                    // Logica dinamica
                                    $colorClass = "";
                                    if ($work) {
                                        if ($type === \App\Enums\WorkType::AGENCY) {
                                            // Prendiamo il colore dal DB (tramite l'array che passi alla view)
                                            $agencyColor = $work['agency_colour'] ?? 'indigo';
                                            $colorClass = "bg-{$agencyColor}-600 text-white";
                                        } else {
                                            // Altrimenti prendiamo la classe standard dall'Enum
                                            $colorClass = $type?->colourButtonsClass() . " text-white";
                                        }
                                    }
                                @endphp
                                <div class="w-13 h-13 flex justify-center items-center">
                                    @if($work)
                                        <div wire:click="openInfoBox({{ $work['id'] }})"
                                             class="job-pill relative cursor-pointer w-11 h-11 rounded-xl text-white shadow-md flex flex-col items-center justify-center {{ $colorClass }}">

                                            {{-- Indicatore EXCLUDED (Pallino o Icona in alto a sinistra) --}}
                                            @if($work['excluded'])
                                                <x-badge name="excluded" title="Escluso dalla ripartizione" />
                                            @endif

                                            {{-- Indicatore SHARED FROM FIRST (Icona in alto a destra) --}}
                                            @if($work['shared_from_first'] ?? false)
                                                <x-badge name="shared_ff" title="Condiviso dal primo" />
                                            @endif

                                            {{-- Contenuto Centrale --}}
                                            <span class="text-[10px] font-black">{{ $work['agency_code'] ?? $work['value'] }}</span>
                                            @if($work['voucher'])
                                                <span class="text-[7px] font-bold opacity-80 uppercase truncate w-10 text-center">{{ Str::limit($work['voucher'], 5) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <button wire:click="assignWork({{ $license['id'] }}, {{ $index }})"
                                                class="w-11 h-11 border border-slate-200 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 text-xl font-light hover:border-indigo-400 hover:bg-indigo-50 transition-all">
                                            +
                                        </button>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
            <footer class="sticky bottom-0 z-50 bg-slate-900 text-white p-3 border-t border-slate-800 flex items-center justify-between shadow-[0_-4px_10px_rgba(0,0,0,0.3)] backdrop-blur-md bg-opacity-95">
    
    {{-- PARTE SINISTRA: Status Dinamico e Statistiche --}}
    <div class="flex items-center gap-6">
        {{-- MONITOR RIUTILIZZABILE --}}
        <x-system-monitor class="border-r border-slate-800 pr-6" />
        {{-- Componente Statistiche Lavori (Input: $licenses) --}}
        <livewire:component.work-summary :licenses="$licenses" :key="'summary-'.count($licenses)" />
    </div>

    {{-- PARTE DESTRA: Legenda Turni e Versione --}}
    <div class="flex items-center gap-8">
        
        {{-- Legenda Turni --}}
        <div class="flex gap-5 items-center bg-slate-800/30 px-4 py-1.5 rounded-xl border border-slate-700/30">
            <div class="flex items-center gap-2 group">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-100 shadow-[0_0_4px_rgba(255,255,255,0.4)] transition-transform group-hover:scale-125"></span>
                <span class="text-[9px] font-black uppercase text-slate-500 group-hover:text-slate-300 transition-colors">F: Full</span>
            </div>
            
            <div class="flex items-center gap-2 group">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shadow-[0_0_4px_rgba(251,191,36,0.4)] transition-transform group-hover:scale-125"></span>
                <span class="text-[9px] font-black uppercase text-slate-500 group-hover:text-slate-300 transition-colors">M: Mattino</span>
            </div>
            
            <div class="flex items-center gap-2 group">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-[0_0_4px_rgba(99,102,241,0.4)] transition-transform group-hover:scale-125"></span>
                <span class="text-[9px] font-black uppercase text-slate-500 group-hover:text-slate-300 transition-colors">P: Pomeriggio</span>
            </div>
        </div>

        {{-- Metadati di Sistema --}}
        <div class="flex items-center pl-4 border-l border-slate-800">
            <span class="text-[9px] font-black text-slate-600 uppercase tracking-[0.2em]">
                Rel. {{ config('app_settings.version') }}
            </span>
        </div>
    </div>
</footer>
        </div>
    </main>
    <div wire:loading.delay.longer wire:target="assignWork" :key="'summary-'.count($licenses).'-'.now()">
        <livewire:ui.spinner/>
    </div>
</div>

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
                                    $shift = $license['turn'] ?? 'full';
                                    $shiftStyles = [
                                        'full'      => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'label' => 'F'],
                                        'morning'   => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'M'],
                                        'afternoon' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'label' => 'P'],
                                    ];
                                    $currentStyle = $shiftStyles[$shift];
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
                                @endphp
                                <div class="w-13 h-13 flex justify-center items-center">
                                    @if($work)
                                        <div wire:click="openInfoBox({{ $work['id'] }})"
                                             class="job-pill relative cursor-pointer w-11 h-11 rounded-xl text-white shadow-md flex flex-col items-center justify-center {{ \App\Enums\WorkType::tryFrom($work['value'])?->colourButtonsClass() }}">

                                            {{-- Indicatore EXCLUDED (Pallino o Icona in alto a sinistra) --}}
                                            @if($work['excluded'])
                                                <div class="absolute -top-1 -left-1 w-4 h-4 bg-rose-500 border-2 border-white rounded-full flex items-center justify-center shadow-sm" title="Escluso dalla ripartizione">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-2 h-2 text-white">
                                                    <path d="M8.5 1a.75.75 0 0 0-.75.75V6.5a.5.5 0 0 1-1 0V2.75a.75.75 0 0 0-1.5 0V7.5a.5.5 0 0 1-1 0V4.75a.75.75 0 0 0-1.5 0v4.5a5.75 5.75 0 0 0 11.5 0v-2.5a.75.75 0 0 0-1.5 0V9.5a.5.5 0 0 1-1 0V2.75a.75.75 0 0 0-1.5 0V6.5a.5.5 0 0 1-1 0V1.75A.75.75 0 0 0 8.5 1Z" />
                                                    </svg>
                                                </div>
                                            @endif

                                            {{-- Indicatore SHARED FROM FIRST (Icona in alto a destra) --}}
                                            @if($work['shared_from_first'] ?? false)
                                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 border-2 border-white rounded-full flex items-center justify-center shadow-sm" title="Condiviso dal primo">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-2 h-2 text-white">
                                                    <path d="M2.09 15a1 1 0 0 0 1-1V8a1 1 0 1 0-2 0v6a1 1 0 0 0 1 1ZM5.765 13H4.09V8c.663 0 1.218-.466 1.556-1.037a4.02 4.02 0 0 1 1.358-1.377c.478-.292.907-.706.989-1.26V4.32a9.03 9.03 0 0 0 0-2.642c-.028-.194.048-.394.224-.479A2 2 0 0 1 11.09 3c0 .812-.08 1.605-.235 2.371a.521.521 0 0 0 .502.629h1.733c1.104 0 2.01.898 1.901 1.997a19.831 19.831 0 0 1-1.081 4.788c-.27.747-.998 1.215-1.793 1.215H9.414c-.215 0-.428-.035-.632-.103l-2.384-.794A2.002 2.002 0 0 0 5.765 13Z" />
                                                    </svg>
                                                </div>
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
        </div>
    </main>
</div>

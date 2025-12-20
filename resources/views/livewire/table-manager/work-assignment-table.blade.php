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

                        {{-- COLONNA STICKY CON TOGGLE --}}
                        <div class="sticky-column bg-white flex items-center justify-between px-3 py-2 border-r border-slate-200 z-30">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <span class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-slate-800 text-white rounded-lg font-black text-xs">
                                    {{ $license['user']['license_number'] }}
                                </span>
                                <p class="font-black text-slate-700 text-[10px] uppercase truncate w-24">
                                    {{ $license['user']['name'] }}
                                </p>
                            </div>

                            {{-- Toggle Solo X (Contanti) --}}
                            <button type="button"
                                wire:click="toggleOnlyCash({{ $license['id'] }})"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $license['only_cash_works'] ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $license['only_cash_works'] ? 'translate-x-4' : 'translate-x-0' }}"></span>
                            </button>
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
                                                    <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367z" clip-rule="evenodd" /></svg>
                                                </div>
                                            @endif

                                            {{-- Indicatore SHARED FROM FIRST (Icona in alto a destra) --}}
                                            @if($work['shared_from_first'] ?? false)
                                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 border-2 border-white rounded-full flex items-center justify-center shadow-sm" title="Condiviso dal primo">
                                                    <svg class="w-2 h-2 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M8 7h8m0 0l-4-4m4 4l-4 4m0 6H8m0 0l4 4m-4-4l4-4" /></svg>
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

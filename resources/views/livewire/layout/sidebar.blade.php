<header class="bg-slate-900 text-white shadow-2xl z-50 flex flex-wrap items-center px-4 py-3 gap-3 shrink-0">
    <div class="flex gap-2 border-r border-white/10 pr-4">
        <button wire:click="editTable()" class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center hover:bg-rose-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="3"/></svg>
        </button>
    </div>

    <div class="flex gap-1 bg-white/10 p-1 rounded-xl h-[50px]">
        @foreach(\App\Enums\WorkType::values() as $type)
            @php
                $colour = \App\Enums\WorkType::tryFrom($type)->colourButtonsClass();
            @endphp
            <button wire:click="setWorkType('{{ $type }}')"
                class="w-10 rounded-lg font-black transition-all {{ $workType == $type ? ($colour . ' shadow-lg scale-105') : 'text-slate-500' }}">
                {{ $type }}
            </button>
        @endforeach
    </div>

    <div class="config-item relative shrink-0">
        <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Prezzo</div>
        <input type="number" wire:model.live="amount" class="w-20 h-full bg-white/10 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-emerald-400 outline-none">
    </div>

    <div class="config-item relative shrink-0">
        <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Slots</div>
        <select wire:model.live="slotsOccupied" class="w-16 h-full bg-white/10 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-white outline-none appearance-none">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>
    </div>

    @if($workType === 'A')
    <div wire:click="setWorkType('A')" class="config-item px-3 bg-indigo-500 rounded-xl gap-3 shadow-lg cursor-pointer hover:bg-indigo-400">
        <div class="flex flex-col">
            <span class="text-[7px] font-black text-indigo-200 uppercase">Agenzia</span>
            <span class="text-[10px] font-black text-white uppercase">{{ $agencyName ?? 'Seleziona...' }}</span>
        </div>
    </div>
    @endif

    <div class="config-item relative shrink-0">
        <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Voucher</div>
        <input type="text" wire:model.live="voucher" class="md:w-28 w-20 xl:w-40 h-full bg-white/5 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-white uppercase outline-none">
    </div>

{{-- TOGGLES (Fisso / Condiviso) --}}
    <div class="flex gap-1 bg-white/5 p-1 rounded-xl border border-white/10 shrink-0 h-[50px]">
        <button type="button" wire:click="toggleExcluded"
            class="h-full px-3 lg:px-4 rounded-lg text-[10px] font-black uppercase transition-all {{ $excluded ? 'bg-red-600 text-white shadow-lg' : 'text-slate-500 hover:bg-white/5' }}">
            <span class="lg:hidden">F</span>
            <span class="hidden lg:inline whitespace-nowrap">Lavoro Fisso</span>
        </button>

        <button type="button" wire:click="toggleShared"
            class="h-full px-3 lg:px-4 rounded-lg text-[10px] font-black uppercase transition-all {{ $sharedFromFirst ? 'bg-yellow-300 text-black shadow-lg' : 'text-slate-500 hover:bg-white/5' }}">
            <span class="lg:hidden">1°</span>
            <span class="hidden lg:inline whitespace-nowrap">Condiviso 1°</span>
        </button>
    </div>

    <button @click="$dispatch('open-info-modal')" class="h-[50px] w-10 hidden lg:block flex items-center justify-center rounded-xl  hover:text-white transition-all shadow-lg">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>

    {{-- AZIONI (Stampa / Ripartizione) --}}
    <div class="flex gap-2 ml-auto shrink-0">
        <button wire:click="$dispatch('printWorksTable')" class="h-[50px] lg:px-4 p-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            <span class="hidden xl:inline text-[10px] font-black text-white uppercase">Stampa</span>
        </button>

        <button wire:click="$dispatch('downloadWorksTable')" class="h-[50px] lg:px-4 p-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
            <span class="hidden xl:inline text-[10px] font-black text-white uppercase">Download</span>
        </button>

        <button wire:click="$dispatch('callRedistributeWorks')" class="h-[50px] lg:px-4 p-2 bg-amber-500 hover:bg-amber-400 rounded-xl shadow-lg flex items-center justify-center gap-2 transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-slate-900">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z" />
            </svg>
            <span class="hidden xl:inline text-[10px] font-black text-slate-900 uppercase">Ripartizione</span>
        </button>
    </div>
</header>

@push('modals')
    <div x-data="{ open: false }"
     x-on:open-info-modal.window="open = true"
     x-show="open"
     class="fixed inset-0 z-[100] overflow-y-auto"
     style="display: none;">

    {{-- Overlay Sfondo --}}
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"></div>

    {{-- Contenitore Modale --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div @click.away="open = false"
             class="relative transform overflow-hidden rounded-[2rem] bg-slate-900 border border-white/10 shadow-2xl transition-all w-full max-w-lg p-8">

            {{-- Header Modale --}}
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-white uppercase italic tracking-tighter">Guida Procedure</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Istruzioni Order Entry</p>
                    </div>
                </div>
                <button @click="open = false" class="text-slate-500 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Contenuto --}}
            <div class="space-y-6">

                {{-- UFFICIO --}}
                <div class="bg-white/5 rounded-2xl p-5 border border-white/5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-indigo-500 text-[9px] font-black rounded uppercase">{{ config('app_settings.labels.shared_from_first') }}</span>
                    </div>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Seleziona il lavoro <strong class="text-white">Cash (X)</strong>. Nel campo voucher scrivi l'<strong>agenzia</strong> o il nome del <strong>capogruppo</strong>. Infine, attiva il tasto <span class="text-yellow-300 font-bold uppercase text-xs">"1°"</span> (Ripartito dal 1°).
                    </p>
                </div>

                {{-- FISSO --}}
                <div class="bg-white/5 rounded-2xl p-5 border border-white/5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-rose-500 text-[9px] font-black rounded uppercase">Fissi Licenza</span>
                    </div>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        Seleziona la tipologia di lavoro e clicca sul tasto <span class="text-rose-500 font-bold uppercase text-xs">"F"</span> (Fisso alla licenza). Il lavoro non verrà spostato durante la ripartizione.
                    </p>
                </div>

                {{-- MULTI-SLOT --}}
                <div class="bg-white/5 rounded-2xl p-5 border border-white/5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 bg-emerald-500 text-[9px] font-black rounded uppercase text-slate-900">Multi-Slot</span>
                    </div>
                    <p class="text-slate-300 text-sm leading-relaxed">
                        In caso di servizi da più di uno slot (es. orarie). Imposta gli slot occupati. Decidi se deve essere <span class="text-rose-500 font-bold italic">Fisso</span>, <span class="text-yellow-300 font-bold italic">Ripartito dal 1°</span> o <span class="text-emerald-400 font-bold italic">Libero</span>.
                        <br>
                        <span class="text-[11px] text-amber-400 font-bold uppercase mt-2 block italic">⚠️ Attenzione: Se lasciato libero, il lavoro seguirà la ripartizione automatica.</span>
                    </p>
                </div>

            </div>

            {{-- Footer Modale --}}
            <button @click="open = false" class="w-full mt-8 py-4 bg-slate-800 hover:bg-slate-700 text-white rounded-xl font-black uppercase text-xs transition-all border border-white/5">
                Ho capito
            </button>
        </div>
    </div>
</div>
@endpush

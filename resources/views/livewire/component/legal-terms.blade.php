<div class="min-h-screen w-full bg-slate-100 py-12 px-4 sm:px-6 lg:px-8 overflow-y-auto">
    <div class="max-w-4xl mx-auto space-y-6">
        
        {{-- HEADER --}}
        <div class="bg-white p-8 sm:p-12 rounded-[2rem] border border-slate-200 shadow-sm">
            <h1 class="text-4xl font-black text-slate-900 uppercase italic tracking-tighter leading-none text-center sm:text-left">
                Termini e Condizioni
            </h1>
            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mt-3 text-center sm:text-left">
                Contratto di licenza SaaS v.{{ config('legal.current_version') }}
            </p>
        </div>

        {{-- CARD 01 --}}
        <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-md italic">01</span>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest italic">Oggetto del Servizio</h2>
            </div>
            <p class="text-sm leading-relaxed text-slate-600">
                Il Fornitore <strong>{{ config('legal.owner_name') }}</strong> concede al Cliente una licenza d’uso non esclusiva e temporanea del software <strong>{{ config('legal.software_name') }}</strong>. Il servizio è erogato in modalità SaaS tramite sottodominio presso <span class="italic text-slate-900">{{ config('legal.domain_base') }}</span>.
            </p>
        </div>

        {{-- CARD 02 --}}
        <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-md italic">02</span>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest italic">Responsabilità dei Dati</h2>
            </div>
            <p class="text-sm leading-relaxed text-slate-600">
                Il Cliente è l'unico responsabile della veridicità dei dati (licenze, nomi, turni). Il Fornitore non effettua controlli sui contenuti e non risponde di errori derivanti da un uso improprio o inserimento di dati incoerenti.
            </p>
        </div>

        {{-- CARD 03 (RESPONSABILITÀ) --}}
        <div class="bg-rose-50 p-8 rounded-[2rem] border border-rose-100 shadow-sm">
            <div class="flex items-center gap-4 mb-6">
                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest bg-rose-100 px-2 py-1 rounded-md italic">03</span>
                <h2 class="text-xs font-black text-rose-700 uppercase tracking-widest italic">Limiti di Responsabilità</h2>
            </div>
            <div class="space-y-4">
                <p class="text-sm font-bold text-rose-900 italic uppercase tracking-tight">Il Fornitore declina ogni responsabilità per:</p>
                <ul class="space-y-3 text-[13px] text-rose-800/80">
                    <li class="flex items-start gap-3 italic leading-relaxed">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-2 shrink-0"></span>
                        Perdite di guadagno o mancati incassi derivanti dalla pianificazione generata dal sistema.
                    </li>
                    <li class="flex items-start gap-3 italic leading-relaxed">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-2 shrink-0"></span>
                        Sanzioni amministrative o violazioni di regolamenti locali derivanti dall'uso del software.
                    </li>
                    <li class="flex items-start gap-3 italic leading-relaxed">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mt-2 shrink-0"></span>
                        Interruzioni del servizio dovute a manutenzioni tecniche o cause di forza maggiore.
                    </li>
                </ul>
            </div>
        </div>

        {{-- CARD 04 --}}
        <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-md italic">04</span>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest italic">Proprietà Intellettuale</h2>
            </div>
            <p class="text-sm leading-relaxed text-slate-600">
                Il codice sorgente, gli algoritmi di ottimizzazione (MatrixEngine) e il Know-How sono di proprietà esclusiva di <strong>{{ config('legal.owner_name') }}</strong>. È fatto espresso divieto di copia, reverse-engineering o cessione della licenza a terzi.
            </p>
        </div>

        {{-- CARD 05 --}}
        <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-md italic">05</span>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest italic">Sospensione e Recesso</h2>
            </div>
            <p class="text-sm leading-relaxed text-slate-600">
                La sospensione avviene dopo {{ config('legal.notice_days') }} giorni di insolvenza. Il recesso richiede {{ config('legal.recess_days') }} giorni di preavviso via mail all'indirizzo dedicato: <span class="font-bold text-indigo-600">{{ config('legal.support_email') }}</span>.
            </p>
        </div>

        {{-- CARD 06 --}}
        <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <span class="text-[10px] font-black text-indigo-500 uppercase tracking-widest bg-indigo-50 px-2 py-1 rounded-md italic">06</span>
                <h2 class="text-xs font-black text-slate-800 uppercase tracking-widest italic">Accettazione e Log</h2>
            </div>
            <p class="text-sm leading-relaxed text-slate-600 italic">
                L'accettazione è registrata tramite ID utente, indirizzo IP e timestamp. Tale record costituisce prova legale del consenso e dell'obbligo di verifica finale della matrice da parte del Cliente prima della sua esecuzione.
            </p>
        </div>

        {{-- AZIONE FINALE --}}
        <div class="bg-slate-900 p-12 rounded-[3rem] text-white flex flex-col items-center text-center shadow-2xl">
            <h3 class="text-2xl font-black italic tracking-tighter mb-4 italic">Confermi l'accettazione?</h3>
            <div class="h-1.5 w-12 bg-emerald-500 rounded-full mb-8"></div>

            <button wire:click="accept" wire:loading.attr="disabled"
                class="w-full sm:w-auto px-20 py-6 bg-emerald-500 hover:bg-emerald-400 text-slate-900 rounded-2xl font-black uppercase text-xs tracking-[0.2em] transition-all active:scale-95">
                <span wire:loading.remove wire:target="accept">Accetta e Procedi</span>
                <span wire:loading wire:target="accept" class="flex items-center gap-3">
                    <svg class="animate-spin h-4 w-4 text-slate-900" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Elaborazione...
                </span>
            </button>
        </div>

        {{-- FOOTER --}}
        <div class="text-center py-6">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em]">
                &copy; {{ date('Y') }} {{ config('legal.owner_name') }}
            </p>
        </div>
    </div>
</div>
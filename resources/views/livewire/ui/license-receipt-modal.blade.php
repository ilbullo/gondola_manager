<div class="relative">
    @if($showModal)
        <div x-data="{ open: @entangle('showModal') }" 
             x-show="open" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-2 bg-slate-900/90 backdrop-blur-md">
            
            <div x-show="open" x-on:click="open = false" class="fixed inset-0 cursor-pointer"></div>

            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 class="relative bg-white rounded-[2rem] shadow-2xl max-w-lg w-full mx-auto overflow-hidden flex flex-col max-h-[95vh]">
                
                {{-- Header --}}
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white shrink-0">
                    <div class="flex flex-col">
                        <span class="text-[8px] font-black uppercase tracking-widest text-slate-500 mb-1">Cassa Odierna</span>
                        <h2 class="text-xl font-black uppercase italic tracking-tighter">Licenza {{ $license['user']['license_number'] ?? 'N/D' }}</h2>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3"/></svg>
                    </button>
                </div>

                <div class="p-5 overflow-y-auto custom-scrollbar space-y-5">
                    
                    {{-- Badge Volumi (Utilizzo direttiva @number) --}}
                    <div class="grid grid-cols-4 gap-2">
                        <div class="bg-amber-50 border border-amber-100 p-2 rounded-2xl text-center">
                            <div class="text-lg font-black text-amber-600">@number($this->liquidation->counts['n'])</div>
                            <div class="text-[7px] font-black uppercase">Noli</div>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 p-2 rounded-2xl text-center">
                            <div class="text-lg font-black text-emerald-600">@number($this->liquidation->counts['x'])</div>
                            <div class="text-[7px] font-black uppercase text-emerald-500">Contanti</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 p-2 rounded-2xl text-center italic">
                            <div class="text-lg font-black text-blue-600">@number($this->liquidation->counts['shared'])</div>
                            <div class="text-[7px] font-black uppercase text-blue-500">{{ config('app_settings.labels.shared_from_first') }}</div>
                        </div>

                        <div class="bg-rose-50 border border-rose-100 p-2 rounded-2xl text-center italic">
                            <div class="text-lg font-black text-rose-600">@number($this->liquidation->counts['p'])</div>
                            <div class="text-[7px] font-black uppercase text-rose-500">{{ \App\Enums\WorkType::PERDI_VOLTA->label() }}</div>
                        </div>
                    </div>

                    {{-- Elenco Agenzie (Utilizzo direttiva @trim) --}}
                    <div class="space-y-2">
                        <h3 class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1">Agenzie (Crediti Futuri)</h3>
                        <div class="space-y-1">
                            @forelse($this->liquidation->lists['agencies'] as $name => $voucher)
                                <div class="flex justify-between items-center bg-slate-50 px-3 py-2 rounded-xl border border-slate-100 text-[10px]">
                                    <span class="font-bold text-slate-600 uppercase">{{ count($voucher) }} X @trim($name, 25) </span>
                                    <span class="font-black text-indigo-500">{{ $voucher[0] ?: '---' }}</span>
                                </div>
                            @empty
                                <div class="text-[8px] text-slate-300 italic px-1 uppercase text-center">Nessuna agenzia</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Box Economico Finale (Utilizzo metodi Presenter del DTO) --}}
                    <div class="bg-slate-900 rounded-3xl p-5 text-white shadow-xl">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-[9px] font-black uppercase">
                                <span class="text-slate-400">Valore Contanti X</span>
                                <span class="text-white">{{ $this->liquidation->valoreX() }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center text-[9px] font-black uppercase">
                                <span class="text-slate-400">Conguaglio Wallet</span>
                                <span class="{{ $this->liquidation->money['wallet_diff'] < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                    {{ $this->liquidation->money['wallet_diff'] < 0 ? '-' : '+' }} {{ $this->liquidation->walletDiffFormatted() }}
                                </span>
                            </div>

                            @if($this->liquidation->money['bancale'] > 0)
                                <div class="flex justify-between items-center text-rose-400 text-[9px] font-black uppercase italic">
                                    <span>Addebito Bancale</span>
                                    <span>- @money($this->liquidation->money['bancale'])</span>
                                </div>
                            @endif

                            <div class="pt-4 border-t border-white/10 flex justify-between items-center">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-black uppercase text-indigo-400 italic">Netto Ricevere</span>
                                    <span class="text-[6px] text-slate-500 uppercase font-bold tracking-widest">Esclusi crediti futuri</span>
                                </div>
                                <span class="text-3xl font-black italic tracking-tighter {{ $this->liquidation->money['netto'] >= 0 ? 'text-emerald-400' : 'text-rose-500' }}">
                                    {{ $this->liquidation->netto() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer con Link di Stampa (Utilizzo @dateTime nel parametro opzionale) --}}
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-2 shrink-0">
                    <a href="{{ route('print.receipt', $this->liquidation->toPrintParams([
                            'license' => $license['user']['license_number'] ?? 'N/D',
                            'date'    => \App\Helpers\Format::dateTime(now()),
                            'op'      => auth()->user()->name,
                        ])) }}" 
                       target="_blank"
                       class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-black uppercase text-[10px] text-center shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" stroke-width="2.5"/>
                        </svg>
                        Stampa Scontrino
                    </a>
                    <button wire:click="closeModal" class="flex-1 py-3 bg-white border border-slate-200 text-slate-500 rounded-xl font-black uppercase text-[10px]">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
{{-- resources/views/livewire/ui/license-receipt-modal.blade.php --}}
<div>
    @if($showModal)
        <div x-data="{ open: @entangle('showModal') }" x-show="open" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-4 bg-slate-900/90 backdrop-blur-md">
            
            <div x-show="open" x-transition.scale.95 
                 class="relative bg-white rounded-[2rem] shadow-2xl max-w-lg w-full mx-auto overflow-hidden border border-slate-200 flex flex-col max-h-[95vh]">
                
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white shrink-0">
                    <div class="flex flex-col">
                        <span class="text-[8px] font-black uppercase tracking-widest text-slate-500 leading-none mb-1">Dettaglio Scontrino</span>
                        <h2 class="text-xl font-black uppercase italic tracking-tighter">
                            Licenza {{ $license['user']['license_number'] ?? 'N/D' }}
                        </h2>
                    </div>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3"/></svg>
                    </button>
                </div>

                <div class="p-5 overflow-y-auto custom-scrollbar space-y-5">
                    
                    <div class="flex justify-between items-center border-b border-slate-100 pb-3 text-[10px]">
                        <span class="font-bold text-slate-400 uppercase">{{ now()->format('d/m/Y') }}</span>
                        <span class="font-bold text-slate-400 uppercase">Op: {{ auth()->user()->name }}</span>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-amber-50 border border-amber-100 p-2 rounded-2xl text-center">
                            <div class="text-lg font-black text-amber-600">{{ $this->getNCount() }}</div>
                            <div class="text-[7px] font-black text-amber-500 uppercase">Nolo</div>
                        </div>
                        <div class="bg-rose-50 border border-rose-100 p-2 rounded-2xl text-center">
                            <div class="text-lg font-black text-rose-600">{{ $this->getPCount() }}</div>
                            <div class="text-[7px] font-black text-rose-500 uppercase">Perdi</div>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-100 p-2 rounded-2xl text-center">
                            <div class="text-lg font-black text-emerald-600">{{ $this->getCashWorks()->count() }}</div>
                            <div class="text-[7px] font-black text-emerald-500 uppercase">Contanti</div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Agenzie</h3>
                            <div class="flex-1 h-[1px] bg-slate-100"></div>
                        </div>
                        
                        <div class="space-y-1">
                            @forelse($this->getAgencyWorks() as $work)
                                <div class="flex justify-between items-center bg-slate-50 px-3 py-2 rounded-xl border border-slate-100 text-[10px]">
                                    <span class="font-black text-slate-700 uppercase">{{ Str::limit($work['agency'] ?? 'N/D', 20) }}</span>
                                    <span class="font-bold text-slate-400">{{ $work['voucher'] ? 'Cod: '.$work['voucher'] : '—' }}</span>
                                </div>
                            @empty
                                <div class="text-center py-2 text-slate-300 text-[8px] font-black uppercase italic">Nessuna agenzia</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-slate-900 rounded-3xl p-5 text-white shadow-lg">
                        <div class="space-y-2">
                            <div class="flex justify-between items-center text-slate-400 text-[9px] font-black uppercase">
                                <span>Incassato (X)</span>
                                <span class="text-white">€ {{ number_format($this->getCashTotal(), 2) }}</span>
                            </div>

                            @if($bancaleCost > 0)
                                <div class="flex justify-between items-center text-rose-400 text-[9px] font-black uppercase italic">
                                    <span>Bancale</span>
                                    <span>- € {{ number_format($bancaleCost, 2) }}</span>
                                </div>
                            @endif

                            <div class="pt-3 border-t border-white/10 flex justify-between items-center">
                                <span class="text-[10px] font-black uppercase text-indigo-400 italic">Netto Ricevere</span>
                                <span class="text-3xl font-black italic tracking-tighter {{ $this->getFinalCash() > 0 ? 'text-emerald-400' : 'text-rose-500' }}">
                                    € {{ number_format($this->getFinalCash(), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 shrink-0">
                    <button wire:click="closeModal"
                            class="w-full py-3 bg-white border border-slate-200 text-slate-500 rounded-xl font-black uppercase text-[10px] hover:bg-slate-100 transition-colors shadow-sm">
                        Chiudi scontrino
                    </button>
                </div>
            </div>
        </div>
    @endif
    @push('custom_css')
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 3px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        </style>
    @endpush
</div>
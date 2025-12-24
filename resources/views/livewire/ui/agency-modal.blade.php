{{-- resources/views/livewire/modals/agency-modal.blade.php --}}
<div x-data="{ show: @entangle('show') }"
     x-show="show"
     x-cloak
     x-on:keydown.escape.window="show = false"
     class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-900/95 backdrop-blur-md p-4">

    {{-- Overlay per chiusura al click esterno --}}
    <div x-show="show" x-on:click="show = false" class="fixed inset-0 cursor-pointer"></div>

    <div class="bg-white rounded-[3rem] shadow-[0_0_50px_rgba(0,0,0,0.5)] w-full max-w-2xl overflow-hidden flex flex-col relative animate-in zoom-in duration-300 border border-white/10"
         style="max-height: 80vh;"
         x-on:click.stop> {{-- Impedisce la chiusura cliccando sul modal stesso --}}

        {{-- Header "Pro Black" --}}
        <div class="bg-slate-900 px-8 py-6 flex items-center justify-between shrink-0 border-b border-slate-800">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-white uppercase italic tracking-tighter leading-none">Seleziona Agenzia</h3>
                    <p class="text-slate-500 text-[9px] font-black uppercase mt-1 tracking-[0.2em]">Partner autorizzati</p>
                </div>
            </div>

            <button type="button" wire:click="close" class="text-slate-500 hover:text-white transition-colors p-2 outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Lista Agenzie (Grid compatta) --}}
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-3 flex-1 overflow-y-auto bg-slate-50/50">
            @forelse ($this->agencies as $agency)
                {{-- Chiamiamo il metodo del componente invece del dispatch diretto per pulizia logica --}}
                <button 
                    wire:click="selectAgency({{ $agency->id }})"
                    wire:key="agency-{{ $agency->id }}"
                    class="group relative flex items-center p-3 bg-white border border-slate-200 rounded-2xl hover:border-indigo-500 hover:shadow-xl hover:shadow-indigo-500/5 transition-all active:scale-95 text-left outline-none">
                    
                    <div class="w-8 h-8 bg-slate-100 group-hover:bg-indigo-600 rounded-lg flex items-center justify-center transition-colors shrink-0">
                        <span class="text-[9px] font-black text-slate-400 group-hover:text-white uppercase">
                            {{ substr($agency->name, 0, 2) }}
                        </span>
                    </div>

                    <div class="ml-3 overflow-hidden">
                        <p class="text-[8px] font-black text-indigo-600 uppercase tracking-tighter opacity-70 mb-0.5">
                             {{ $agency->code ?? 'N/D' }}
                        </p>
                        <p class="text-[10px] font-black text-slate-700 uppercase leading-none truncate group-hover:text-indigo-900">
                            {{ $agency->name }}
                        </p>
                    </div>

                    {{-- Icona Check Invisibile --}}
                    <div class="absolute right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </button>
            @empty
                <div class="col-span-full py-20 text-center">
                    <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nessuna agenzia trovata</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="p-4 bg-white border-t border-slate-100 shrink-0 text-center">
            <button wire:click="close"
                    class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] hover:text-rose-500 transition-colors outline-none">
                Esci senza selezionare
            </button>
        </div>
    </div>
</div>
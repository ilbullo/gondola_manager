{{-- resources/views/livewire/modals/agency-modal.blade.php --}}
<div x-data="{ show: @entangle('show') }"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4">

    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col relative animate-in zoom-in duration-300"
         style="max-height: 85vh;">

        {{-- Tasto Chiudi in alto a destra --}}
        <button type="button"
                wire:click="close"
                class="absolute top-4 right-4 z-50 w-10 h-10 flex items-center justify-center bg-white/10 hover:bg-white/20 rounded-full text-white transition-all backdrop-blur-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Header Fisso --}}
        <div class="bg-indigo-600 p-10 text-center text-white shrink-0">
            <div class="w-16 h-16 bg-white/20 rounded-2xl mx-auto flex items-center justify-center text-2xl font-black mb-3 shadow-xl border border-white/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="text-2xl font-black uppercase italic tracking-widest">Seleziona Agenzia</h3>
            <p class="text-indigo-100 text-[10px] font-bold uppercase mt-1 tracking-tighter opacity-70">Scegli il partner per l'assegnazione del servizio</p>
        </div>

        {{-- Corpo Scorrevole --}}
        <div class="p-8 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 flex-1 overflow-y-auto bg-white">
            @foreach ($agencies as $agency)
                <button wire:click="$dispatch('selectAgency', { agencyId: {{ $agency['id'] }} })"
                    class="h-20 bg-slate-50 border-2 border-slate-200 rounded-2xl flex flex-col items-center justify-center hover:border-indigo-500 transition-all">

                    {{-- Badge Codice --}}
                    <span class="text-[8px] font-black text-slate-400 uppercase">
                        {{ $agency['code'] ?? "" }}
                    </span>

                    <span class="text-xs font-black text-slate-700 uppercase mt-1">
                        {{ $agency['name'] }}
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Footer Fisso --}}
        <div class="p-6 bg-slate-50 border-t border-slate-100 shrink-0">
            <button wire:click="close"
                    class="w-full py-5 bg-white border border-slate-200 text-slate-400 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-slate-100 hover:text-slate-600 transition-all shadow-sm">
                Annulla Selezione
            </button>
        </div>
    </div>
</div>

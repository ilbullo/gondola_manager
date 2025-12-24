{{-- resources/views/livewire/modals/confirm-modal.blade.php --}}
<div class="relative">
    @if($show)
    <div
        x-data="{ open: @entangle('show') }"
        x-show="open"
        x-on:keydown.escape.window="$wire.cancel()"
        class="fixed inset-0 z-[10000] overflow-hidden"
        role="dialog"
        aria-modal="true"
    >
        {{-- Overlay con Blur --}}
        <div 
            class="fixed inset-0 bg-slate-900/90 backdrop-blur-md transition-opacity" 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="$wire.cancel()"
        ></div>

        {{-- Contenitore Modale --}}
        <div class="flex min-h-full items-center justify-center p-6 text-center shadow-inner">
            <div
                x-show="open"
                x-transition:enter="ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                @click.stop {{-- Impedisce la chiusura cliccando dentro la modale --}}
                class="relative w-full max-w-sm transform overflow-hidden rounded-[3rem] bg-white p-8 shadow-2xl border border-slate-200"
            >
                {{-- Icona di Avviso --}}
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[2rem] bg-rose-50 mb-6 shadow-inner">
                    <svg class="h-10 w-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                {{-- Testi --}}
                <h3 class="text-2xl font-black uppercase italic tracking-tighter text-slate-900 mb-2">
                    Attenzione
                </h3>
                
                <div class="px-2 mb-8">
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest leading-relaxed">
                        {{ $message }}
                    </p>
                </div>

                {{-- Azioni (Ottimizzate per Tablet) --}}
                <div class="flex flex-col gap-3">
                    <button
                        type="button"
                        wire:click="confirm"
                        class="w-full py-5 bg-rose-500 text-white rounded-[1.5rem] font-black uppercase text-xs tracking-[0.2em] shadow-lg shadow-rose-200 active:bg-rose-600 active:scale-[0.97] transition-all"
                    >
                        Conferma
                    </button>

                    <button
                        type="button"
                        wire:click="cancel"
                        class="w-full py-4 bg-slate-50 text-slate-400 rounded-[1.5rem] font-black uppercase text-[10px] tracking-widest active:bg-slate-100 active:scale-[0.98] transition-all"
                    >
                        Annulla
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
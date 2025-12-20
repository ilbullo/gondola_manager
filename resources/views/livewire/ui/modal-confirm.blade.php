{{-- resources/views/livewire/modals/confirm-modal.blade.php --}}
<div>
    @if($show)
    <div
        x-data="{ open: @entangle('show') }"
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="$wire.cancel()"
        class="fixed inset-0 z-[10000] overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-md transition-opacity" @click="$wire.cancel()"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="relative w-full max-w-sm transform overflow-hidden rounded-[2.5rem] bg-white p-8 text-center shadow-2xl border border-slate-200"
            >
                {{-- Icona di Avviso PRO --}}
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-rose-50 mb-6">
                    <svg class="h-10 w-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-2xl font-black uppercase italic tracking-tight text-slate-900 mb-2">
                    Attenzione
                </h3>
                
                <p class="text-sm font-bold text-slate-400 uppercase leading-relaxed mb-8">
                    {{ $message ?? 'Sei sicuro di voler procedere con questa azione?' }}
                </p>

                <div class="space-y-3">
                    <button
                        type="button"
                        wire:click="confirm"
                        class="w-full py-5 bg-rose-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-lg shadow-rose-200 hover:bg-rose-600 transition-all active:scale-95"
                    >
                        Conferma Operazione
                    </button>

                    <button
                        type="button"
                        wire:click="cancel"
                        class="w-full py-4 bg-slate-100 text-slate-400 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:bg-slate-200 transition-all"
                    >
                        Annulla
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
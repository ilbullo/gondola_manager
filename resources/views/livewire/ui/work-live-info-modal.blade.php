{{-- resources/views/livewire/modals/work-live-info-modal.blade.php --}}
<div wire:key="work-info-modal-container">
    @if($open) {{-- Usiamo @if per evitare query inutili al DB quando il modale è chiuso --}}
    <div
        x-data="{ flipped: false }"
        x-cloak
        x-on:flip-to-front.window="flipped = false"
        x-on:keydown.escape.window="$wire.closeModal()"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4 sm:p-6"
    >
        {{-- Overlay di chiusura --}}
        <div class="absolute inset-0" @click="$wire.closeModal()"></div>

        <div class="relative w-full max-w-sm mx-auto perspective-1000">
            <div 
                class="relative preserve-3d transition-transform duration-700 ease-in-out shadow-2xl rounded-[2.5rem]"
                style="min-height: 580px;"
                :class="flipped ? 'rotate-y-180' : ''"
            >
                @if($this->work)
                    {{-- LATO FRONTALE: Visualizzazione Info Live --}}
                    <div class="card-side backface-hidden bg-white rounded-[2.5rem] overflow-hidden flex flex-col border border-slate-200 shadow-xl">
                        {{-- Passiamo esplicitamente i dati computati al partial --}}
                        @include('livewire.ui.partials.work-info-front', ['workData' => $this->workData])
                    </div>

                    {{-- LATO POSTERIORE: Form di Modifica --}}
                    <div class="card-side backface-hidden rotate-y-180 bg-slate-50 rounded-[2.5rem] overflow-hidden flex flex-col border border-slate-200 shadow-xl">
                        @include('livewire.ui.partials.work-edit-back')
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@push('custom_css')
<style>
    .perspective-1000 { perspective: 1200px; }
    .preserve-3d { transform-style: preserve-3d; position: relative; }
    .backface-hidden { 
        backface-visibility: hidden !important; 
        -webkit-backface-visibility: hidden !important; 
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
    }
    .rotate-y-180 { transform: rotateY(180deg); }
    
    /* Ottimizzazione per Tablet: evita lag durante la rotazione */
    .card-side { 
        will-change: transform; 
        background-color: white; /* Previene trasparenze durante il flip */
    }
</style>
@endpush
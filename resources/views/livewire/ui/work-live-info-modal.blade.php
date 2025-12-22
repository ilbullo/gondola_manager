{{-- resources/views/livewire/modals/work-live-info-modal.blade.php --}}
<div wire:key="work-info-modal-container">

    <div
        x-data="{ flipped: false }"
        x-show="$wire.open" {{-- Usiamo x-show al posto di @if per non rompere il DOM --}}
        x-cloak
        x-init="$watch('$wire.open', value => { if(!value) flipped = false })"
        x-on:flip-to-front.window="flipped = false"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4"
    >

        <div class="relative w-full max-w-sm mx-auto perspective-1000" @click.away="$wire.closeModal()">
            <div class="relative preserve-3d transition-transform duration-500 ease-in-out shadow-2xl rounded-[2.5rem]"
                 style="min-height: 550px;"
                 :class="flipped ? 'rotate-y-180' : ''">

                @if($work)
                    {{-- LATO FRONTALE --}}
                    <div class="card-side backface-hidden bg-white rounded-[2.5rem] overflow-hidden flex flex-col border border-slate-200">
                        @include('livewire.ui.partials.work-info-front')
                    </div>

                    {{-- LATO POSTERIORE --}}
                    <div class="card-side backface-hidden rotate-y-180 bg-white rounded-[2.5rem] overflow-hidden flex flex-col border border-slate-200">
                        @include('livewire.ui.partials.work-edit-back')
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('custom_css')
        <style>
            .perspective-1000 { perspective: 1000px; }
            .preserve-3d { transform-style: preserve-3d; }
            .backface-hidden { backface-visibility: hidden !important; -webkit-backface-visibility: hidden !important; }
            .rotate-y-180 { transform: rotateY(180deg); }
            .card-side { position: absolute; inset: 0; width: 100%; height: 100%; }
        </style>
@endpush

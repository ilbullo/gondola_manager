{{-- resources/views/livewire/modals/work-live-info-modal.blade.php --}}
<div>
    @if ($open)
        <div class="fixed inset-0 z-[200] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-4"
            x-data="{ flipped: false }"
            x-on:keydown.escape.window="if(!flipped) $wire.closeModal(); else flipped = false"
            x-on:flip-to-front.window="flipped = false">
            
            {{-- Container con Prospettiva per il Flip --}}
            <div class="relative w-full max-w-sm mx-auto perspective-1000">
                <div class="relative preserve-3d transition-transform duration-500 ease-in-out shadow-2xl rounded-[2.5rem]"
                     style="min-height: 550px;"
                     :class="flipped ? 'rotate-y-180' : ''">

                    {{-- LATO FRONTALE: Info (Vedi punto 2) --}}
                    <div class="absolute inset-0 backface-hidden bg-white rounded-[2.5rem] overflow-hidden flex flex-col">
                        @include('livewire.ui.partials.work-info-front')
                    </div>

                    {{-- LATO POSTERIORE: Edit (Vedi punto 3) --}}
                    <div class="absolute inset-0 backface-hidden rotate-y-180 bg-white rounded-[2.5rem] overflow-hidden flex flex-col">
                        @include('livewire.ui.partials.work-edit-back')
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
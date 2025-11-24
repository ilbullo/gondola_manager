<div>
    @if ($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="relative w-full max-w-lg h-[640px] perspective-1000" x-data="{
                flipped: false,
                excluded: @entangle('excluded'),
                shared_from_first: @entangle('shared_from_first')
            }"
                x-init="$watch('open', () => flipped = false);
                Livewire.on('flip-back', () => {
                    flipped = true;
                    $nextTick(() => flipped = false);
                });">
                <!-- CARD FLIP CONTAINER -->
                <div class="relative w-full h-full transition-transform duration-700 transform-style-preserve-3d"
                    :class="flipped ? 'rotate-y-180' : ''">
                    <!-- FRONTE: Dettagli lavoro (stile identico al modal) -->
                    <div
                        class="absolute inset-0 backface-hidden bg-white rounded-3xl shadow-2xl ring-1 ring-black ring-opacity-10 overflow-hidden">

                        @include('livewire.ui.partials.work-info-front')
                    </div>
                    <!-- RETRO: Form modifica -->
                    <div
                        class="absolute inset-0 backface-hidden bg-white rounded-3xl shadow-2xl ring-1 ring-black ring-opacity-10 overflow-hidden rotate-y-180">
                        @include('livewire.ui.partials.work-edit-back')
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .perspective-1000 {
            perspective: 1000px;
        }

        .transform-style-preserve-3d {
            transform-style: preserve-3d;
        }

        .backface-hidden {
            backface-visibility: hidden;
        }

        .rotate-y-180 {
            transform: rotateY(180deg);
        }
    </style>
</div>

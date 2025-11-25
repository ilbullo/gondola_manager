{{-- resources/views/livewire/modals/work-live-info-modal.blade.php --}}
<div>
    @if ($open)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="work-info-title"
            x-data="{
                flipped: false,
                excluded: @entangle('excluded'),
                shared_from_first: @entangle('shared_from_first')
            }"
            x-init="flipped = false; $nextTick(() => $el.focus())"
            x-on:keydown.escape.window="if(!flipped) $wire.closeModal(); else flipped = false"
            x-trap.noscroll.inert="true",
            x-on:flip-to-front.window="flipped = false"
        >
            {{-- Container 3D --}}
            <div class="relative w-full max-w-xl mx-auto perspective-1000">
                <div class="relative preserve-3d transition-transform duration-500 ease-in-out"
                     style="min-height: 500px;"
                     :class="flipped ? 'rotate-y-180' : ''">

                    {{-- LATO FRONTALE: Info --}}
                    <div class="absolute inset-0 backface-hidden bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                         aria-hidden="false"
                         :aria-hidden="flipped">
                        @include('livewire.ui.partials.work-info-front')
                    </div>

                    {{-- LATO POSTERIORE: Edit --}}
                    <div class="absolute inset-0 backface-hidden rotate-y-180 bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col"
                         aria-hidden="true"
                         :aria-hidden="!flipped">
                        @include('livewire.ui.partials.work-edit-back')
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .perspective-1000 { perspective: 1000px; }
        .preserve-3d      { transform-style: preserve-3d; }
        .backface-hidden  { backface-visibility: hidden; }
        .rotate-y-180     { transform: rotateY(180deg); }
    </style>
</div>

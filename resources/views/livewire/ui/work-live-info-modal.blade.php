{{-- resources/views/livewire/modals/work-live-info-modal.blade.php --}}
<div>
    @if ($open)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-md"
             role="dialog" aria-modal="true" aria-labelledby="work-info-title">

            <div class="relative w-full max-w-md mx-auto">
                <div x-data="{
                        flipped: false,
                        excluded: @entangle('excluded'),
                        shared_from_first: @entangle('shared_from_first')
                     }"
                     x-init="flipped = false">

                    <div class="relative preserve-3d" style="min-height: 560px; max-height: 92vh;"
                         :class="flipped ? 'rotate-y-180' : ''">

                        <div class="absolute inset-0 backface-hidden bg-white rounded-3xl shadow-2xl ring-1 ring-gray-900/10 overflow-hidden">
                            @include('livewire.ui.partials.work-info-front')
                        </div>

                        <div class="absolute inset-0 backface-hidden rotate-y-180 bg-white rounded-3xl shadow-2xl ring-1 ring-gray-900/10 overflow-hidden">
                            @include('livewire.ui.partials.work-edit-back')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
    .preserve-3d     { transform-style: preserve-3d; }
    .backface-hidden { backface-visibility: hidden; }
    .rotate-y-180    { transform: rotateY(180deg); }
</style>
</div>



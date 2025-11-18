{{-- resources/views/livewire/components/loading-overlay.blade.php --}}
<div>
    <div
        x-data="{ loading: @entangle('isLoading') }"
        x-show="loading"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm"
        aria-hidden="true"
    >
        <div class="flex flex-col items-center space-y-6 bg-white rounded-2xl shadow-2xl px-10 py-8 min-w-[280px]">
            <!-- Spinner moderno con gradiente -->
            <div class="relative">
                <div class="w-16 h-16 rounded-full border-4 border-gray-200"></div>
                <div class="absolute inset-0 w-16 h-16 rounded-full border-4 border-transparent border-t-blue-600 border-r-blue-600 animate-spin"></div>
                <div class="absolute inset-0 w-16 h-16 rounded-full border-4 border-transparent border-t-transparent border-r-indigo-500 animate-spin animation-delay-300"></div>
            </div>

            <!-- Testo con animazione pulsata -->
            <div class="text-center">
                <p class="text-lg font-semibold text-gray-800 animate-pulse">
                    {{ __('Caricamento in corso') }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    Attendere prego...
                </p>
            </div>

            <!-- Barra di progresso opzionale (se vuoi un effetto più dinamico) -->
            <div class="w-full max-w-xs bg-gray-200 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full animate-shimmer"></div>
            </div>
        </div>
    </div>
</div>
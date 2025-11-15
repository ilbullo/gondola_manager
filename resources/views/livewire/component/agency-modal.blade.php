<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" role="dialog" aria-modal="true" aria-labelledby="agencyModalTitle">
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-lg shadow-lg p-4 w-full max-w-md mx-4 sm:mx-auto max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h2 id="agencyModalTitle" class="text-lg font-bold text-gray-800">Seleziona Agenzia</h2>
            <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300 rounded-full" aria-label="Chiudi modale">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="grid gap-2">
            @forelse ($agencies as $agency)
                <button wire:click="selectAgency({{ $agency->id }})"
                    class="h-10 px-3 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-300 shadow-blue-500/40 rounded-md shadow-sm focus:ring-2 focus:outline-none transition-all duration-200">
                    {{ $agency->name }}
                </button>
            @empty
                <p class="text-sm text-gray-500 text-center">Nessuna agenzia disponibile</p>
            @endforelse
        </div>
    </div>
</div>
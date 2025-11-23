<div class="h-full flex flex-col">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-black text-white">Dettagli Lavoro</h3>
            <button wire:click="closeModal" class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-1 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Contenuto dettagli -->
    <div class="flex-1 p-5 space-y-4 text-sm overflow-y-auto">
        @php
            $data = $this->workData; // Usa la proprietà calcolata
            $workTypeEnum = \App\Enums\WorkType::tryFrom($data['value']);
        @endphp

        <!-- Tempo trascorso -->
        <div class="text-center pt-1 border-b border-gray-200 pb-4">
            <span class="text-sm font-semibold text-gray-600 block mb-1">Tempo Trascorso dalla Partenza</span>
            <span class="text-3xl font-black text-red-600">
                {{ $data['time_elapsed'] }}
            </span>
        </div>

        <!-- Griglia info -->
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div><span class="font-semibold text-gray-600">Ora:</span> <span class="font-bold">{{ $data['departure_time'] }}</span></div>
            <div><span class="font-semibold text-gray-600">Tipo:</span>
                <span class="uppercase inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $workTypeEnum?->colourClass() ?? 'bg-gray-200 text-gray-800' }}">
                    {{ $workTypeEnum?->label() ?? 'N/D' }}
                </span>
            </div>
            <div><span class="font-semibold text-gray-600">ID:</span> <span class="font-bold">{{ $data['id'] }}</span></div>
            <div><span class="font-semibold text-gray-600">Codice:</span> <span class="font-bold">{{ $data['value'] }}</span></div>
        </div>

        @if($data['value'] === 'A')
            <div class="text-center text-sm">
                <span class="font-semibold text-gray-600">Agenzia:</span>
                <span class="font-bold text-indigo-700">{{ $data['agency']['name'] }} ({{ $data['agency_code'] ?? '—' }})</span>
            </div>
        @endif
        <!-- Voucher / Note -->
@if(!empty($data['voucher']))
    <div class="text-center bg-amber-50 border border-amber-200 rounded-xl p-4 mx-4">
        <span class="text-xs font-semibold text-amber-700 block uppercase tracking-wider">Voucher / Nota</span>
        <span class="text-lg font-bold text-amber-900 break-all">{{ $data['voucher'] }}</span>
    </div>
@endif
        <div class="text-center text-3xl font-black text-indigo-600 pt-4">
            €{{ number_format($data['amount'], 2) }}
        </div>

        <!-- Pulsante per girare -->
        <div class="absolute inset-x-0 bottom-0 p-5 bg-gray-50">
            <button 
                @click="flipped = true" 
                class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold rounded-xl hover:scale-105 transition shadow-lg"
            >
                Modifica Lavoro
            </button>
        </div>
    </div>
</div>
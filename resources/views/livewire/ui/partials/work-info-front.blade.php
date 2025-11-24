<div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
    <div class="flex items-center justify-between">
        <h2 class="text-xl sm:text-2xl font-bold text-white">Dettagli Lavoro</h2>
        <button wire:click="closeModal"
            class="rounded-full p-2.5 text-white/80 hover:text-white hover:bg-white/20 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

<div class="flex-1 px-6 py-6 sm:px-8 overflow-y-auto space-y-6">
    @php
        $data = $this->workData;
        $workTypeEnum = \App\Enums\WorkType::tryFrom($data['value']);
    @endphp

    <!-- Tempo trascorso -->
    <div class="text-center pt-3 pb-5 border-b-2 border-gray-100">
        <span class="block text-sm font-semibold text-gray-600 mb-1">Tempo Trascorso</span>
        <span class="text-4xl font-black text-red-600">{{ $data['time_elapsed'] }}</span>
    </div>

    <!-- Griglia info -->
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <span class="text-gray-600 font-medium">Ora Partenza</span>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $data['departure_time'] }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl p-4 text-center">
            <span class="text-gray-600 font-medium">Tipo</span>
            <p class="mt-1">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $workTypeEnum?->colourClass() ?? 'bg-gray-300 text-gray-800' }}">
                    {{ $workTypeEnum?->label() ?? 'N/D' }}
                </span>
            </p>
        </div>
    </div>

    @if ($data['value'] === 'A')
        <div class="bg-indigo-50 border-2 border-indigo-200 rounded-xl p-5 text-center">
            <span class="text-sm font-semibold text-indigo-700">Agenzia</span>
            <p class="text-lg font-bold text-indigo-900 mt-1">
                {{ $data['agency']['name'] }} ({{ $data['agency_code'] ?? '—' }})
            </p>
        </div>
    @endif

    @if (!empty($data['voucher']))
        <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-5 text-center">
            <span class="text-xs font-bold text-amber-700 uppercase tracking-wider block mb-2">Voucher / Nota</span>
            <p class="text-base font-black text-amber-900 break-all">{{ $data['voucher'] }}</p>
        </div>
    @endif

    <!-- Importo grande -->
    <div class="text-center pt-4">
        <span class="text-5xl font-black text-indigo-600">
            €{{ number_format($data['amount'], 2) }}
        </span>
    </div>
</div>

<!-- FOOTER FISSO CON DUE PULSANTI -->
<div class="px-6 py-5 sm:px-8 bg-gray-50 border-t border-gray-200">
    <div class="flex flex-col sm:flex-row gap-3">
        <!-- Pulsante Elimina -->
        <button wire:click="confirmDelete({{ $data['id'] }})"
                class="order-2 sm:order-1 w-full px-6 py-3.5 text-base font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl shadow-sm hover:shadow transition-all duration-200 ring-1 ring-red-200">
            <span class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Elimina Lavoro
            </span>
        </button>

        <!-- Pulsante Modifica -->
        <button @click="flipped = true"
                class="order-1 sm:order-2 w-full px-8 py-3.5 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 rounded-xl shadow-lg hover:shadow-xl focus:ring-4 focus:ring-emerald-500/50 transition-all duration-200">
            Modifica Lavoro
        </button>
    </div>
</div>
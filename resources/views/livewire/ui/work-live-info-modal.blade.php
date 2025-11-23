<div>
    @if($open)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-on:click="$wire.closeModal()"
            x-transition.opacity
        >
            <div 
                class="w-full max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden" 
                x-on:click.stop
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="scale-95 opacity-0"
                x-transition:enter-end="scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-end="scale-95 opacity-0"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-black text-white">Dettagli Lavoro</h3>
                        <button 
                            wire:click="closeModal" 
                            class="text-white/80 hover:text-white hover:bg-white/20 rounded-lg p-1 transition"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Corpo -->
                <div class="p-5 space-y-4 text-sm">
                    @php
                        $formattedWork = $this->formattedWork; 
                        $workTypeEnum = \App\Enums\WorkType::tryFrom($formattedWork['value'] ?? '');
                    @endphp

                    <!-- Tempo trascorso (evidenziato) -->
                    <div class="pt-1 border-b border-gray-200 pb-3">
                        <div class="text-center">
                            <span class="text-sm font-semibold text-gray-600 block mb-1">Tempo Trascorso dalla Partenza</span>
                            <span class="text-3xl font-black text-red-600">
                                {{ $formattedWork['time_elapsed'] ?? 'N/A' }} fa
                            </span>
                        </div>
                    </div>

                    <!-- Griglia informazioni principali -->
                    <div class="grid grid-cols-2 gap-3 text-xs pt-1">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-600">Ora Partenza:</span>
                            <span class="font-bold text-gray-900">{{ $formattedWork['departure_time'] ?? '—' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-600">Tipo:</span>
                            <span class="font-bold text-gray-900">
                                <span class="uppercase inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $workTypeEnum?->colourClass() ?? 'bg-gray-200 text-gray-800' }}">
                                    {{ $workTypeEnum?->label() ?? 'Non Definito' }}
                                </span>
                            </span>
                        </div>

                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-600">ID Lavoro:</span>
                            <span class="font-bold text-gray-900">{{ $formattedWork['id'] ?? '—' }}</span>
                        </div>

                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-600">Codice DB:</span>
                            <span class="font-bold text-gray-900">{{ $formattedWork['value'] ?? '—' }}</span>
                        </div>
                    </div>

                    <!-- Agenzia (solo se tipo A) -->
                    @if(($formattedWork['value'] ?? '') === \App\Enums\WorkType::AGENCY->value)
                        <div class="flex justify-between border-t border-gray-200 pt-3">
                            <span class="font-semibold text-gray-600">Agenzia:</span>
                            <span class="font-bold text-gray-900">
                                {{ $formattedWork['agency'] ?? 'Non specificata' }} 
                                ({{ $formattedWork['agency_code'] ?? '—' }})
                            </span>
                        </div>
                    @endif

                    <!-- Importo di partenza -->
                    <div class="pt-3 border-t border-gray-200 mt-3">
                        <div class="flex justify-between items-baseline">
                            <span class="font-semibold text-gray-600">Importo di partenza:</span>
                            <span class="text-2xl font-black text-indigo-600">
                                €{{ number_format($formattedWork['amount'] ?? 0, 2) }}
                            </span>
                        </div>
                    </div>

                    <!-- Dati tecnici -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-gray-700 pt-3 border-t border-gray-200">Dati Tecnici</h4>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-600">Escluso da ripartizione:</span>
                            @if($formattedWork['excluded'] ?? false)
                                <span class="text-red-600 font-bold">SÌ</span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-600">Ripartito dal primo:</span>
                            @if($formattedWork['shared_from_first'] ?? false)
                                <span class="text-emerald-600 font-bold">Sì</span>
                            @else
                                <span class="text-gray-400">No</span>
                            @endif
                        </div>
                    </div>

                    <!-- Note / Voucher -->
                    @if(!empty($formattedWork['voucher'] ?? $formattedWork['note'] ?? ''))
                        <div class="pt-3 border-t border-gray-200">
                            <span class="font-semibold text-gray-600 block mb-1">Note (Voucher):</span>
                            <p class="bg-gray-50 px-3 py-2 rounded-xl text-gray-800 font-medium whitespace-pre-wrap text-xs">
                                {{ $formattedWork['voucher'] ?? $formattedWork['note'] }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Footer con Modifica (sinistra) ed Elimina (destra) -->
                <div class="bg-gray-50 px-5 py-3 flex justify-between items-center">
                    <!-- Modifica -->
                    <button 
                        wire:click="editWork({{ $formattedWork['id'] ?? 0 }})" 
                        class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium rounded-lg transition hover:bg-blue-100 flex items-center gap-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Modifica
                    </button>
                    <!-- Elimina (sostituisce "Chiudi") -->
                    <button 
                        wire:click="openConfirmRemove({{ $formattedWork['id'] ?? 0 }},{{ $formattedWork['slot'] }})"
                        class="px-5 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white font-bold rounded-xl transition transform hover:scale-105 shadow-lg flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Elimina
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
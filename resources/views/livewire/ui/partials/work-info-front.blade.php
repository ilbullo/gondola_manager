{{-- resources/views/livewire/ui/partials/work-info-front.blade.php --}}
<div class="h-full flex flex-col bg-white">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5">
        <div class="flex justify-between items-center">
            <h2 id="work-info-title" class="text-2xl font-bold text-white">Dettagli Lavoro</h2>
            <button wire:click="closeModal" class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Contenuto compatto e bellissimo -->
    <div class="flex-1 px-6 py-4 space-y-6 overflow-y-auto text-gray-800">
        @php $d = $this->workData; @endphp

        <div class="space-y-3">
            <!-- Ora partenza + Tipo -->
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium text-gray-600">Tempo trascorso</span>
                </div>
                <span class="font-bold text-xl text-red-600">{{ $d['time_elapsed'] }}</span>
            </div>

            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium text-gray-600">Ora Partenza</span>
                </div>
                <span class="font-bold text-xl text-gray-900">{{ $d['departure_time'] }}</span>
            </div>

            <!-- Tipo lavoro -->
            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h-4m-8 0H5" />
                    </svg>
                    <span class="font-medium text-gray-600">{{ $d['value']==='A' ? (\App\Enums\WorkType::tryFrom($d['value'])?->label() ?? 'N/D') : 'Tipo lavoro' }}</span>
                </div>
                @if ($d['value'] === 'A')
                    <div class="text-right">
                        <div class="font-bold text-gray-900">{{ $d['agency']['name'] ?? '—' }}</div>
                        <div class="text-sm text-gray-500">({{ $d['agency_code'] ?? '—' }})</div>
                    </div>
                @else
                    <div class="text-right">
                        <div class="font-bold text-gray-900">{{ (\App\Enums\WorkType::tryFrom($d['value'])?->label() ?? 'N/D') }}</div>
                    </div>
                @endif
            </div>

            <!-- Voucher / Nota -->
            @if (!empty($d['voucher']))
                <div class="flex items-center justify-between py-3 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6-4h6m-6 8h6m-9 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="font-medium text-gray-600">Nota / Voucher</span>
                    </div>
                    <span class="font-bold text-xl text-gray-900">{{ $d['voucher'] }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between py-3 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-66 h-6 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.25 7.756a4.5 4.5 0 1 0 0 8.488M7.5 10.5h5.25m-5.25 3h5.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>

                    <span class="font-medium text-gray-600">Importo Totale</span>
                </div>
                <span class="font-bold text-xl text-gray-900">€ {{ number_format($d['amount'], 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-4">
            <button wire:click="confirmDelete({{ $d['id'] }})"
                class="py-4 text-lg font-bold text-red-600 bg-red-50 hover:bg-red-100 rounded-xl border border-red-200 transition">
                Elimina
            </button>
            <button @click="flipped = true"
                class="py-4 text-lg font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 rounded-xl shadow-lg transition">
                Modifica Lavoro
            </button>
        </div>
    </div>
</div>

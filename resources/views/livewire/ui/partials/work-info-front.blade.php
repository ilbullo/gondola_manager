{{-- resources/views/livewire/ui/partials/work-info-front.blade.php --}}
<div class="h-full flex flex-col bg-white">

    <div class="bg-blue-600 px-6 py-4 flex justify-between items-center">
        <h3 id="work-info-title" class="text-2xl font-bold text-white">Dettagli Lavoro</h3>
        <button wire:click="closeModal" class="text-white/80 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-white/50 rounded">
            <span class="sr-only">Chiudi</span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex-1 p-6 overflow-y-auto space-y-5">
        @php $d = $this->workData; @endphp

        {{-- Tempo Trascorso (Evidenza) --}}
        <div class="text-center pb-4 border-b border-gray-100">
            <span class="block text-sm font-medium text-gray-500 uppercase tracking-wider">Tempo trascorso</span>
            <span class="block text-3xl font-bold text-red-600 mt-1">{{ $d['time_elapsed'] }}</span>
        </div>

        <div class="grid grid-cols-1 gap-4">
            {{-- Riga 1: Partenza e ID --}}
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Ora Partenza</span>
                <span class="text-base font-semibold text-gray-900">{{ $d['departure_time'] }}</span>
            </div>

            {{-- Riga 2: Tipo Lavoro --}}
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-600">Tipo</span>
                <div class="text-right">
                    @php
                        $label = \App\Enums\WorkType::tryFrom($d['value'])?->label() ?? 'N/D';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $label }}
                    </span>
                    @if ($d['value'] === 'A')
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $d['agency']['name'] ?? '' }} ({{ $d['agency_code'] ?? '—' }})
                        </div>
                    @endif
                </div>
            </div>

            {{-- Riga 3: Voucher --}}
            @if (!empty($d['voucher']))
                <div class="flex justify-between items-start border-t border-gray-100 pt-3">
                    <span class="text-sm font-medium text-gray-600 mt-0.5">Voucher / Note</span>
                    <span class="text-base font-medium text-gray-900 text-right max-w-[60%] break-words bg-yellow-50 px-2 py-1 rounded">
                        {{ $d['voucher'] }}
                    </span>
                </div>
            @endif

            {{-- Riga 4: Importo --}}
            <div class="flex justify-between items-center border-t border-gray-100 pt-3 mt-2">
                <span class="text-sm font-bold text-gray-700">Importo Totale</span>
                <span class="text-xl font-bold text-blue-600">€ {{ number_format($d['amount'], 2) }}</span>
            </div>
        </div>

        {{-- Info Tecniche (Escluso/Shared) --}}
        @if(($d['excluded'] ?? false) || ($d['shared_from_first'] ?? false))
            <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-xs text-center">
                @if($d['excluded'])
                    <span class="bg-red-50 text-red-700 px-2 py-1 rounded font-medium">Escluso da Rip.</span>
                @endif
                @if($d['shared_from_first'])
                    <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded font-medium">Ripartito dal 1°</span>
                @endif
            </div>
        @endif
    </div>

    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between gap-3">
        <button wire:click="confirmDelete({{ $d['id'] }})"
            class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition">
            Elimina
        </button>
        <button @click="flipped = true"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition">
            Modifica Lavoro
        </button>
    </div>
</div>

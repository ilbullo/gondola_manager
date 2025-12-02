<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black opacity-50" wire:click="closeModal"></div>

                <!-- Modale -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-auto p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Scontrino Licenza {{ $license['user']['license_number'] ?? 'N/D' }}
                        </h2>
                        <button wire:click="closeModal" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-5 text-sm">
                        <!-- Intestazione -->
                        <div class="border-b pb-3">
                            <p class="text-lg font-semibold">Data: {{ now()->format('d/m/Y') }}</p>
                            <p>Generato da: {{ auth()->user()->name }}</p>
                        </div>

                        <!-- Conteggi rapidi -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="{{ \App\Enums\WorkType::NOLO->colourClass() }} p-4 rounded">
                                <div class="text-3xl font-bold">{{ $this->getNCount() }}</div>
                                <div class="text-sm text-gray-600">Lavori N</div>
                            </div>
                            <div class="{{ \App\Enums\WorkType::PERDI_VOLTA->colourClass() }}  p-4 rounded">
                                <div class="text-3xl font-bold">{{ $this->getPCount() }}</div>
                                <div class="text-sm text-gray-600">Lavori P</div>
                            </div>
                            <div class="{{ \App\Enums\WorkType::CASH->colourClass() }} p-4 rounded">
                                <div class="text-3xl font-bold">{{ $this->getCashWorks()->count() }}</div>
                                <div class="text-sm text-gray-600">Contanti (X)</div>
                            </div>
                        </div>

                        <!-- Elenco Agenzie svolte -->
                        @if($this->getAgencyWorks()->count() > 0)
                            <div>
                                <h3 class="font-semibold text-lg mb-2">Agenzie Svolte ({{ $this->getAgencyWorks()->count() }})</h3>
                                <div class="space-y-2 max-h-48 overflow-y-auto bg-gray-50 p-3 rounded">
                                    @foreach($this->getAgencyWorks() as $work)
                                        <div class="flex justify-between text-sm">
                                            <span>
                                                <strong>{{ $work['agency'] ?? 'N/D' }}</strong>
                                                <small class="text-gray-500">
                                                     {{ $work['voucher'] ?? '' }}
                                                </small>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 italic">Nessuna agenzia svolta</p>
                        @endif

                        <!-- Riepilogo contanti -->
                        <div class="border-t pt-4 space-y-2">
                            <div class="flex justify-between text-lg">
                                <span>Totale contanti incassati (X):</span>
                                <strong>€ {{ number_format($this->getCashTotal(), 2) }}</strong>
                            </div>

                            @if($bancaleCost > 0)
                                <div class="flex justify-between text-red-600">
                                    <span>- Costo bancale:</span>
                                    <strong>- € {{ number_format($bancaleCost, 2) }}</strong>
                                </div>
                            @endif

                            <div class="flex justify-between text-xl font-bold border-t pt-3
                                {{ $this->getFinalCash() > 0 ? 'text-green-600' : 'text-red-700' }}">
                                <span>TOTALE DA RICEVERE:</span>
                                <span>€ {{ number_format($this->getFinalCash(), 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button wire:click="closeModal"
                                class="px-5 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- resources/views/livewire/table-manager/table-splitter.blade.php --}}
<div x-data="{ confirmModal: false, confirmData: {} }">
    {{-- Header con azioni --}}
    <div class="mb-6 p-4 bg-gray-50 border rounded-lg flex flex-wrap items-center justify-between shadow-sm">
        <div class="flex items-center space-x-4 mb-2 md:mb-0">
            <label for="bancaleCost" class="font-medium text-gray-700">Costo Bancale (€):</label>
            <input type="number" step="0.01" wire:model.live.debounce.300ms="bancaleCost"
                   class="border rounded p-2 w-32 text-right">
        </div>

        <div class="flex flex-wrap gap-3">
            <button wire:click="generateTable" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Rigenera
            </button>
            <button wire:click="printSplitTable" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Stampa Tabella
            </button>
            <button wire:click="printAgencyReport" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                Report Agenzie
            </button>
            <button wire:click="$dispatch('goToAssignmentTable')"
                    class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                ← Torna
            </button>
        </div>
    </div>

    {{-- LAVORI NON ASSEGNATI --}}
    @if($unassignedWorks)
    <div class="mb-8 p-5 bg-yellow-50 border-2 border-yellow-400 rounded-lg">
        <h3 class="text-lg font-bold text-yellow-800 mb-3">
            Lavori non assegnati ({{ count($unassignedWorks) }})
            @if($selectedWork)
                → <span class="text-blue-700 font-bold">{{ $selectedWork['value'] }}</span>
                alle {{ \Carbon\Carbon::parse($selectedWork['timestamp'])->format('H:i') }}
                <button wire:click="deselectWork" class="ml-2 text-xs underline">deseleziona</button>
            @endif
        </h3>
        <div class="flex flex-wrap gap-3">
            @foreach($unassignedWorks as $index => $work)
                <div wire:click="selectUnassignedWork({{ $index }})"
                     wire:key="unassigned-{{ $index }}"
                     class="px-5 py-3 rounded-lg border-2 cursor-pointer text-center min-w-36 transition-all
                            {{ $selectedWork && ($selectedWork['id'] ?? null) == ($work['id'] ?? null) ? 'bg-blue-600 text-white border-blue-800 scale-105 shadow-lg' : 'bg-white hover:border-blue-500' }}">
                    <div class="font-bold text-xl">{{ $work['value'] === 'A' ? ($work['agency_code'] ?? 'A') : $work['value'] }}</div>
                    @if($work['agency'] ?? null)
                        <div class="text-xs">{{ $work['agency']['name'] }}</div>
                    @endif
                    <div class="text-sm font-medium mt-1">
                        {{ \Carbon\Carbon::parse($work['timestamp'])->format('H:i') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- TABELLA MATRICE --}}
    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table class="min-w-full bg-white border-collapse">
            <thead>
                <tr class="bg-gray-800 text-white text-xs">
                    <th class="sticky left-0 bg-gray-800 z-10 border px-4 py-2">Licenza</th>
                    <th class="border px-3 py-2">Turno</th>
                    <th class="border px-3 py-2">N</th>
                    <th class="border px-3 py-2">P</th>
                    <th class="border px-3 py-2">Capacità</th>
                    <th class="border px-3 py-2">Contanti</th>
                    @for($i = 1; $i <= 25; $i++)
                        <th class="border px-2 py-1">{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $licenseKey => $license)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <td class="sticky left-0 bg-inherit z-10 border px-4 py-2 font-bold text-blue-700">
                            {{ $license['user']['license_number'] ?? '—' }}
                        </td>
                        <td class="border text-center">
                            <span class="px-2 py-1 rounded text-white text-xs font-bold
                                @if($license['shift'] ?? '' === 'morning') bg-green-600
                                @elseif($license['shift'] ?? '' === 'afternoon') bg-orange-600
                                @else bg-gray-600 @endif">
                                {{ ucfirst($license['shift'] ?? 'full') }}
                            </span>
                        </td>
                        <td class="border text-center">{{ collect($license['worksMap'])->where('value','N')->count() }}</td>
                        <td class="border text-center">{{ collect($license['worksMap'])->where('value','P')->count() }}</td>
                        <td class="border text-center font-bold">
                            {{ collect($license['worksMap'])->filter()->count() }} / {{ $license['capacity'] ?? 25 }}
                        </td>
                        <td class="border text-center">
                            {{ number_format(collect($license['worksMap'])->where('value','X')->sum('amount') - $bancaleCost, 2) }} €
                        </td>

                        @foreach($license['worksMap'] as $slotIndex => $work)
                            @php $isEmpty = is_null($work); @endphp
                            <td wire:key="cell-{{ $licenseKey }}-{{ $slotIndex }}"
                                class="border p-1 text-center h-16 transition-all cursor-pointer
                                       {{ $isEmpty ? 'bg-gray-100 hover:bg-green-100' : 'bg-white hover:bg-red-100' }}
                                       {{ $selectedWork && $isEmpty ? 'ring-4 ring-blue-400 ring-opacity-50' : '' }}"

                                @if($isEmpty)
                                    wire:click="assignToSlot({{ $licenseKey }}, {{ $slotIndex }})"
                                    title="Assegna lavoro selezionato"
                                @else
                                    wire:click="removeWork({{ $licenseKey }}, {{ $slotIndex }})"
                                    title="Clicca per rimuovere"
                                @endif
                            >
                                @if($work)
                                    <div class="text-xs leading-tight">
                                        <div class="font-bold text-lg
                                            @if($work['value'] === 'A') text-blue-700
                                            @elseif($work['value'] === 'X') text-purple-700
                                            @elseif(in_array($work['value'], ['P','N'])) text-green-700
                                            @else text-gray-700 @endif">
                                            {{ $work['value'] === 'A' ? ($work['agency_code'] ?? 'A') : $work['value'] }}
                                        </div>
                                        <div class="text-gray-600">
                                            {{ \Carbon\Carbon::parse($work['timestamp'])->format('H:i') }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

   {{-- MODALE CONFERMA RIMOZIONE --}}
<div x-show="confirmModal" x-transition
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
     x-on:click.away="confirmModal = false">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4" @click.stop>
        <h3 class="text-xl font-bold text-red-700 mb-4">Rimuovere questo lavoro?</h3>
        <div class="text-sm space-y-1 mb-6 bg-gray-50 p-3 rounded">
            <div><strong>Tipo:</strong> <span x-text="confirmData.work?.value?.toUpperCase()"></span></div>
            <div><strong>Agenzia:</strong> 
                <span x-text="confirmData.work?.agency?.name || confirmData.work?.agency_code || '—'"></span>
            </div>
            <div><strong>Ora:</strong> 
                <span x-text="new Date(confirmData.work?.timestamp).toLocaleTimeString('it-IT', {hour:'2-digit', minute:'2-digit'})"></span>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <button @click="confirmModal = false" 
                    class="px-4 py-2 border rounded hover:bg-gray-100">
                Annulla
            </button>
            <button @click="$dispatch('confirmed-remove', confirmData); confirmModal = false"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-medium">
                Rimuovi dalla matrice
            </button>
        </div>
    </div>
</div>

    {{-- SCRIPT ALPINE + LIVEWIRE --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-confirm-remove', (data) => {
                window.confirmModal = true;
                window.confirmData = data;
            });

            Livewire.on('work-selected', () => {
                document.querySelectorAll('td[wire\\:click*="assignToSlot"]').forEach(el => {
                    el.classList.add('ring-4', 'ring-blue-400', 'ring-opacity-50');
                });
            });

            Livewire.on('work-deselected', () => {
                document.querySelectorAll('td').forEach(el => {
                    el.classList.remove('ring-4', 'ring-blue-400', 'ring-opacity-50');
                });
            });
        });
    </script>
</div>
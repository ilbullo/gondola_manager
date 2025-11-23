<div wire:refresh>
    <!-- Modale caricamento -->
    <div id="loading-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg shadow-lg flex items-center space-x-2">
            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 text-sm sm:text-base">Caricamento...</span>
        </div>
    </div>

    <!-- Layout principale -->
    <div class="flex flex-col md:flex-row gap-3 sm:gap-4 h-[calc(100vh-2rem)]">
        <!-- Sidebar -->
        <livewire:layout.sidebar />

        <!-- Tabella -->
        <div class="w-full bg-white p-3 sm:p-4 shadow-md overflow-x-auto">
            <div class="min-w-[1200px] table-fixed w-full">
                <table class="w-full border-collapse table-fixed">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left text-sm font-semibold text-gray-700 sticky left-0 bg-gray-100 border-b w-20 z-10">Licenza</th>
                            @for ($i = 1; $i <= 25; $i++)
                                <th class="p-2 text-center text-sm font-semibold text-gray-700 border-b w-10">{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody id="sortable">
                        @foreach($licenses as $license)
                            <tr class="border-b">
                                <td class="p-2 text-sm text-gray-900 font-medium sticky left-0 bg-white z-10">
                                    <div class="flex items-center space-x-2">
                                        <span>{{ $license['user']['license_number'] ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                @for ($slot = 0; $slot <= 24; $slot++)
                                    @php
                                        $index = $slot + 1;
                                    @endphp
                                    <td class="p-1 text-center text-xs border cursor-pointer {{ \App\Enums\WorkType::tryFrom($license['worksMap'][$slot]['value']?? '')?->colourClass() }}"
                                     {{-- wire:click="@if (isset($license['worksMap'][$slot])) openConfirmRemove({{ $license['id'] }}, {{ $index }}) @else assignWork({{ $license['id'] }}, {{ $index }}) @endif" --}}
                                        wire:click="@if (isset($license['worksMap'][$slot])) openInfoBox(@js($license['worksMap'][$slot] ?? null),{{ $index }}) @else assignWork({{ $license['id'] }}, {{ $index }}) @endif" 
                                        >
                                        @if (isset($license['worksMap'][$slot]))
                                            <span class="text-gray-900 font-medium">
                                                @if ($license['worksMap'][$slot]['value'] === 'A')
                                                    {{ $license['worksMap'][$slot]['agency_code'] ?? 'N/A' }}
                                                    @if ($license['worksMap'][$slot]['voucher'])
                                                        ({{ Str::limit($license['worksMap'][$slot]['voucher'],4,'') }})
                                                    @endif
                                                @elseif ($license['worksMap'][$slot]['value'] === 'X')
                                                    X
                                                    @if ($license['worksMap'][$slot]['voucher'])
                                                       ({{ Str::limit($license['worksMap'][$slot]['voucher'],4,'') }})
                                                    @endif
                                                @elseif ($license['worksMap'][$slot]['value'] === 'P')
                                                    P
                                                @elseif ($license['worksMap'][$slot]['value'] === 'N')
                                                    N
                                                @else
                                                    {{ $license['worksMap'][$slot]['value'] }} <!-- Fallback per valore sconosciuto -->
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    @livewire('component.rules-modal')
                </div>
            </div>
        </div>
    </div>
</div>

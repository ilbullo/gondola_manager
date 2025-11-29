{{-- resources/views/debug/matrix-preview.blade.php --}}

<div class="max-w-7xl mx-auto p-6 bg-gray-50 rounded-lg shadow-lg font-mono text-xs">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Matrice Finale – {{ now()->format('d/m/Y') }}
        <span class="text-sm font-normal text-gray-600">
            ({{ $matrix->count() }} licenze × 25 slot)
        </span>
    </h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-gray-300 bg-white">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th class="border border-gray-600 px-3 py-2 text-left sticky left-0 bg-gray-800 z-10">#</th>
                    <th class="border border-gray-600 px-4 py-2 text-left sticky left-0 bg-gray-800 z-10">User</th>
                    <th class="border border-gray-600 px-3 py-2 text-center">Turno</th>
                    <th class="border border-gray-600 px-3 py-2 text-center">Capacità</th>
                    @for ($i = 1; $i <= 25; $i++)
                        <th class="border border-gray-600 px-2 py-1 text-center">{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($matrix as $index => $license)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                        <!-- Indice licenza -->
                        <td class="border border-gray-300 px-3 py-2 font-bold sticky left-0 bg-inherit">
                            {{ $index + 1 }}
                        </td>

                        <!-- User + ID -->
                        <td class="border border-gray-300 px-4 py-2 font-medium sticky left-16 bg-inherit">
                            <div class="flex items-center space-x-2">
                                @if($license['user'])
                                    <span class="text-blue-700 font-bold">{{ $license['user']['license_number'] ?? 'N/D' }}</span>
                                @else
                                    <span class="text-gray-400 italic">—</span>
                                @endif
                            </div>
                        </td>

                        <!-- Turno -->
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <span class="px-2 py-1 rounded text-white text-xs font-bold
                                @if($license['shift'] === 'morning') bg-green-600
                                @elseif($license['shift'] === 'afternoon') bg-orange-600
                                @else bg-gray-600 @endif">
                                {{ ucfirst($license['shift'] ?? 'full') }}
                            </span>
                        </td>

                        <!-- Capacità / Usati -->
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <span class="font-bold text-gray-700">
                                {{ collect($license['worksMap'])->filter()->count() }}
                            </span>
                            <span class="text-gray-500"> / {{ $license['capacity'] ?? 25 }}</span>
                        </td>

                        <!-- Slot 0–24 -->
                        @foreach ($license['worksMap'] as $slot => $work)
                            <td class="border border-gray-300 p-1 text-center align-middle h-12">
                                @if ($work)
                                    <div class="text-xs leading-tight">
                                        <div class="font-bold
                                            @if($work['value'] === 'A') text-blue-700
                                            @elseif($work['value'] === 'X') text-purple-700
                                            @elseif(in_array($work['value'], ['P','N'])) text-green-700
                                            @else text-gray-700 @endif">
                                            {{ $work['value'] === 'A' ? $work['agency_code'] : $work['value'] }}
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

    <!-- Legenda veloce -->
    <div class="mt-6 text-sm text-gray-600">
        <strong>Legenda:</strong>
        <span class="mx-3"><strong class="text-blue-700">A</strong> = Agenzie</span>
        <span class="mx-3"><strong class="text-purple-700">X</strong> = Extra</span>
        <span class="mx-3"><strong class="text-green-700">P/N</strong> = Presenze</span>
    </div>
</div>
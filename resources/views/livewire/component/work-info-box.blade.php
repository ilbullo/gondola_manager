@props(['class' => ''])

<div {{ $attributes->merge(['class' => $class]) }}>
    @if($isVisible)
        <div class="mt-4 px-3 py-3 bg-gray-50 border-l-4 border-blue-600 rounded-r-md text-xs font-medium">

            <!-- Riga 1 – Tipo lavoro + agenzia -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex-1 min-w-0 truncate font-bold text-blue-900">
                    {{ $label }}
                    @if($workType === 'A' && $agencyName)
                        <span class="text-blue-700">→ {{ $agencyName }}</span>
                    @endif
                </div>
            </div>

            <!-- Riga 2 – Voucher (solo se presente) -->
            @if(filled($voucher))
                <div class="mt-2.5 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg font-bold text-center ring-1 ring-emerald-300">
                    {{ $voucher }}
                </div>
            @endif

            <!-- Riga 3 – Importo, posti, flag + check verde -->
            <div class="mt-2.5 flex items-center justify-between text-xs">
                <div class="flex items-center gap-3 text-gray-700">
                    <span class="font-bold text-indigo-600">€{{ $amount }}</span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h8"/>
                        </svg>
                        <span class="font-semibold">{{ $slotsOccupied }} {{ Str::plural('posto', $slotsOccupied) }}</span>
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @if($sharedFromFirst)
                        <span class="px-1.5 py-0.5 bg-amber-500 text-white rounded text-[10px] font-bold">R1</span>
                    @endif
                    @if($excluded)
                        <span class="px-1.5 py-0.5 bg-red-500 text-white rounded text-[10px] font-bold">X</span>
                    @endif
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>
    @endif
</div>
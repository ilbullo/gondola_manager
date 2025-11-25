<div
    role="gridcell"
    aria-label="{{ $this->ariaLabel() }}"
    @if($hasWork) tabindex="0" @endif
    class="p-1 text-center focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-inset rounded"
    @if($hasWork)
        wire:keydown.enter.prevent="$dispatch('cellClicked', { licenseId: {{ $licenseId }}, slot: {{ $slot }} })"
        wire:keydown.space.prevent="$dispatch('cellClicked', { licenseId: {{ $licenseId }}, slot: {{ $slot }} })"
    @endif
>
    @if ($hasWork)
        <div class="flex flex-col items-center justify-center min-h-10">
            <span class="text-sm font-bold leading-tight">
                @if ($work['value'] === 'A')
                    {{ $work['agency_code'] ?? 'N/A' }}
                @elseif ($work['value'] === 'X')
                    X
                @elseif (in_array($work['value'] ?? '', ['P', 'N']))
                    {{ $work['value'] }}
                @else
                    {{ $work['value'] ?? '-' }}
                @endif
            </span>

            {{-- Voucher a capo riga, piccolo e troncato se troppo lungo --}}
            @if (!empty($work['voucher']))
                <span class="text-[10px] font-medium text-gray-500 mt-0.5 truncate max-w-full" title="{{ $work['voucher'] }}">
                    ({{ $work['voucher'] }})
                </span>
            @endif

            {{-- Badge F o R --}}
            @if ($badge = $this->badge())
                <span class="inline-block px-1.5 py-0.5 mt-1 text-[9px] font-bold rounded-full {{ $badge['class'] }}">
                    {{ $badge['label'] }}
                </span>
            @endif
        </div>
    @else
        <span class="text-gray-300 text-sm select-none" aria-hidden="true">–</span>
    @endif
</div>
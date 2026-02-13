@props(['work', 'mode' => 'matrix'])

@php
    // 1. Normalizzazione: supporta sia Array (Matrix/Splitter) che Oggetti (Ripartizione)
    $w = is_array($work) ? (object) $work : $work;
    
    // 2. Inizializzazione variabili
    $colorClass = "text-white";
    $label = "";
    $voucher = $w->voucher ?? null;
    $value = $w->value ?? '';

    // 3. Logica Colore e Label (Tua logica originale centralizzata)
    if ($w) {
        $type = \App\Enums\WorkType::tryFrom($value);
        
        if ($type === \App\Enums\WorkType::AGENCY) {
            // Priorità colore: dato diretto (array) -> relazione (oggetto) -> default
            $agencyColor = $w->agency_colour ?? ($w->agency->colour ?? 'indigo');
            $colorClass .= " bg-{$agencyColor}-600";
            // Priorità codice: dato diretto -> relazione -> default
            $label = $w->agency_code ?? ($w->agency->code ?? 'A');
        } else {
            // Colore standard dall'Enum
            $colorClass .= " " . ($type?->colourButtonsClass() ?? 'bg-indigo-600');
            $label = $value;
        }
    }

    // 4. Classi di layout in base al contesto (Matrix vs Table)
    $layoutClasses = $mode === 'matrix' 
        ? 'w-11 h-11 rounded-xl shadow-md flex-col' 
        : 'w-full h-full py-1 flex-col rounded-sm';
@endphp

<div {{ $attributes->merge(['class' => "relative flex items-center justify-center font-bold transition-all $colorClass $layoutClasses"]) }}>
    
    {{-- Badge Automatici (se presenti nei dati) --}}
    @if($w->excluded ?? false) 
        <x-badge name="excluded" /> 
    @endif
    
    @if($w->shared_from_first ?? false) 
        <x-badge name="shared_ff" /> 
    @endif

    {{-- Valore Principale (es: A1, X, P, N) --}}
    <span class="{{ $mode === 'matrix' ? 'text-[10px]' : 'text-[9px]' }} uppercase leading-none font-black">
        {{ $label }}
    </span>
    
    {{-- Voucher (se presente) --}}
    @if($voucher)
        <span class="text-[0.7em] opacity-80 truncate px-1 mt-0.5 font-bold leading-none">
            {{ Str::limit($voucher, 5, '') }}
        </span>
    @endif

    {{-- Slot per contenuti extra (es: "DA: 123" nello splitter) --}}
    @if(isset($slot) && $slot->isNotEmpty())
        <div class="leading-none mt-0.5">
            {{ $slot }}
        </div>
    @endif
</div>
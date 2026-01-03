<div class="flex items-center gap-3">
    <span class="text-[9px] font-black text-slate-500 uppercase italic tracking-[0.1em] mr-2">Monitor Lavori:</span>

    <div class="flex items-center gap-2">
        @foreach(\App\Enums\WorkType::cases() as $type)
            <div class="flex items-center gap-2 bg-slate-800/50 px-3 py-1.5 rounded-xl border border-slate-700/50 min-w-[70px]">
                {{-- Usiamo il metodo dell'Enum per il colore --}}
                <span class="w-1.5 h-1.5 rounded-full {{ $type->colourButtonsClass() }}"></span>

                {{-- Usiamo il nome o un'etichetta dell'Enum --}}
                <span class="text-[9px] font-black uppercase text-slate-400">
                    {{ $type->label() }}
                </span>

                <span class="ml-auto text-xs font-black text-white leading-none">
                    {{ $counts[$type->value] ?? 0 }}
                </span>
            </div>
        @endforeach

        <div class="h-4 w-px bg-slate-700 mx-1"></div>

        <div class="flex items-center gap-1 bg-indigo-500/10 px-3 py-1.5 rounded-xl border border-indigo-500/20">
            <span class="text-[9px] font-black uppercase text-indigo-400">Totale</span>
            <span class="text-xs font-black text-indigo-400 leading-none">{{ $total }}</span>
        </div>
    </div>
</div>

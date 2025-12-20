<header class="bg-slate-900 text-white shadow-2xl z-50 flex flex-wrap items-center px-4 py-3 gap-3 shrink-0">
    <div class="flex gap-2 border-r border-white/10 pr-4">
        <button wire:click="editTable()" class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center hover:bg-rose-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7" stroke-width="3"/></svg>
        </button>
    </div>

    <div class="flex gap-1 bg-white/10 p-1 rounded-xl h-[50px]">
        @foreach(\App\Enums\WorkType::values([\App\Enums\WorkType::EXCLUDED, \App\Enums\WorkType::FIXED]) as $type)
            @php
                $colour = \App\Enums\WorkType::tryFrom($type)->colourButtonsClass();
            @endphp
            <button wire:click="setWorkType('{{ $type }}')"
                class="w-10 rounded-lg font-black transition-all {{ $workType == $type ? ($colour . ' shadow-lg scale-105') : 'text-slate-500' }}">
                {{ $type }}
            </button>
        @endforeach
    </div>

    <div class="config-item relative shrink-0">
        <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Prezzo</div>
        <input type="number" wire:model.live="amount" class="w-20 h-full bg-white/10 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-emerald-400 outline-none">
    </div>

    <div class="config-item relative shrink-0">
        <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Slots</div>
        <select wire:model.live="slotsOccupied" class="w-16 h-full bg-white/10 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-white outline-none appearance-none">
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>
    </div>

    @if($workType === 'A')
    <div wire:click="setWorkType('A')" class="config-item px-3 bg-indigo-500 rounded-xl gap-3 shadow-lg cursor-pointer hover:bg-indigo-400">
        <div class="flex flex-col">
            <span class="text-[7px] font-black text-indigo-200 uppercase">Agenzia</span>
            <span class="text-[10px] font-black text-white uppercase">{{ $agencyName ?? 'Seleziona...' }}</span>
        </div>
    </div>
    @endif

    <div class="config-item relative shrink-0">
        <div class="absolute left-3 top-1 text-[7px] font-black text-slate-500 uppercase">Voucher</div>
        <input type="text" wire:model.live="voucher" class="w-40 h-full bg-white/5 border border-white/10 rounded-xl pl-3 pt-2 text-xl font-black text-white uppercase outline-none">
    </div>

    <div class="flex gap-1.5 bg-white/5 p-1 rounded-xl border border-white/10">
        <button wire:click="$toggle('excluded')" class="h-10 px-3 rounded-lg text-[8px] font-black uppercase {{ $excluded  ? 'bg-indigo-600' : 'text-slate-500' }}">Lavoro Fisso</button>
        <button wire:click="$toggle('sharedFromFirst')" class="h-10 px-3 rounded-lg text-[8px] font-black uppercase {{ $sharedFromFirst ? 'bg-cyan-600' : 'text-slate-500' }}">Condiviso 1°</button>
    </div>

    <button wire:click="$dispatch('callRedistributeWorks')" class="config-item px-4 ml-auto bg-amber-500 hover:bg-amber-400 rounded-xl shadow-lg">
        <span class="text-[10px] font-black text-slate-900 uppercase">Ripartizione</span>
    </button>
</header>

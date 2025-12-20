{{-- resources/views/livewire/ui/partials/work-info-front.blade.php --}}
<div class="h-full flex flex-col">
    @php $work = $this->workData; @endphp
    {{-- Intestazione Dinamica --}}
    <div class="{{ \App\Enums\WorkType::tryFrom($work['value'])->colourButtonsClass() }} p-8 text-center text-white shrink-0">
        <div class="w-20 h-20 bg-white/20 rounded-3xl mx-auto flex items-center justify-center text-3xl font-black mb-4 shadow-xl border border-white/20">
            {{ $work['agency']['code'] ?? $work['value'] }}
        </div>
        <h3 class="text-lg font-black uppercase tracking-widest">
            {{ $work['agency']['name'] ?? 'Lavoro Diretto' }}
        </h3>
    </div>

    <div class="p-8 space-y-6 flex-1 overflow-y-auto">
        <div class="flex justify-between items-start border-b border-slate-100 pb-4">
            <div>
                <span class="text-[10px] font-black text-slate-400 uppercase block">Inizio</span>
                <span class="text-3xl font-black text-slate-800">{{ $work['departure_time'] }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black text-rose-500 uppercase block">Attesa</span>
                <span class="text-sm font-black text-slate-600 bg-slate-100 px-3 py-1 rounded-full mt-1 inline-block">
                    {{ $work['time_elapsed'] }}
                </span>
            </div>
        </div>

        @if($work['voucher'])
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <span class="text-[9px] font-black text-slate-400 uppercase block mb-1">Codice Voucher</span>
                <span class="text-xl font-black text-indigo-600 uppercase">{{ $work['voucher'] }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
            <span class="text-xs font-black text-emerald-700 uppercase">Totale</span>
            <span class="text-2xl font-black text-emerald-600">€{{ number_format($work['amount'], 2) }}</span>
        </div>

        {{-- Footer Azioni --}}
        <div class="grid grid-cols-2 gap-3 pt-2">
            <button @click="flipped = true" class="py-4 bg-slate-100 text-slate-600 rounded-2xl font-black uppercase text-[10px] hover:bg-slate-200">Modifica</button>
            <button wire:click="confirmDelete({{ $work['id'] }})" class="py-4 bg-rose-50 text-rose-600 rounded-2xl font-black uppercase text-[10px] hover:bg-rose-100">Elimina</button>
        </div>
        
        <button wire:click="closeModal" class="w-full py-5 bg-slate-900 text-white rounded-[1.5rem] font-black uppercase text-xs tracking-widest">Chiudi</button>
    </div>
</div>
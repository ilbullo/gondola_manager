{{-- resources/views/livewire/ui/partials/work-info-front.blade.php --}}
<div class="h-full flex flex-col bg-white relative">
    @php
        $work = $workData;
        $workType = isset($work['value']) ? \App\Enums\WorkType::tryFrom($work['value']) : null;
        $colorClass = $workType ? $workType->colourButtonsClass() : 'bg-slate-600';
    @endphp

    {{-- Tasto Chiudi --}}
    <button type="button"
            @click="$wire.closeModal()"
            class="absolute top-4 right-4 z-50 w-10 h-10 flex items-center justify-center bg-black/10 hover:bg-black/20 rounded-full text-white transition-all backdrop-blur-sm">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Header Originale --}}
    <div class="{{ $colorClass }} p-8 text-center text-white shrink-0">
        <div class="w-20 h-20 bg-white/20 rounded-3xl mx-auto flex items-center justify-center text-3xl font-black mb-4 shadow-xl border border-white/20">
            {{ $work['agency_code'] ?: ($work['value'] ?? '?') }}
        </div>
        <h3 class="text-lg font-black uppercase tracking-widest">
            {{ $work['agency'] ?: ($workType ? $workType->label() : 'Lavoro') }}
        </h3>
    </div>

    {{-- Contenuto Centrale --}}
    <div class="p-8 space-y-6 flex-1 overflow-y-auto">
        <div class="flex justify-between items-start border-b border-slate-100 pb-4">
            <div>
                <span class="text-[10px] font-black text-slate-400 uppercase block">Inizio</span>
                <span class="text-3xl font-black text-slate-800">{{ $work['departure_time'] ?? '--:--' }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] font-black text-rose-500 uppercase block">Partito</span>
                <span class="text-sm font-black text-slate-600 bg-slate-100 px-3 py-1 rounded-full mt-1 inline-block">
                    {{ $work['time_elapsed'] ?? '' }}
                </span>
            </div>
        </div>

        @if(!empty($work['voucher']))
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <span class="text-[9px] font-black text-slate-400 uppercase block mb-1">Codice Voucher</span>
                <span class="text-xl font-black text-indigo-600 uppercase break-words">{{ $work['voucher'] }}</span>
            </div>
        @endif

        <div class="flex justify-between items-center bg-emerald-50 p-4 rounded-2xl border border-emerald-100">
            <span class="text-xs font-black text-emerald-700 uppercase">Totale</span>
            <span class="text-2xl font-black text-emerald-600">€{{ number_format($work['amount'] ?? 0, 2) }}</span>
        </div>
    </div>

    {{-- Footer Originale --}}
    <div class="p-6 bg-slate-50 border-t border-slate-100 shrink-0">
        <div class="grid grid-cols-2 gap-3">
            <button type="button" @click="flipped = true"
                    class="py-4 bg-white border border-slate-200 text-slate-600 rounded-2xl font-black uppercase text-[10px] hover:bg-slate-50 transition-colors shadow-sm">
                Modifica
            </button>
            <button type="button" wire:click="confirmDelete({{ $work['id'] ?? 0 }})"
                    class="py-4 bg-rose-50 text-rose-600 rounded-2xl font-black uppercase text-[10px] hover:bg-rose-100 transition-colors">
                Elimina
            </button>
        </div>
    </div>
</div>
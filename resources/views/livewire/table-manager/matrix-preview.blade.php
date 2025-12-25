{{-- resources/views/livewire/table-manager/table-splitter.blade.php --}}
<div class="h-screen flex flex-col bg-slate-100 overflow-hidden">
    
    {{-- MODALE COSTO BANCALE --}}
    @if ($showBancaleModal)
        <div x-data="{ open: @entangle('showBancaleModal') }" x-show="open" 
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/90 backdrop-blur-md">
            
            <div x-show="open" x-transition.scale.95 
                class="relative w-full max-w-sm bg-white rounded-[2rem] shadow-2xl overflow-hidden border border-slate-200">
                
                <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
                    <h2 class="text-lg font-black uppercase italic tracking-tighter">Costo Bancale</h2>
                    <button @click="$wire.closeBancaleModal()" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="w-12 h-12 shrink-0 bg-amber-100 rounded-xl flex items-center justify-center">
                            <span class="text-xl font-black text-amber-500">€</span>
                        </div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-tight">
                            Verrà sottratto dal totale contanti odierno (X).
                        </p>
                    </div>

                    <div class="relative">
                        <input type="number" id="bancale-input" wire:model.live="bancaleCost" step="0.01"
                            class="block w-full py-4 text-4xl font-black text-center text-slate-800 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-indigo-100 transition-all"
                            placeholder="0.00" autofocus @keydown.enter="$wire.confirmBancaleCost()" />
                    </div>

                    <button type="button" wire:click="confirmBancaleCost"
                        class="w-full py-4 text-lg font-black uppercase tracking-tighter text-white bg-indigo-600 hover:bg-indigo-500 rounded-2xl shadow-lg shadow-indigo-100 transition-all active:scale-95">
                        Conferma →
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- HEADER AZIONI --}}
    <header class="bg-slate-900 text-white p-4 shadow-2xl z-40 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-6">
            <h1 class="text-2xl font-black uppercase italic tracking-tighter border-r border-white/10 pr-6">Splitter</h1>
            <div class="flex items-center gap-3 bg-white/5 p-2 rounded-xl border border-white/10">
                <span class="text-[8px] font-black text-slate-500 uppercase">Costo Bancale</span>
                <input type="number" step="1" wire:model.live.debounce.300ms="bancaleCost"
                    class="w-20 bg-transparent border-none text-xl font-black text-emerald-400 p-0 focus:ring-0">
            </div>
        </div>
        
        <div class="flex gap-2">
            <button wire:click="printSplitTable" 
                class="flex items-center gap-2 px-4 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-[10px] font-black uppercase transition-all shadow-lg active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                Stampa Tabella
            </button>
            <button wire:click="printAgencyReport" 
                class="flex items-center gap-2 px-4 py-4 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-[10px] font-black uppercase transition-all shadow-lg active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Report Agenzie
            </button>
            <button wire:click="$dispatch('goToAssignmentTable')" 
                class="flex items-center gap-2 px-4 py-4 bg-amber-500 hover:bg-amber-400 text-slate-900 rounded-xl text-[10px] font-black uppercase transition-all shadow-lg active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span class="hidden lg:block">Indietro</span>
            </button>
        </div>
    </header>

    <main class="flex-1 flex flex-col p-4 gap-4 overflow-hidden {{ $showBancaleModal ? 'blur-sm pointer-events-none' : '' }}">

        {{-- AREA LAVORI DA ASSEGNARE --}}
        @if ($unassignedWorks && count($unassignedWorks) > 0)
            <div class="bg-white rounded-[2rem] p-6 shadow-xl border border-slate-200 shrink-0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Lavori In Sospeso ({{ count($unassignedWorks) }})</h2>
                    @if ($selectedWork)
                        <div class="flex items-center gap-3 animate-pulse">
                            <span class="text-[10px] font-black text-indigo-600 uppercase italic">Selezionato: {{ strtoupper($selectedWork['value']) }}</span>
                            <button wire:click="deselectWork" class="text-[9px] font-bold text-slate-400 underline">ANNULLA</button>
                        </div>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($unassignedWorks as $index => $work)
                        <button wire:click="selectUnassignedWork({{ $index }})"
                            class="group relative h-12 w-16 rounded-xl border-2 transition-all hover:scale-105 flex flex-col items-center justify-center
                                {{ $selectedWork && data_get($selectedWork, 'id') == data_get($work, 'id') ? 'border-indigo-600 bg-indigo-50 shadow-lg ring-2 ring-indigo-200' : 'border-slate-100 bg-slate-50' }}">
                            <span class="text-xs font-black {{ $selectedWork && data_get($selectedWork, 'id') == data_get($work, 'id') ? 'text-indigo-600' : 'text-slate-700' }}">
                                {{ $work['value'] === 'A' ? $work['agency_code'] ?? 'A' : strtoupper($work['value']) }}
                            </span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase">
                                {{ \Carbon\Carbon::parse($work['timestamp'])->format('H:i') }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- MATRICE SPLITTER --}}
        <div class="flex-1 bg-white rounded-[2rem] shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
            <div class="flex-1 overflow-auto">
                <table class="w-full border-separate border-spacing-0">
                    <thead class="sticky top-0 z-30">
                        <tr class="bg-slate-50">
                            <th class="p-4 text-[9px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 sticky left-0 bg-slate-50 z-40 w-24">Licenza</th>
                            <th class="p-4 text-[9px] font-black text-slate-400 uppercase border-b border-slate-200">N</th>
                            <th class="p-4 text-[9px] font-black text-slate-400 uppercase border-b border-slate-200">X</th>
                            <th class="p-4 text-[9px] font-black text-slate-400 uppercase border-b border-slate-200">Capacità</th>
                            <th class="p-4 text-[9px] font-black text-slate-400 uppercase border-b border-slate-200">Netto €</th>
                            @for ($i = 1; $i <= config('app_settings.matrix.total_slots'); $i++)
                                <th class="p-2 text-[9px] font-black text-slate-300 border-b border-slate-200 min-w-[3.5rem]">{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($matrix as $licenseKey => $license)
                            @php
                                // 1. Calcolo Wallet specifico per la riga
                                $nCount = collect($license['worksMap'])->where('value', 'N')->count();
                                $walletDiff = ($nCount * (config('app_settings.works.default_amount') ?? 90 )) - (float)($license['wallet'] ?? 0);

                                // 2. Applichiamo il SERVICE centralizzato
                                $liq = \App\Services\LiquidationService::calculate(
                                    $license['worksMap'], 
                                    $walletDiff, 
                                    $this->bancaleCost
                                );

                                $occupied = collect($license['worksMap'])->filter()->count();
                                $maxCapacity = $license['target_capacity'] ?? config('app_settings.matrix.total_slots');
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="p-3 sticky left-0 bg-white group-hover:bg-slate-50 z-20 border-r border-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                    <div class="flex flex-col items-center gap-1">
                                        <div class="flex items-center gap-1">
                                            <span class="w-8 h-8 flex items-center justify-center bg-slate-800 text-white rounded-lg font-black text-xs">{{ $license['user']['license_number'] }}</span>
                                            @if($license['only_cash_works']) <span class="text-[8px] bg-emerald-100 text-emerald-700 px-1 rounded font-black">X</span> @endif
                                        </div>
                                        <button wire:click="$dispatch('open-license-receipt', { license: {{ \Illuminate\Support\Js::from($license) }}, bancaleCost: {{ $bancaleCost }} })"
                                            class="text-[8px] font-black uppercase text-indigo-600 hover:text-indigo-800 transition">Scontrino</button>
                                    </div>
                                </td>

                                <td class="p-3 text-center font-black text-amber-500 bg-amber-50/30">{{ $liq['counts']['n'] }}</td>
                                <td class="p-3 text-center font-black text-emerald-500 bg-emerald-50/30">{{ $liq['counts']['x'] }}</td>
                                <td class="p-3 text-center">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black {{ $maxCapacity > 0 && $occupied >= $maxCapacity ? 'text-rose-600' : 'text-slate-700' }}">{{ $occupied }}/{{ $maxCapacity }}</span>
                                        @if ($maxCapacity > 0 && $occupied >= $maxCapacity) <span class="text-[7px] font-black text-rose-400 uppercase">Piena</span> @endif
                                    </div>
                                </td>
                                {{-- NETTO DERIVATO DAL SERVICE --}}
                                <td class="p-3 text-center font-black text-emerald-600 italic">
                                    {{ number_format($liq['money']['netto'], 0) }}
                                </td>

                                @for ($slotIndex = 1; $slotIndex <= config('app_settings.matrix.total_slots'); $slotIndex++)
                                    @php
                                        $work = $license['worksMap'][$slotIndex] ?? null;
                                        $isEmpty = is_null($work);
                                    @endphp
                                    <td class="p-1 border-r border-slate-50 transition-all {{ $isEmpty ? 'hover:bg-slate-100' : '' }}"
                                        wire:click="{{ $isEmpty ? 'assignToSlot(' . $licenseKey . ', ' . $slotIndex . ')' : 'removeWork(' . $licenseKey . ', ' . $slotIndex . ')' }}">
                                        
                                        @if ($work)
                                            <div class="relative h-12 w-full rounded-lg flex flex-col items-center justify-center shadow-sm transition-transform active:scale-90 {{ \App\Enums\WorkType::tryFrom($work['value'])?->colourButtonsClass() }} text-white">
                                                <span class="text-[10px] font-black leading-none">{{ $work['value'] === 'A' ? $work['agency_code'] ?? 'A' : strtoupper($work['value']) }}</span>
                                                @if($work['unassigned'] ?? false)
                                                    <span class="text-[6px] font-bold opacity-70 italic">DA: {{ $work['prev_license_number'] }}</span>
                                                @endif

                                                @if ($work['excluded'] ?? false)
                                                    <x-badge name="excluded" title="Escluso dalla ripartizione" />
                                                @endif
                                                @if ($work['shared_from_first'] ?? false)
                                                    <x-badge name="shared_ff" title="Condiviso dal primo" />
                                                @endif
                                            </div>
                                        @else
                                            <div class="h-12 w-full flex items-center justify-center group/cell cursor-pointer">
                                                <span class="text-slate-200 group-hover/cell:text-indigo-400 transition-colors text-lg font-light">+</span>
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <footer class="bg-slate-50 p-4 border-t border-slate-200">
                <div class="flex flex-wrap gap-6 justify-center">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-emerald-500 shadow-sm"></span><span class="text-[9px] font-black text-slate-500 uppercase">Contanti</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-indigo-500 shadow-sm"></span><span class="text-[9px] font-black text-slate-500 uppercase">Agenzie</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-amber-500 shadow-sm"></span><span class="text-[9px] font-black text-slate-500 uppercase">Nolo</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-rose-500 shadow-sm"></span><span class="text-[9px] font-black text-slate-500 uppercase">Perdi Volta</span></div>
                    <div class="h-4 w-[1px] bg-slate-200 mx-2"></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-600 border border-white"></span><span class="text-[9px] font-black text-slate-500 uppercase">Fisso</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-400 border border-white"></span><span class="text-[9px] font-black text-slate-500 uppercase">{{ config('app_settings.labels.shared_from_first') }}</span></div>
                </div>
            </footer>
        </div>
    </main>

    <livewire:ui.license-receipt-modal />
</div>
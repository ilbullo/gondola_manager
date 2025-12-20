<div class="h-full flex flex-col overflow-hidden">
    <livewire:layout.sidebar /> 
    <main class="flex-1 overflow-auto bg-slate-200">
        <div class="inline-block min-w-full">
            <div class="flex items-center bg-slate-50 border-b border-slate-300 sticky top-0 z-40 h-10 uppercase text-[9px] font-black text-slate-400">
                <div class="sticky-column bg-slate-50 px-4">Licenza / Socio</div>
                <div class="service-grid px-4">
                    @for($s=1; $s<=25; $s++)
                        <div class="w-12 text-center">S.{{ $s }}</div>
                    @endfor
                </div>
            </div>

            <div class="bg-white divide-y divide-slate-100">
                @foreach($licenses as $license)
                    <div class="flex items-center hover:bg-indigo-50/20">
                        <div class="sticky-column flex items-center justify-between px-3 py-2 border-r border-slate-200">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-slate-800 text-white rounded-lg font-black text-xs">
                                    {{ $license['user']['license_number'] }}
                                </span>
                                <p class="font-black text-slate-700 text-[10px] uppercase truncate">{{ $license['user']['name'] }}</p>
                            </div>
                        </div>
                        
                        <div class="service-grid p-2 px-4">
                            @for($s=1; $s<=25; $s++)
                                @php $work = $license['worksMap'][$s] ?? null; @endphp
                                <div class="w-13 h-13 flex justify-center items-center">
                                    @if($work)
                                        <div wire:click="openInfoBox({{ $work['id'] }})" 
                                             class="job-pill w-11 h-11 rounded-xl text-white shadow-md {{ \App\Enums\WorkType::tryFrom($work['value'])->colourButtonsClass() }}">
                                            <span class="text-[10px] font-black">{{ $work['agency']['code'] ?? $work['value'] }}</span>
                                            <span class="text-[7px] font-bold opacity-80 mt-1 uppercase">{{ Str::limit($work['voucher'], 8) }}</span>
                                        </div>
                                    @else
                                        <button wire:click="assignWork({{ $license['id'] }}, {{ $s }})" 
                                                class="w-11 h-11 border border-slate-200 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 text-xl font-light hover:border-indigo-400 hover:bg-indigo-50 transition-all">
                                            +
                                        </button>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>
</div>
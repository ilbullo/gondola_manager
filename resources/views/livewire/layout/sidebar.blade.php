{{-- resources/views/livewire/components/sidebar.blade.php --}}
<div
    id="sidebar"
    class="fixed inset-y-0 left-0 w-64 bg-white shadow-2xl border-r border-gray-200 transform -translate-x-full lg:translate-x-0 lg:static transition-transform duration-300 ease-in-out z-40 overflow-y-auto"
    x-data="{ actionsOpen: @entangle('showActions') }"
    aria-label="Barra laterale di configurazione"
>

    <div class="p-5 space-y-7">

        {{-- Messaggio flash --}}
        @if (session('success'))
            <div role="alert" aria-live="polite"
                 class="flex items-center gap-2 p-3 text-white bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl text-sm font-bold shadow-md">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Tipo Lavoro --}}
        <section aria-labelledby="work-type-title">
            <h2 id="work-type-title" class="text-base font-extrabold text-gray-900 mb-3">Tipo Lavoro</h2>

            <div class="grid gap-2.5">
                @foreach ($config['work_types'] as $btn)
                    <button
                        type="button"
                        wire:click="setWorkType('{{ $btn['value'] }}')"
                        aria-pressed="{{ $workType === $btn['value'] ? 'true' : 'false' }}"
                        class="h-11 px-4 text-sm font-bold rounded-xl shadow-sm focus:outline-none focus:ring-4 transition-all {{ $btn['classes'] }}
                               {{ $workType === $btn['value'] ? 'ring-4 scale-105 shadow-md ' . $btn['ring'] : 'hover:shadow-md' }}"
                    >
                        {{ $btn['label'] }}
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Riepilogo lavoro attivo (solo se selezionato) --}}
        @if($workType && $workType !== 'clear')
        {{-- Note / Voucher --}}
        @if ($config['sections']['notes']['enabled'] ?? true)
            <div class="mt-5 space-y-2">
                <label class="block text-sm font-extrabold text-gray-800 border-l-4 border-emerald-600 pl-3">
                    {{ $config['sections']['notes']['label'] }}
                </label>
                <input
                    type="text"
                    wire:model.live="voucher"
                    placeholder="{{ $config['sections']['notes']['placeholder'] }}"
                    class="w-full h-11 px-4 text-sm font-medium bg-white border-2 border-emerald-300 rounded-lg focus:border-emerald-600 focus:ring-4 focus:ring-emerald-300 focus:outline-none transition-all"
                />
            </div>
        @endif


            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 text-sm shadow-inner">
    <h3 class="font-extrabold text-gray-900 mb-3 text-base flex items-center justify-between">
        Lavoro Attivo

        {{-- Badge F o R – in alto a destra, piccolo ma evidente --}}
        @if($excluded ?? false)
            <span class="inline-block px-2 py-1 text-[10px] font-bold bg-red-100 text-red-700 rounded-full">
                F – Fisso alla licenza
            </span>
        @elseif($sharedFromFirst ?? false)
            <span class="inline-block px-2 py-1 text-[10px] font-bold bg-emerald-100 text-emerald-700 rounded-full">
                R – Ripartito dal primo
            </span>
        @endif
    </h3>

    <dl class="space-y-2">
        <div class="flex justify-between">
            <dt class="font-bold text-gray-700">Tipo</dt>
            <dd class="font-black text-blue-700">{{ $label }}</dd>
        </div>

        @if($workType === 'A' && $agencyName)
            <div class="flex justify-between">
                <dt class="font-bold text-gray-700">Agenzia</dt>
                <dd class="font-black text-indigo-700">{{ $agencyName }}</dd>
            </div>
        @endif

        @if(trim($voucher ?? ''))
            <div class="flex justify-between">
                <dt class="font-bold text-gray-700">Voucher</dt>
                <dd class="font-mono text-xs bg-emerald-100 text-emerald-700 px-2 py-1 rounded">
                    {{ Str::limit($voucher, 15) }}
                </dd>
            </div>
        @endif

        <div class="flex justify-between pt-2 border-t border-blue-200">
            <dt class="font-bold text-gray-700">Importo</dt>
            <dd class="text-base font-black @if($excluded) text-red-600 @elseif($sharedFromFirst) text-emerald-700 @else text-indigo-700 @endif">
                €{{ number_format($amount, 2) }}
            </dd>
        </div>

        <div class="flex justify-between">
            <dt class="font-bold text-gray-700">Posti</dt>
            <dd class="font-black">{{ $slotsOccupied }}</dd>
        </div>

        {{-- Info aggiuntiva chiara se escluso o ripartito --}}
        @if($excluded ?? false)
            <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800">
                Questo lavoro è <strong>fisso alla licenza</strong> e <strong>non conta</strong> nella ripartizione.
            </div>
        @elseif($sharedFromFirst ?? false)
            <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800">
                L'importo viene <strong>ripartito a partire dalla prima licenza</strong> del turno.
            </div>
        @endif
    </dl>
</div>
        @endif

        {{-- Configura Lavoro --}}
        @if($workType && $workType !== '')
            <button
                type="button"
                wire:click="openWorkDetailsModal"
                class="w-full h-12 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-xl shadow-lg focus:ring-4 focus:ring-blue-300 transition flex items-center justify-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configura Lavoro
            </button>
        @endif

        {{-- Azioni avanzate collassabili --}}
        <section aria-labelledby="actions-title">
            <button
                type="button"
                wire:click="toggleActions"
                id="actions-title"
                aria-controls="actions-panel"
                :aria-expanded="actionsOpen.toString()"
                class="w-full h-12 px-4 text-sm font-bold text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 rounded-xl shadow-lg focus:ring-4 focus:ring-purple-300 transition flex items-center justify-between"
            >
                <span class="flex items-center gap-2 uppercase">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Opzioni Tabella
                </span>
                <svg :class="actionsOpen ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div
                id="actions-panel"
                x-show="actionsOpen"
                x-transition
                class="mt-3 grid gap-2.5"
            >
                @foreach ($config['sections']['actions'] as $action)
                    @if (!($action['hidden'] ?? false))
                        <button
                            type="button"
                            wire:click="{{ $action['wire'] ?? '' }}()"
                            class="h-11 text-sm font-bold rounded-xl shadow-sm focus:outline-none focus:ring-4 transition {{ $action['classes'] }} {{ $action['ring'] }}"
                        >
                            {{ $action['label'] }}
                        </button>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- Riepilogo Lavori --}}
        <section aria-labelledby="summary-title" class="border-t border-gray-200 pt-6">
            <h3 id="summary-title" class="text-base font-extrabold text-gray-900 mb-3">Riepilogo</h3>
            <livewire:component.work-summary wire:key="sidebar-summary-{{ uniqid() }}" />
        </section>

    </div>
</div>
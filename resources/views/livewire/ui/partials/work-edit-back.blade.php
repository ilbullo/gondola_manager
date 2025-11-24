{{-- resources/views/livewire/ui/partials/work-edit-back.blade.php --}}
<div class="h-full flex flex-col bg-white">

    <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-5">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Modifica Lavoro</h2>
            <button @click="flipped = false"
                    class="p-2 rounded-full bg-white/20 hover:bg-white/30 text-white transition">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
        <!-- Messaggio di successo -->
    @if (session('message'))
        @include('components.sessionMessage',["message" => session('message')])
    @endif
        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-2">Tipo Lavoro</label>
                <select wire:model.live="value"
                        class="w-full px-4 py-3.5 text-base bg-gray-50 border border-gray-300 rounded-xl focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/30">
                    @foreach(\App\Enums\WorkType::cases() as $type)
                        @if(!in_array($type->value, [\App\Enums\WorkType::FIXED->value, \App\Enums\WorkType::EXCLUDED->value]))
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-base font-semibold text-gray-700 mb-2">Importo (€)</label>
                <div class="relative">
                    <input type="number" step="0.01" wire:model.live="amount" placeholder="0.00"
                           class="w-full pl-11 pr-4 py-3.5 text-base font-medium bg-gray-50 border border-gray-300 rounded-xl focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500/30">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-gray-600">€</span>
                </div>
            </div>
        </div>

        @if($value === 'A')
            <div>
                <label class="block text-base font-semibold text-gray-700 mb-2">Codice Agenzia</label>
                <select
                    wire:model.live="agency_code"
                    class="w-full px-4 py-3.5 text-base bg-gray-50 border border-gray-300 rounded-xl focus:border-emerald-600 focus:ring-2 focus:ring-emerald-500"
                >
                    <option value="">Modifica Agenzia</option>
                    {{-- PLACEHOLDER: Qui devi ciclare la proprietà $agencyCodes del tuo componente --}}
                    @foreach(\App\Models\Agency::select(['name','code'])->get() ?? [] as $key => $agency)

                        <option value="{{ $agency['code'] }}">{{ $agency['name'] }} ({{ $agency['code'] }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-base font-semibold text-gray-700 mb-2">Voucher / Nota</label>
            <input type="text" wire:model.live="voucher" placeholder="Facoltativo..."
                   class="w-full px-4 py-3.5 text-base bg-gray-50 border border-gray-300 rounded-xl">
        </div>

        <div class="space-y-4">
            <label class="flex items-center justify-between bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-4">
                    <input type="checkbox" x-model="excluded"
                           @change="if($event.target.checked) shared_from_first = false"
                           class="w-6 h-6 text-emerald-600 rounded focus:ring-emerald-500">
                    <span class="text-base font-semibold">Escluso da ripartizione</span>
                </div>
                <span class="text-xs bg-white px-3 py-1.5 rounded-full text-gray-600">Non conta</span>
            </label>

            <label class="flex items-center justify-between bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center gap-4">
                    <input type="checkbox" x-model="shared_from_first"
                           @change="if($event.target.checked) excluded = false"
                           class="w-6 h-6 text-emerald-600 rounded focus:ring-emerald-500">
                    <span class="text-base font-semibold">Ripartito dal primo</span>
                </div>
                <span class="text-xs bg-white px-3 py-1.5 rounded-full text-gray-600">Prima licenza</span>
            </label>
        </div>
    </div>

    <div class="px-6 py-5 bg-gray-50 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-4">
            <button @click="flipped = false"
                    class="py-4 text-lg font-semibold text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-xl transition">
                Annulla
            </button>
            <button wire:click="save"
                    class="py-4 text-lg font-bold text-white bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 rounded-xl shadow-lg transition">
                Salva Modifiche
            </button>
        </div>
    </div>
</div>

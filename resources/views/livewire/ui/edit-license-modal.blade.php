{{-- MODALE SEMPRE NEL DOM – FUNZIONA AL PRIMO CLICK --}}
<div 
    x-data 
    x-show="$wire.show"
    x-transition
    @keydown.escape.window="$wire.show = false"
    class="fixed inset-0 z-[9999] flex items-center justify-center"
    style="display: none;"
    role="dialog"
    aria-modal="true"
    aria-labelledby="modal-title"
>
    <div class="fixed inset-0 bg-black/50" @click="$wire.show = false"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <h2 id="modal-title" class="text-lg font-semibold mb-4">Modifica Licenza</h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Turno</label>
                <select wire:model="turn" class="w-full rounded-lg border border-gray-300 p-2">
                    @foreach(\App\Enums\DayType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Solo contanti</span>
                <button
                    type="button"
                    @click="$wire.onlyCashWorks = !$wire.onlyCashWorks"
                    :class="{ 'bg-blue-600': $wire.onlyCashWorks, 'bg-gray-200': !$wire.onlyCashWorks }"
                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                >
                    <span :class="{ 'translate-x-5': $wire.onlyCashWorks, 'translate-x-0.5': !$wire.onlyCashWorks }"
                          class="inline-block h-5 w-5 rounded-full bg-white shadow transform transition"></span>
                </button>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" @click="$wire.show = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
                    Annulla
                </button>
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-lg">
                    Salva
                </button>
            </div>
        </form>
    </div>
</div>
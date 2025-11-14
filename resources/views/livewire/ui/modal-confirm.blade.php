<div>
    @if ($show)
        <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl p-6 w-80">
                <p class="text-gray-900 text-lg mb-6">{{ $message }}</p>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancel"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                        Annulla
                    </button>

                    <button wire:click="confirm"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Conferma
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

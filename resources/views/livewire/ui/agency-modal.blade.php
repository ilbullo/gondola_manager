{{-- resources/views/livewire/modals/agency-modal.blade.php --}}
<div x-data="{ show: @entangle('show') }" x-show="show" x-cloak 
     class="fixed inset-0 z-[250] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4">
    
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden animate-in zoom-in">
        <div class="bg-indigo-600 p-6 text-center text-white">
            <h3 class="text-2xl font-black uppercase italic">Seleziona Agenzia</h3>
        </div>

        <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-3 max-h-[60vh] overflow-y-auto">
            @foreach ($agencies as $agency)
                <button wire:click="$dispatch('selectAgency', [{{ $agency['id'] }}])" 
                    class="h-24 bg-slate-50 border-2 border-slate-200 rounded-2xl flex flex-col items-center justify-center hover:border-indigo-500 hover:bg-indigo-50 transition-all group">
                    <span class="text-[8px] font-black text-slate-400 group-hover:text-indigo-500 uppercase">{{ $agency['code'] }}</span>
                    <span class="text-xs font-black text-slate-700 uppercase mt-1">{{ $agency['name'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="p-4 bg-slate-100 border-t border-slate-200">
            <button wire:click="close" class="w-full py-4 bg-slate-400 text-white rounded-2xl font-black uppercase">Annulla</button>
        </div>
    </div>
</div>
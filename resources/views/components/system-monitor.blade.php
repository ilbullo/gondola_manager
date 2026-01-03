{{-- resources/views/components/system-monitor.blade.php --}}
<div x-data="{ online: navigator.onLine }" 
     x-on:online.window="online = true" 
     x-on:offline.window="online = false"
     {{ $attributes->merge(['class' => 'flex items-center gap-3 px-2']) }}>
    
    {{-- Pallino di Stato --}}
    <div class="flex-shrink-0 w-2">
        <span class="relative flex h-2 w-2">
            {{-- Online e Sincronizzato --}}
            <span x-show="online" wire:loading.remove 
                  class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
            
            {{-- In fase di salvataggio/caricamento --}}
            <span wire:loading 
                  class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span wire:loading 
                  class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>

            {{-- Offline --}}
            <span x-show="!online" x-cloak
                  class="relative inline-flex rounded-full h-2 w-2 bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></span>
        </span>
    </div>

    {{-- Testo con larghezza fissa per evitare layout shift --}}
    <div class="flex flex-col w-24 overflow-hidden select-none">
        <template x-if="online">
            <div class="relative w-full">
                <span wire:loading.remove class="block text-[10px] font-black text-emerald-400 uppercase italic leading-none tracking-tight whitespace-nowrap">
                    Sistema Live
                </span>
                <span wire:loading class="block text-[10px] font-black text-amber-400 uppercase italic leading-none tracking-tight whitespace-nowrap">
                    Inviando...
                </span>
            </div>
        </template>
        <template x-if="!online">
            <span class="text-[10px] font-black text-rose-500 uppercase italic leading-none tracking-tight">
                Offline
            </span>
        </template>
    </div>
</div>
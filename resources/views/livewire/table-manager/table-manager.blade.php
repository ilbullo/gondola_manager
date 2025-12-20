{{-- resources/views/livewire/table-manager/table-manager.blade.php --}}

{{-- CAMBIO 1: h-screen e overflow-hidden per bloccare lo scroll "brutto" del browser --}}
<div class="w-full h-screen bg-slate-100 overflow-hidden flex flex-col">

    {{-- Header / Toolbar (se presente, altrimenti ignora) --}}
    
    {{-- CAMBIO 2: Il contenitore interno deve essere flex-1 (prende lo spazio rimanente) e overflow-hidden --}}
    <div class="flex-1 flex overflow-hidden relative">
        
        <main class="flex-1 w-full h-full relative flex flex-col">
            
            @if ($tableConfirmed)
                @if ($isRedistributed)
                    <livewire:table-manager.table-splitter />
                @else
                    <livewire:table-manager.work-assignment-table />
                @endif
            @else
                {{-- Qui c'è il tuo LicenseManager --}}
                <livewire:table-manager.license-manager />
            @endif
            
        </main>
    </div>
</div>
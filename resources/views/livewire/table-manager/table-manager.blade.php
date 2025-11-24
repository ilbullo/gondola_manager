{{-- resources/views/livewire/table-manager/table-manager.blade.php (Massimizzazione Spazio) --}}

<div class="max-w-full mx-auto min-h-screen bg-gray-100 p-0 sm:p-0">

    {{-- Il contenuto principale viene racchiuso in un div con lo stile delle altre sezioni, ma senza margine esterno fisso --}}
    <div class="bg-white rounded-none sm:rounded-2xl shadow-none sm:shadow-sm border-t sm:border border-gray-200">
        
        @if ($tableConfirmed)
            {{-- SE la tabella è confermata --}}

            @if ($isRedistributed)
                {{-- SE è stata richiesta la ripartizione, carica il TableSplitter --}}
                <livewire:table-manager.table-splitter />
            @else
                {{-- ALTRIMENTI (modalità assegnazione standard), carica WorkAssignmentTable --}}
                {{-- Nota: WorkAssignmentTable al suo interno ha già una sidebar e una tabella a tutta larghezza --}}
                <livewire:table-manager.work-assignment-table />
            @endif
        @else
            {{-- SE la tabella NON è confermata, mostra LicenseManager --}}
            {{-- Nota: LicenseManager ha un layout a colonne, manterrà lo stile interno unificato --}}
            <livewire:table-manager.license-manager />
        @endif
        
    </div>
</div>
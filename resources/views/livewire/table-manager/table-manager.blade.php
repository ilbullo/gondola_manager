<div>
    @if ($tableConfirmed)
        {{-- SE la tabella è confermata --}}

        @if ($isRedistributed)
            {{-- SE è stata richiesta la ripartizione, carica il TableSplitter --}}
            <livewire:table-manager.table-splitter />
        @else
            {{-- ALTRIMENTI (modalità assegnazione standard), carica WorkAssignmentTable --}}
            <livewire:table-manager.work-assignment-table />
        @endif
    @else
        {{-- SE la tabella NON è confermata, mostra LicenseManager --}}
        <livewire:table-manager.license-manager />
    @endif
</div>



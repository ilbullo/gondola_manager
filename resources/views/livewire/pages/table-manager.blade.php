<x-tm>
        @livewire('table-manager.table-manager')

        @push('modals')
            @livewire('ui.agency-modal')
            @livewire('ui.work-details-modal')
            @livewire('ui.work-live-info-modal')
            @livewire('ui.modal-confirm')

        @endpush

</x-tm>

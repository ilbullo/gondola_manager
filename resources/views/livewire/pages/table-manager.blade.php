<x-app-layout>
        @livewire('table-manager.table-manager')

        @push('modals')
            @livewire('ui.agency-modal')
            @livewire('ui.modal-confirm')
            @livewire('ui.work-details-modal')
            @livewire('ui.work-live-info-modal')
            @livewire('ui.work-edit-modal')
        @endpush

</x-app-layout>

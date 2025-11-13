<x-app-layout>
 <!-- CONTENUTO PRINCIPALE -->
    <div id="mainContent" class=" flex flex-col h-screen">
      <!-- HEADER -->
                <!-- LAYOUT PRINCIPALE -->
      <div class="flex flex-1 overflow-hidden">
        <!-- SIDEBAR -->
        @livewire('layout.sidebar')

        <!-- CONTENUTO PRINCIPALE -->
        <div class="flex-1 flex flex-col overflow-hidden">
          <!-- TABELLA -->
          <div class="flex-1 overflow-x-auto bg-white p-3">
          </div>
        </div>
      </div>
    </div>
</x-app-layout>

<button id="logoutButton" wire:click="logout"
    onclick="if(!confirm('Sei sicuro di voler uscire?')) { event.stopImmediatePropagation(); }"
    class="h-10 px-3 text-sm font-bold text-white bg-red-600 rounded-md shadow-sm shadow-red-500/40 hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200">
    ESCI
</button>

<div id="sidebar"
    class="fixed inset-y-0 left-0 w-56 sm:w-64 bg-white shadow-lg shadow-blue-500/20 p-3 transform -translate-x-full lg:static lg:translate-x-0 lg:w-64 transition-transform duration-300 lg:flex lg:flex-col overflow-y-auto z-30">
    <div id="successMessage"
        class="hidden flex items-center gap-2 p-3 bg-gradient-to-r from-emerald-500 to-lime-500 rounded-lg shadow-md shadow-emerald-500/50">
        <span class="text-base font-bold text-white">✓ Azione completata!</span>
    </div>
    <div class="space-y-3">
        <label class="block text-base sm:text-lg font-bold text-gray-800 border-l-4 border-blue-500 pl-3">
            TIPO LAVORO
        </label>
        <div class="grid gap-2">
            <button id="quickNoloButton" wire:click="$set('workType', 'N')"
                class="h-12 px-4 text-base font-bold text-gray-900 bg-yellow-400 rounded-lg shadow-md shadow-yellow-400/50 hover:bg-yellow-500 focus:ring-2 focus:ring-yellow-300 focus:outline-none transition-all duration-200">
                NOLO (N)
            </button>
            <button id="quickContantiButton"
                class="h-12 px-4 text-base font-bold text-white bg-emerald-600 rounded-lg shadow-md shadow-emerald-500/50 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-300 focus:outline-none transition-all duration-200">
                CONTANTI (X)
            </button>
            <button id="selectAgencyButton"
                class="h-12 px-4 text-base font-bold text-white bg-blue-600 rounded-lg shadow-md shadow-blue-500/50 hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-all duration-200">
                AGENZIA
            </button>
            <button id="quickCustomButton"
                class="h-12 px-4 text-base font-bold text-white bg-purple-600 rounded-lg shadow-md shadow-purple-500/50 hover:bg-purple-700 focus:ring-2 focus:ring-purple-300 focus:outline-none transition-all duration-200">
                CUSTOM
            </button>
            <button id="quickPerdiVoltaButton"
                class="h-12 px-4 text-base font-bold text-white bg-red-600 rounded-lg shadow-md shadow-red-500/50 hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200">
                PERDI VOLTA (P)
            </button>
            <button id="clearSelectionButton"
                class="h-12 px-4 text-base font-bold text-white bg-pink-600 rounded-lg shadow-md shadow-pink-500/50 hover:bg-pink-700 focus:ring-2 focus:ring-pink-300 focus:outline-none transition-all duration-200">
                ANNULLA
            </button>
        </div>
        <div id="currentSelection"
            class="hidden bg-gray-200 border-2 border-gray-500 rounded-lg p-2 text-sm font-extrabold text-center">
            Selezione: <span id="selectionText"></span>
        </div>
    </div>
    <div id="customInputContainer" class="hidden mt-3 space-y-2">
        <label class="block text-base sm:text-lg font-bold text-gray-800 border-l-4 border-purple-500 pl-3">
            NOME AGENZIA PERSONALIZZATA
        </label>
        <input id="customInput" type="text" placeholder="Esempio: Agenzia TEST"
            class="w-full h-12 px-3 text-base font-medium text-gray-900 bg-white border-2 border-purple-300 rounded-lg placeholder:text-gray-500 placeholder:font-medium focus:border-purple-600 focus:ring-2 focus:ring-purple-300 focus:outline-none transition-all duration-200" />
    </div>
    <div class="mt-3 space-y-2">
        <label class="block text-base sm:text-lg font-bold text-gray-800 border-l-4 border-emerald-500 pl-3">
            NOTE O VOUCHER
        </label>
        <input id="agencyNotes" type="text" placeholder="Esempio: Voucher 1234" wire:model.live="voucher"
            class="w-full h-12 px-3 text-base font-medium text-gray-900 bg-white border-2 border-emerald-300 rounded-lg placeholder:text-gray-500 placeholder:font-medium focus:border-emerald-600 focus:ring-2 focus:ring-emerald-300 focus:outline-none transition-all duration-200" />
    </div>
    <div class="mt-3 flex items-center gap-2">
        <input id="excludeSummary" type="checkbox"
            class="h-5 w-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-300 focus:outline-none" />
        <label class="text-base font-bold text-gray-800">
            ESCLUDI DAL RIEPILOGO
        </label>
    </div>
    <div class="mt-3 grid gap-2">
        <button id="redistributeButton"
            class="h-12 px-4 text-base font-bold text-white bg-emerald-600 rounded-lg shadow-md shadow-emerald-500/50 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-300 focus:outline-none transition-all duration-200">
            RIPARTISCI
        </button>
        <button id="undoButton"
            class="hidden h-12 px-4 text-base font-bold text-white bg-orange-500 rounded-lg shadow-md shadow-orange-500/50 hover:bg-orange-600 focus:ring-2 focus:ring-orange-300 focus:outline-none transition-all duration-200">
            ANNULLA RIPARTIZIONE
        </button>
        <button id="resetButton"
            class="h-12 px-4 text-base font-bold text-white bg-red-600 rounded-lg shadow-md shadow-red-500/50 hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200">
            RESET TABELLA
        </button>
    </div>
    <div class="mt-3 bg-gray-800 text-white p-3 rounded-lg shadow-md shadow-gray-500/50 space-y-2">
        <div class="flex justify-between text-xs sm:text-sm font-bold">
            <span>Contanti (X):</span>
            <span id="countX">0</span>
        </div>
        <div class="flex justify-between text-xs sm:text-sm font-bold">
            <span>Nolo (N):</span>
            <span id="countN">0</span>
        </div>
        <div class="flex justify-between text-xs sm:text-sm font-bold">
            <span>Perdi Volta (P):</span>
            <span id="countP">0</span>
        </div>
        <div class="flex justify-between text-xs sm:text-sm font-bold">
            <span>Agenzie:</span>
            <span id="countAgency">0</span>
        </div>
        <div class="flex justify-between text-xs sm:text-sm font-bold">
            <span>Ufficio:</span>
            <span id="countOffice">0</span>
        </div>
        <div id="grandTotal" class="flex justify-between text-xs sm:text-sm font-bold hidden">
            <span>Totale:</span>
            <span id="grandTotalValue">0 EUR</span>
        </div>
    </div>
    <div class="mt-3 grid gap-2">
        <button id="logoutButton" wire:click="logout"
            class="h-12 px-4 text-base font-bold text-white bg-red-600 rounded-lg shadow-md shadow-red-500/50 hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200">
            ESCI
        </button>
    </div>
</div>

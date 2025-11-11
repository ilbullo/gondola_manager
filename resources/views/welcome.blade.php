<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestione Ripartizione Lavori</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
            <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen font-sans text-gray-900 flex flex-col">
   @if (Route::has('login'))
        <livewire:welcome.navigation />
    @endif


    <!-- CONTENUTO PRINCIPALE -->
    <div id="mainContent" class=" flex flex-col h-screen">
      <!-- HEADER -->
      @livewire('layout.header')
                <!-- LAYOUT PRINCIPALE -->
      <div class="flex flex-1 overflow-hidden">
        <!-- SIDEBAR -->
        @livewire('layout.sidebar')

        <!-- CONTENUTO PRINCIPALE -->
        <div class="flex-1 flex flex-col overflow-hidden">
          <!-- BANCALE DISPLAY -->
          <div class="p-3 bg-white">
            <div
              id="bancaleDisplay"
              class="flex items-center gap-2 text-xs sm:text-sm font-bold text-gray-800"
            >
              BANCALE:
              <span id="bancaleValue">0</span> EUR
              <button
                id="editBancaleButton"
                class="h-10 px-3 text-xs sm:text-sm font-bold text-white bg-blue-600 rounded-lg shadow-md shadow-blue-500/50 hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-all duration-200"
              >
                MODIFICA
              </button>
            </div>
          </div>
          <!-- TABELLA -->
          <div class="flex-1 overflow-x-auto bg-white p-3">
            <table id="licenseTable" class="min-w-full text-sm">
              <thead>
                <tr
                  class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white"
                >
                  <th
                    class="py-2 px-2 text-left text-sm font-extrabold sticky left-0 bg-gradient-to-r from-blue-600 to-indigo-600 z-10"
                  >
                    LICENZA
                  </th>
                </tr>
              </thead>
              <tbody class="text-center"></tbody>
            </table>
          </div>
          <!-- GESTIONE RIGHE -->
          <div class="p-3 bg-gray-50">
            <div id="rowManagement" class="text-center space-y-2">
              <button
                id="addRowButton"
                class="h-12 px-4 text-base font-bold text-white bg-emerald-600 rounded-lg shadow-md shadow-emerald-500/50 hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-300 focus:outline-none transition-all duration-200"
              >
                + AGGIUNGI LICENZA
              </button>
              <p
                id="rowMessage"
                class="text-base font-bold text-red-600 hidden"
              >
                Le righe si modificano solo se la tabella è vuota
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- TOOLTIP -->
    <div
      id="cellTooltip"
      class="fixed bg-gray-900 text-white p-2 rounded-lg text-xs font-bold z-50 pointer-events-none opacity-0 transition-opacity max-w-xs shadow-md shadow-gray-500/50"
    ></div>

    <!-- MODALE BANCALE -->
    <div
      id="modal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50"
    >
      <div
        class="bg-white rounded-xl shadow-lg shadow-blue-500/20 border-t-4 border-pink-500 p-4 sm:p-6 w-full max-w-sm sm:max-w-md"
      >
        <h2
          class="text-xl sm:text-2xl font-extrabold text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4"
        >
          INSERISCI COSTO BANCALE
        </h2>
        <input
          id="modalBancaleAmount"
          type="number"
          min="0"
          step="1"
          placeholder="Esempio: 100"
          class="w-full h-12 px-3 text-base font-medium text-gray-900 bg-white border-2 border-blue-300 rounded-lg placeholder:text-gray-500 placeholder:font-medium focus:border-purple-600 focus:ring-2 focus:ring-purple-300 focus:outline-none transition-all duration-200"
        />
        <div class="flex justify-center gap-3 mt-4">
          <button
            id="modalCancelButton"
            class="h-12 px-4 text-base font-bold text-white bg-red-600 rounded-lg shadow-md shadow-red-500/50 hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200"
          >
            ANNULLA
          </button>
          <button
            id="modalConfirmButton"
            class="h-12 px-4 text-base font-bold text-white bg-emerald-600 rounded-lg shadow-md shadow-emerald-500/50 hover:bg-emerald-700 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-all duration-200"
          >
            CONFERMA
          </button>
        </div>
      </div>
    </div>

    <!-- MODALE AGENZIA -->
    <div
      id="agencyModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50"
    >
      <div
        class="bg-white rounded-xl shadow-lg shadow-blue-500/20 border-t-4 border-pink-500 p-4 sm:p-6 w-full max-w-2xl sm:max-w-3xl max-h-[80vh] overflow-y-auto"
      >
        <h2
          class="text-xl sm:text-2xl font-extrabold text-center text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4"
        >
          SELEZIONA AGENZIA
        </h2>
        <div
          id="agencyButtonsContainer"
          class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2"
        ></div>
        <div class="text-center mt-4">
          <button
            id="agencyModalCancelButton"
            class="h-12 px-4 text-base font-bold text-white bg-red-600 rounded-lg shadow-md shadow-red-500/50 hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-all duration-200"
          >
            ANNULLA
          </button>
        </div>
      </div>
    </div>
  </body>
</html>

<header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-1.5 px-3 sm:px-4">
    <div class="flex items-center justify-between">
        <!-- Toggle Sidebar (visibile su tablet/mobile) -->
        <button id="sidebarToggle" class="lg:hidden text-white focus:outline-none h-8 w-8 flex items-center justify-center rounded-md hover:bg-blue-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        <!-- Titolo e Data -->
        <div class="flex items-center flex-1 gap-2 justify-center">
            <h1 class="text-lg sm:text-xl font-bold text-yellow-400 truncate">
                {{ env('APP_NAME') }}
            </h1>
            <span class="text-sm sm:text-base font-medium text-white truncate">
                | {{ $info ?? __('date').': ' . $date }}
            </span>
        </div>

        <!-- Menu Azioni -->
        <div class="relative">
            <button id="menuToggle" class="h-8 w-8 flex items-center justify-center bg-yellow-400 text-white rounded-full hover:bg-yellow-500 focus:ring-1 focus:ring-yellow-300 focus:outline-none transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
            <div id="menuContent" class="hidden absolute top-10 right-0 bg-white rounded-md shadow-lg p-2 w-56 sm:w-64 z-50 flex flex-col gap-1">
                @foreach ($menuActions ?? [] as $action)
                    <button 
                        class="h-10 px-3 text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:ring-1 focus:ring-blue-300 focus:outline-none transition-colors"
                        id="{{ $action['id'] }}"
                    >
                        {{ $action['label'] }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</header>
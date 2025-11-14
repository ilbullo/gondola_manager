<div>
    <header class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2 px-3 sm:px-4">
        <div class="flex items-center justify-between">

            <!-- Titolo e Data -->
            <div class="flex items-center flex-1 gap-2 justify-center">
                <h1 class="text-lg sm:text-xl font-bold text-yellow-400 truncate">
                    {{ env('APP_NAME') }}
                </h1>
                <span class="text-sm sm:text-base font-medium text-white truncate">
                    | {{ $info ?? __('date').': ' . now()->format('d/m/Y') }}
                </span>
            </div>

            <!-- Menu Tablet/Desktop -->
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                @foreach ($menuItems as $item)
                    @if ($item['id'] == 'logout')
                    <button
                        wire:click="logout"
                        class="flex flex-col items-center justify-center h-12 w-16 lg:w-auto lg:h-10 lg:px-3 lg:flex-row lg:gap-1 text-xs lg:text-sm font-semibold text-white bg-red-600 rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-300 focus:outline-none transition-colors"
                        aria-label="{{ $item['label'] }}"
                    >
                        <svg class="w-5 h-5 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        <span class="lg:hidden">{{ $item['short_label'] }}</span>
                        <span class="hidden lg:inline">{{ $item['label'] }}</span>
                    </button>
                    @else
                    <a
                        href="{{ $item['route'] }}"
                        class="flex flex-col items-center justify-center h-12 w-16 lg:w-auto lg:h-10 lg:px-3 lg:flex-row lg:gap-1 text-xs lg:text-sm font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors"
                        aria-label="{{ $item['label'] }}"
                    >
                        <svg class="w-5 h-5 lg:w-4 lg:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        <span class="lg:hidden">{{ $item['short_label'] }}</span>
                        <span class="hidden lg:inline">{{ $item['label'] }}</span>
                    </a>
                    @endif
                @endforeach
            </nav>
        </div>

        <!-- Menu Mobile (aperto al click) -->
        <div
            class="{{ $isMenuOpen ? 'block' : 'hidden' }} md:hidden absolute top-14 left-0 right-0 bg-white shadow-lg z-50"
        >
            <nav class="flex flex-col p-4 gap-2">
                @foreach ($menuItems as $item)
                    <a
                        href="{{ $item['route'] }}"
                        class="h-12 px-4 flex items-center gap-2 text-base font-semibold text-blue-600 bg-gray-100 rounded-md hover:bg-gray-200 focus:ring-2 focus:ring-blue-300 focus:outline-none transition-colors"
                        aria-label="{{ $item['label'] }}"
                        wire:click="toggleMenu"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>
</div>

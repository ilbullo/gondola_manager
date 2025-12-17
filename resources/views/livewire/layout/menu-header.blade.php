<header class="bg-white shadow-lg border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14">
            <!-- Logo / Titolo -->
            <div class="flex items-center">
                <h1 class="text-2xl font-bold text-indigo-600 hidden lg:block">Gondola Manager</h1>
                <h1 class="text-2xl font-bold text-indigo-600 block lg:hidden">GM</h1>
            </div>

            <!-- Menu Desktop -->
            <nav class="hidden md:flex items-center space-x-1">
                @foreach ($menuItems as $item)
                    @if ($item['action'] ?? false)
                        <button wire:click="{{ $item['action'] }}"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" /></svg>
                            <span>{{ $item['label'] }}</span>
                        </button>
                    @else
                        <a href="{{ $item['route'] }}"
                           class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs($item['id'] . '*') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           @if(str_starts_with($item['route'], route('dashboard'))) wire:navigate @endif>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" /></svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <!-- Menu Mobile Toggle -->
            <button wire:click="toggleMenu"
                    class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                <svg x-show="!$wire.isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="$wire.isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Menu Mobile Dropdown -->
        <div x-data="{ open: @entangle('isMenuOpen') }"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-end="opacity-0"
             class="md:hidden border-t border-gray-200 bg-white"
             @click.outside="open = false">
            <nav class="px-2 pt-2 pb-4 space-y-1">
                @foreach ($menuItems as $item)
                    <div class="flex items-center">
                        @if ($item['action'] ?? false)
                            <button wire:click="{{ $item['action'] }}(); open = false"
                                class="w-full flex items-center gap-3 px-3 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" /></svg>
                                {{ $item['label'] }}
                            </button>
                        @else
                            <a href="{{ $item['route'] }}"
                               wire:navigate
                               @click="open = false"
                               class="w-full flex items-center gap-3 px-3 py-3 text-base font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition {{ request()->routeIs($item['id'] . '*') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" /></svg>
                                {{ $item['label'] }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </nav>
        </div>
    </div>
</header>
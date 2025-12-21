<header class="bg-white border-b-2 border-slate-100 sticky top-0 z-50">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-8">
        <div class="flex justify-between items-center h-20">
            
            <div class="flex items-center group cursor-pointer shrink-0">
                <div class="flex flex-col">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 uppercase italic tracking-tighter leading-none group-hover:text-indigo-600 transition-colors">
                        G<span class="hidden xs:inline">ondola</span><span class="text-indigo-600">M<span class="hidden xs:inline">anager</span></span>
                    </h1>
                    <span class="text-[7px] sm:text-[8px] font-black text-slate-400 uppercase tracking-[0.3em] sm:tracking-[0.4em] leading-none mt-1">Professional Suite</span>
                </div>
            </div>

            <nav class="hidden xl:flex items-center gap-1 ml-4">
                @foreach ($menuItems as $item)
                    @php $isActive = request()->routeIs($item['id'] . '*'); @endphp
                    
                    @if ($item['action'] ?? false)
                        <button wire:click="{{ $item['action'] }}"
                            class="flex items-center gap-2 px-3 2xl:px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-indigo-600 hover:bg-slate-50 rounded-xl transition-all shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $item['icon'] }}" /></svg>
                            <span>{{ $item['label'] }}</span>
                        </button>
                    @else
                        <a href="{{ $item['route'] }}"
                           class="flex items-center gap-2 px-3 2xl:px-5 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all rounded-xl shrink-0 {{ $isActive ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50' }}"
                           @if(str_starts_with($item['route'], route('dashboard'))) wire:navigate @endif>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $item['icon'] }}" /></svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="xl:hidden flex items-center">
                <button wire:click="toggleMenu"
                        class="p-3 rounded-2xl bg-slate-50 text-slate-900 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                    <svg x-show="!$wire.isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="$wire.isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-data="{ open: @entangle('isMenuOpen') }"
             x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="xl:hidden absolute left-0 w-full border-t border-slate-100 bg-white shadow-2xl z-[60]"
             @click.outside="open = false">
            <nav class="p-4 space-y-1">
                @foreach ($menuItems as $item)
                    @php $isActive = request()->routeIs($item['id'] . '*'); @endphp
                    @if ($item['action'] ?? false)
                        <button wire:click="{{ $item['action'] }}(); open = false"
                            class="w-full flex items-center gap-4 px-6 py-4 text-xs font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 rounded-2xl transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $item['icon'] }}" /></svg>
                            {{ $item['label'] }}
                        </button>
                    @else
                        <a href="{{ $item['route'] }}" wire:navigate @click="open = false"
                           class="w-full flex items-center gap-4 px-6 py-4 text-xs font-black uppercase tracking-widest rounded-2xl transition-all {{ $isActive ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $item['icon'] }}" /></svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>
</header>
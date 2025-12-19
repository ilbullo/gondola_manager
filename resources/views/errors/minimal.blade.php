<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>

<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen font-sans text-gray-900 antialiased flex flex-col justify-center items-center p-6">

    <div class="max-w-2xl w-full bg-white shadow-2xl border border-gray-100 rounded-[2rem] overflow-hidden">
        <div class="p-8 md:p-12 text-center">
            
            {{-- Badge Codice Errore --}}
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-3xl shadow-xl shadow-indigo-200 mb-8 transform -rotate-6">
                <span class="text-4xl font-black text-white">
                    @yield('code')
                </span>
            </div>

            {{-- Titolo e Messaggio --}}
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight mb-4">
                @yield('title')
            </h1>
            
            <p class="text-lg text-gray-500 mb-10 leading-relaxed max-w-md mx-auto">
                @yield('message')
            </p>

            {{-- Azioni (Stesso stile dei bottoni nella tua Sidebar) --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ url('/') }}" 
                   class="w-full sm:w-auto h-12 px-8 flex items-center justify-center text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-xl shadow-lg focus:ring-4 focus:ring-blue-300 transition-all active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Torna alla Dashboard
                </a>
                
                <button onclick="window.history.back()" 
                        class="w-full sm:w-auto h-12 px-8 flex items-center justify-center text-sm font-bold text-gray-700 bg-white border-2 border-gray-100 hover:bg-gray-50 rounded-xl shadow-sm transition-all active:scale-95">
                    Pagina precedente
                </button>
            </div>
        </div>

        {{-- Footer coordinato --}}
        <div class="bg-gray-50/50 border-t border-gray-100 py-6 px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <img src="/img/favicon/favicon.svg" class="w-5 h-5 opacity-50" alt="Logo">
                <span class="text-xs font-bold uppercase text-gray-400 tracking-widest">
                    {{ config('app.name') }}
                </span>
            </div>
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} — Sistema di Ripartizione
            </p>
        </div>
    </div>

    {{-- Decorazione di sfondo coordinata al layout menu-header --}}
    <div class="fixed top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>

</body>
</html>
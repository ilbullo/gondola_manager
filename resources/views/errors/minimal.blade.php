<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>

<body class="bg-slate-950 min-h-screen font-sans antialiased flex flex-col justify-center items-center p-6 relative overflow-hidden">

    {{-- Decorazione di sfondo (Radial Glow sottile) --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-xl w-full bg-white rounded-[3rem] shadow-[0_0_50px_rgba(0,0,0,0.3)] border border-white/10 overflow-hidden relative z-10">
        
        {{-- Header Scuro Tecnico --}}
        <div class="bg-slate-900 p-12 text-center relative overflow-hidden">
            {{-- Pattern di sfondo (opzionale) --}}
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
            
            {{-- Badge Codice Errore "Pro" --}}
            <div class="inline-flex items-center justify-center w-28 h-28 bg-white rounded-[2.5rem] shadow-2xl mb-6 transform rotate-3 border-4 border-slate-800">
                <span class="text-5xl font-black text-slate-900 italic tracking-tighter">
                    @yield('code')
                </span>
            </div>

            <h1 class="text-2xl font-black text-white uppercase italic tracking-[0.2em] leading-none">
                @yield('title')
            </h1>
        </div>

        {{-- Corpo Messaggio --}}
        <div class="p-10 text-center bg-white">
            <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-10 leading-relaxed max-w-xs mx-auto">
                @yield('message')
            </p>

            {{-- Azioni (Stile bottoni Sidebar) --}}
            <div class="flex flex-col gap-3 max-w-xs mx-auto">
                <a href="{{ url('/') }}" 
                   class="h-14 flex items-center justify-center text-[10px] font-black uppercase tracking-[0.2em] text-white bg-indigo-600 hover:bg-indigo-700 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                
                <button onclick="window.history.back()" 
                        class="h-14 flex items-center justify-center text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 bg-slate-50 border border-slate-200 hover:bg-slate-100 rounded-2xl transition-all">
                    Indietro
                </button>
            </div>
        </div>

        {{-- Footer coordinato --}}
        <div class="bg-slate-50 py-6 px-10 flex flex-col items-center border-t border-slate-100">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-pulse"></div>
                <span class="text-[9px] font-black uppercase text-slate-400 tracking-[0.3em]">
                    {{ config('app.name') }} — System Status
                </span>
            </div>
            <p class="text-[8px] font-bold text-slate-300 uppercase tracking-tighter">
                &copy; {{ date('Y') }} — Tutti i diritti riservati
            </p>
        </div>
    </div>

    {{-- Linea di accento superiore --}}
    <div class="fixed top-0 left-0 w-full h-1.5 bg-indigo-600 shadow-[0_0_15px_rgba(79,70,229,0.5)]"></div>

</body>
</html>
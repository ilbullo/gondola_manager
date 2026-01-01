<!DOCTYPE html>
<html lang="it" class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        /* Scrollbar sottile per il modale */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>

<body class="bg-white text-slate-900 h-full overflow-x-hidden">

    <div class="flex flex-col h-full">
        <div class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-100 px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-rose-50 p-1.5 rounded-md text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">
                    @yield('title', 'Dettaglio Errore')
                </h1>
            </div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2 py-1 bg-slate-50 rounded">System Alert</span>
        </div>

        <div class="flex-grow p-5 space-y-5">
            @yield('content')
        </div>

        <div class="sticky bottom-0 bg-slate-50 border-t border-slate-100 px-5 py-3 flex justify-end">
            <a href="{{ url()->previous() ?? '/' }}"
                class="text-xs font-bold text-slate-600 hover:text-slate-900 px-4 py-2 rounded-md transition duration-200">
                Indietro
            </a>
        </div>
    </div>

</body>
</html>
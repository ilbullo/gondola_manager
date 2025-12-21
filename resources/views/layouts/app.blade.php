<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GondolaManager | Professional Suite</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,700,900&display=swap" rel="stylesheet" />

    <link rel="icon" type="image/png" href="/img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/img/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/img/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Dogana" />
    <link rel="manifest" href="/img/favicon/site.webmanifest" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Uniformiamo il font globale per supportare il font-black */
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Rende le scrollbar della pagina più eleganti e sottili */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    @stack('custom_css')
    @livewireStyles
</head>

{{-- 
    Cambiato bg-gradient (troppo giocoso) con bg-slate-50 (professionale).
    Aggiunta min-h-screen per coprire sempre l'intera altezza.
--}}
<body class="bg-slate-50 min-h-screen text-slate-900 flex flex-col antialiased">
    
    {{-- Header con lo stile aggiornato --}}
    @livewire('layout.menu-header')

    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- Stack per i modali (es. quello di conferma eliminazione) --}}
    <div id="modal-container">
        @stack('modals')
    </div>

    @livewireScripts
    <script src="https://unpkg.com/@wotz/livewire-sortablejs@1.0.0/dist/livewire-sortable.js"></script>

    {{-- Script per gestire la chiusura automatica dei messaggi flash --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            // Logica extra al cambio pagina se necessaria
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>{{ env('APP_NAME',config('app_settings.system_title')) }}</title>

    {{-- Script e Font (Invariati) --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,700,900&display=swap" rel="stylesheet" />

    {{-- Favicon (Invariate) --}}
    <link rel="icon" type="image/png" href="/img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/img/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/img/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Dogana" />
    <link rel="manifest" href="/img/favicon/site.webmanifest" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            /* Forza l'altezza e impedisce lo scroll del body */
            height: 100%;
            overflow: hidden;
            overscroll-behavior-y: contain;
        }

        /* Scrollbar sottili per i contenitori interni */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Evita il "rimbalzo" su iOS */
        html {
            position: fixed;
            width: 100%;
        }

        [x-cloak] { display: none !important; }
    </style>

    @stack('custom_css')
    @livewireStyles
</head>

<body class="bg-slate-50 text-slate-900 flex flex-col antialiased h-full">
    
    {{-- Header fisso in alto --}}
    <header class="shrink-0 z-[60]">
        @livewire('layout.menu-header')
    </header>

    {{-- Main occupa tutto lo spazio rimanente --}}
    <main class="flex-1 min-h-0 relative">
        {{-- 
           IMPORTANTE: Il componente Livewire che carichi qui 
           dovrebbe avere classe "h-full" e gestire il proprio overflow interno 
        --}}
        {{ $slot }}
    </main>

    {{-- Container Modali --}}
    <div id="modal-container">
        @stack('modals')
    </div>

    <x-toast />
    @livewireScripts
    <script src="https://unpkg.com/@wotz/livewire-sortablejs@1.0.0/dist/livewire-sortable.js"></script>
</body>
</html>
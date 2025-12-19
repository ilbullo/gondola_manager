<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestione Ripartizione Servizi</title>
    <!--<script src="https://cdn.tailwindcss.com"></script>-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <!--favicons -->
    <link rel="icon" type="image/png" href="/img/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/img/favicon/favicon.svg" />
    <link rel="shortcut icon" href="/img/favicon/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Dogana" />
    <link rel="manifest" href="/img/favicon/site.webmanifest" />
    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('custom_css')
</head>

<body class="bg-gradient-to-br from-blue-50 to-purple-50 min-h-screen font-sans text-gray-900 flex flex-col antialiased">
    @livewire('layout.menu-header')

    <!-- Page Content -->
    <main>
        {{ $slot }}
    </main>
    @stack('modals')
    @livewireScripts
    <!-- Optional: Remove if sortable functionality is not needed -->
    <script src="https://unpkg.com/@wotz/livewire-sortablejs@1.0.0/dist/livewire-sortable.js"></script>
    <!--<script src="https://cdn.jsdelivr.net/gh/livewire/sortable@v1.x.x/dist/livewire-sortable.js"></script>-->
</body>

</html>

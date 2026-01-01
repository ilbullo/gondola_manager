<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('legal.software_name') }} - Note Legali</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    /* Forza lo scroll su tutti i contenitori radice */
    html, body {
        overflow-y: auto !important;
        height: auto !important;
        min-height: 100% !important;
        position: relative !important;
    }
</style>
</head>
{{-- overflow-y-auto qui assicura che il body sia sempre scorrevole --}}
<body class="bg-slate-100 font-sans antialiased overflow-y-auto min-h-screen">
    
    {{-- Contenitore semplice senza flex-center --}}
    <main class="w-full">
        {{ $slot }}
    </main>

</body>
</html>
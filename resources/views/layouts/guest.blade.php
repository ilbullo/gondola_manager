<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME',config('app_settings.system_title')) }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,700,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            height: 100%;
            overflow: hidden; /* Blocca lo scroll su body */
            position: fixed; /* Evita il rimbalzo su iOS */
            width: 100%;
        }
    </style>
</head>

<body class="antialiased bg-slate-50 text-slate-900">
    {{-- Contenitore principale bloccato all'altezza della finestra --}}
    <div class="h-screen w-full flex flex-col lg:flex-row overflow-hidden border-none">
        
<div class="hidden lg:flex flex-1 bg-slate-900 items-center justify-center p-12 relative overflow-hidden">
    {{-- Effetto Glow per dare profondità al logo --}}
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-indigo-600 rounded-full blur-[140px]"></div>
    </div>

    <x-application-logo />

    {{-- Overlay decorativo tecnico --}}
    <div class="absolute bottom-10 left-10 opacity-20">
        <p class="text-[8px] font-mono text-slate-500 tracking-[0.3em] uppercase rotate-90 origin-left">
            System Status: Operational
        </p>
    </div>
</div>

        <div class="flex-1 flex items-center justify-center p-6 bg-slate-50 h-full overflow-hidden">
            <div class="w-full max-w-md">
                {{-- Logo Mobile --}}
                <div class="lg:hidden text-center mb-8">
                    <h1 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter leading-none">
                        G<span class="text-indigo-600">M</span>
                    </h1>
                </div>

                {{-- Il form di login --}}
                <div class="relative">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Errore - {{ $title ?? 'Problema' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: system-ui, -apple-system, sans-serif; }
    </style>

    @yield('head')  <!-- per meta, title override, stili extra -->
</head>

<body class="h-full bg-gray-50 flex items-center justify-center p-4 sm:p-6">

    <div class="w-full max-w-lg">

        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">

            <!-- Header -->
            <div class="bg-gray-100 px-6 py-5 border-b border-gray-200 flex items-center gap-4">
                @yield('icon')  <!-- icona specifica per tipo di errore -->
                <h1 class="text-xl font-semibold text-gray-800">
                    {{ $title ?? 'Si è verificato un errore' }}
                </h1>
            </div>

            <!-- Contenuto specifico dell'errore -->
            <div class="p-6 space-y-5 text-gray-700">
                @yield('content')
            </div>

            <!-- Pulsanti sempre uguali -->
            <div class="px-6 py-5 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <a href="{{ url()->previous() ?? '/' }}"
                   class="px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition">
                    Indietro
                </a>
                <a href="{{ route('home') ?? '/' }}"
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                    Home
                </a>
            </div>
        </div>

    </div>

    @yield('scripts')  <!-- se un errore vuole JS extra -->

</body>
</html>
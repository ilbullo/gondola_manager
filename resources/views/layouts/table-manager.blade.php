<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ env('APP_NAME',config('app_settings.system_title')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/print-js/1.6.0/print.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/print-js/1.6.0/print.min.css">
 <!--   <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>-->
    <style>
        [x-cloak] { display: none !important; }
        .service-grid { display: grid; grid-template-columns: repeat(25, 52px); gap: 4px; width: max-content; }
        .sticky-column { position: sticky; left: 0; z-index: 20; background: white; box-shadow: 4px 0 10px -2px rgba(0,0,0,0.1); width: 190px; flex-shrink: 0; }
        .job-pill { cursor: pointer; transition: transform 0.1s; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; }
        .config-item { height: 50px; display: flex; align-items: center; }
    </style>
    @stack('custom_css')

    @livewireStyles
</head>
<body class="h-full bg-slate-200 font-sans antialiased text-gray-900 overflow-hidden">
    {{ $slot }}
    @stack('modals')
    @livewireScripts
    @stack('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('do-print-pdf', async (event) => {
            // Aggiungiamo un timestamp per evitare cache
            const url = (event.url || event[0].url) + '?t=' + new Date().getTime();
            
            try {
                // 1. Scarichiamo il PDF come Blob
                const response = await fetch(url);
                const blob = await response.blob();
                
                // 2. Creiamo un URL per il Blob
                const blobUrl = URL.createObjectURL(blob);

                // 3. CREIAMO UN LINK INVISIBILE (Non un iframe!)
                const link = document.createElement('a');
                link.href = blobUrl;
                link.target = '_blank'; // Fondamentale per iPad PWA
                
                // 4. Simuliamo il clic
                document.body.appendChild(link);
                link.click();
                
                // 5. Pulizia
                setTimeout(() => {
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(blobUrl);
                }, 500);

            } catch (error) {
                console.error("Errore:", error);
                // Fallback estremo
                window.location.href = url;
            }
        });
    });
</script>
</body>
</html>

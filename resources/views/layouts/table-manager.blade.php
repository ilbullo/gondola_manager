<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ env('APP_NAME',config('app_settings.system_title')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        window.addEventListener('trigger-print-html', event => {
        console.log('Evento di stampa ricevuto:', event.detail.url);
        const url = event.detail.url + '?t=' + new Date().getTime(); // Cache busting

        const oldFrame = document.getElementById('print-iframe');
        if (oldFrame) oldFrame.remove();

        const iframe = document.createElement('iframe');
        iframe.id = 'print-iframe';

        // Per iPad: l'iframe deve essere "tecnicamente" visibile ma nascosto all'utente
        iframe.style.position = 'fixed';
        iframe.style.left = '-9999px';
        iframe.style.top = '0';
        iframe.style.opacity = '0.01';
        iframe.style.width = '1px';
        iframe.style.height = '1px';
        iframe.src = url;

        document.body.appendChild(iframe);

        iframe.onload = function() {
            console.log('Iframe caricato, lancio stampa...');
            setTimeout(() => {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (e) {
                    console.error("Errore durante l'esecuzione di print():", e);
                }

                // Pulizia dopo 10 secondi per permettere ad AirPrint di completare
                setTimeout(() => {
                    if(document.body.contains(iframe)) document.body.removeChild(iframe);
                }, 10000);
            }, 800);
        };
    });
    </script>
</body>
</html>

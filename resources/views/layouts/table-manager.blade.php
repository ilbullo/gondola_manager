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
        .service-grid { display: grid; grid-template-columns: repeat({{ config('app_settings.matrix.total_slots') ?? 25 }}, 52px); gap: 4px; width: max-content; }
        .sticky-column { position: sticky; left: 0; z-index: 20; background: white; box-shadow: 4px 0 10px -2px rgba(0,0,0,0.1); width: 190px; flex-shrink: 0; }
        .job-pill { cursor: pointer; transition: transform 0.1s; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; }
        .config-item { height: 50px; display: flex; align-items: center; }
    /* 1. NESSUNA regola fuori da @media print deve toccare elementi esistenti */
    #print-container { 
        display: none; 
    }

    @media print {
        /* 2. Nascondi l'interfaccia interattiva solo in fase di stampa */
        body > *:not(#print-container) { 
            display: none !important; 
        }

        /* 3. Mostra il report */
        #print-container { 
            display: block !important; 
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: auto;
        }

        /* 4. Forza il layout orizzontale della tabella nel report */
        #print-container table { display: table !important; width: 100% !important; }
        #print-container tr { display: table-row !important; }
        #print-container td, #print-container th { display: table-cell !important; }
        
        /* 5. Reset strutturale per la stampante */
        html, body { 
            overflow: visible !important; 
            height: auto !important; 
        }
    }
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
       window.addEventListener('print-html', event => {
            let printContainer = document.getElementById('print-container');
            if (!printContainer) {
                printContainer = document.createElement('div');
                printContainer.id = 'print-container';
                document.body.appendChild(printContainer);
            }

            printContainer.innerHTML = event.detail.html;

            // Lanciamo la stampa
            window.print();

            // Pulizia immediata: non appena la finestra di stampa si chiude, 
            // svuotiamo il contenitore. 50ms sono sufficienti per evitare glitch.
            setTimeout(() => {
                printContainer.innerHTML = '';
            }, 50); 
        });
    </script>
</body>
</html>

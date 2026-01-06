<div x-data="{ show: @entangle('isOpen') }" 
     x-show="show" 
     x-cloak 
     class="fixed inset-0 z-[999] bg-slate-900/95 overflow-y-auto no-print-backdrop">

    <div class="fixed top-0 left-0 right-0 h-14 bg-slate-800 text-white flex justify-between items-center px-6 no-print z-50 shadow-xl">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-300">Anteprima di Stampa</span>
        </div>
        <div class="flex gap-3">
            <button @click="$wire.close()" class="text-[10px] font-bold uppercase px-4 py-4 bg-slate-700 hover:bg-slate-600 rounded transition">
                Chiudi
            </button>
            <button onclick="window.print()" class="text-[10px] font-bold uppercase px-6 py-4 bg-blue-600 hover:bg-blue-500 rounded shadow-lg shadow-blue-900/50 transition">
                Stampa Documento
            </button>
        </div>
    </div>

    <div class="pt-20 pb-10 flex justify-center print-wrapper">
        <div class="bg-white shadow-2xl print-area" 
             style="width: {{ ($printData['orientation'] ?? 'portrait') === 'landscape' ? '297mm' : '210mm' }}; min-height: 297mm;">
            
            @if($printData)
                <div class="print-inner-content">
                    @include($printData['view'], $printData['data'])
                </div>
            @endif
        </div>
    </div>

    <style>
        /* --- STILI PER LO SCHERMO --- */
        [x-cloak] { display: none !important; }
        .print-inner-content { padding: 10mm; }

        /* --- STILI PER LA STAMPA --- */
        @media print {
            /* 1. Forza l'orientamento del foglio tramite CSS */
            @page {
                size: A4 {{ $printData['orientation'] ?? 'portrait' }} !important;
                margin: 0mm; /* Gestiamo i margini tramite padding interno */
            }

            /* 2. Nascondi tutto ciò che non è il modale */
            body > *:not(.no-print-backdrop) {
                display: none !important;
            }

            /* 3. Reset del container per la stampante */
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                overflow: visible !important;
            }

            .no-print-backdrop {
                position: static !important;
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }

            .no-print {
                display: none !important;
            }

            /* 4. Il foglio deve occupare l'intera pagina di stampa */
            .print-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            .print-area {
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                margin: 0 !important;
                border: none !important;
                visibility: visible !important;
            }

            .print-inner-content {
                padding: 10mm !important;
                visibility: visible !important;
            }

            /* 5. Forza la stampa dei colori (background-color delle tabelle) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Evita che le righe delle tabelle vengano spezzate a metà */
            tr { page-break-inside: avoid !important; }
            thead { display: table-header-group !important; }
        }
    </style>
</div>
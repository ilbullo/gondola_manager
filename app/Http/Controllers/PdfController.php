<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Genera e restituisce un PDF in download.
     */
    public function generate(Request $request)
    {
        // Preleviamo i dati (pull rimuove automaticamente dalla sessione)
        $config = session()->pull('pdf_generate');

        if (!$config) {
            abort(404, 'Nessun dato trovato per la generazione del PDF.');
        }

        /**
         * SICUREZZA & COERENZA (SOLID):
         * Questo garantisce che la vista Blade carichi i tag <html> e <body> necessari a DomPDF.
         */
        $data = $config['data'] ?? [];

        // Creazione istanza PDF
        $pdf = Pdf::loadView($config['view'], $data);

        /**
         * CONFIGURAZIONE DINAMICA:
         * Invece di forzare 'a4', leggiamo il valore passato dal Service (es. 'a2' per la tabella grande).
         */
        $paperSize = $config['paper'] ?? 'a4';
        $orientation = $config['orientation'] ?? 'portrait';

        $pdf->setPaper($paperSize, $orientation);

        // Opzionale: Se vuoi permettere la visualizzazione nel browser invece del download forzato
        // return $pdf->stream($config['filename']);

        return $pdf->download($config['filename'] ?? 'documento.pdf');
    }
}

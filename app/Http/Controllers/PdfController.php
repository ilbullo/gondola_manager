<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf; // Facade per generare PDF con DomPDF
use Illuminate\Http\Request;

/**
 * PdfController
 * 
 * Controller per la generazione e il download di PDF.
 * Recupera i dati dalla sessione e crea un file PDF utilizzando una view Blade.
 */
class PdfController extends Controller
{
    /**
     * Genera e restituisce un PDF in download.
     *
     * @param Request $request Oggetto della richiesta HTTP
     * @return \Illuminate\Http\Response
     */
    public function generate(Request $request)
    {
        // Controlla se esiste la chiave 'pdf_generate' nella sessione
        if (!session()->has('pdf_generate')) {
            // Se non ci sono dati da generare, restituisce un 404
            abort(404, 'Nessun PDF da generare');
        }

        // Preleva i dati di configurazione dalla sessione e li rimuove
        $config = session()->pull('pdf_generate'); // ['view' => ..., 'data' => ..., 'filename' => ..., 'orientation' => ...]

        // Carica la view e genera il PDF
        $pdf = Pdf::loadView($config['view'], $config['data'])
                  ->setPaper('a4', $config['orientation'] ?? 'portrait'); // Imposta il formato e l'orientamento

        // Restituisce il PDF come download con il nome specificato
        return $pdf->download($config['filename']);
    }
}

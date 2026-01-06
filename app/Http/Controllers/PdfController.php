<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf; // Facade per generare PDF con DomPDF
use Illuminate\Http\Request;

/**
 * Class PdfController
 *
 * @package App\Http\Controllers
 *
 * Gestisce il rendering finale e il download dei documenti PDF del sistema.
 * Utilizza un pattern a "consegna differita": i dati vengono preparati dai Services,
 * parcheggiati temporaneamente in sessione e infine consumati da questo controller.
 *
 * RESPONSABILITÀ (SOLID):
 * 1. Rendering: Trasforma i template Blade e i dati dinamici in documenti PDF tramite DomPDF.
 * 2. Session Orchestration: Gestisce il recupero e la pulizia automatica (pull) dei dati di stampa,
 * garantendo che le informazioni sensibili non rimangano in memoria oltre il necessario.
 * 3. Configuration Management: Applica dinamicamente impostazioni di pagina (A4, A2) e
 * orientamento (Portrait, Landscape) in base alle necessità del report specifico.
 * 4. User Experience: Gestisce il download forzato con nomi file dinamici e coerenti.
 *
 * FLUSSO DI LAVORO:
 * 1. Un componente Livewire prepara i dati tramite un Service.
 * 2. Il componente esegue un `Session::flash('pdf_generate', $config)`.
 * 3. Il componente reindirizza alla rotta gestita da questo controller.
 * 4. Il controller genera il PDF e pulisce la sessione.
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

    public function getPrintData()
    {
        if (!session()->has('pdf_generate')) {
            return null;
        }

        // Recuperiamo i dati e puliamo la sessione (pattern pull)
        $config = session()->pull('pdf_generate');

        return [
            'view'        => $config['view'],
            'data'        => $config['data'],
            'orientation' => $config['orientation'] ?? 'portrait'
        ];
    }
}

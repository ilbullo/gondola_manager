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
    if (!session()->has('pdf_generate')) {
        abort(404);
    }

    $config = session()->get('pdf_generate'); 
    
    $pdf = Pdf::loadView($config['view'], $config['data'])
              ->setPaper($config['paper'] ?? 'a4', $config['orientation'] ?? 'portrait');

    // Abilitiamo l'esecuzione degli script
    $pdf->setOption('isPhpEnabled', true);
    
    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    // Iniezione dello script di stampa direttamente nel Canvas
    $dompdf->getCanvas()->javascript("this.print();");

    $output = $dompdf->output();

    return response($output, 200, [
        'Content-Type' => 'application/pdf',
        // 'inline' forza l'apertura nel browser, impedendo il download automatico
        'Content-Disposition' => 'inline; filename="'.$config['filename'].'"',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'X-Frame-Options' => 'ALLOWALL' // Aiuta la compatibilità PWA
    ]);
}
}

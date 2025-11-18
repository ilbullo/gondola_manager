<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Assumendo Dompdf

class PdfController extends Controller
{
    public function generate(Request $request)
    {
        // 1. Validazione e recupero dati
        $request->validate([
            'view' => 'required|string',
            'data' => 'required|string', // I dati arrivano come stringa JSON
            'filename' => 'required|string',
            'orientation' => 'sometimes|in:portrait,landscape',
        ]);

        $view = $request->input('view');
        // Decodifica i dati serializzati dal frontend
        $data = json_decode($request->input('data'), true);
        $filename = $request->input('filename');
        $orientation = $request->input('orientation', 'portrait');

        try {
            // 2. Generazione del PDF usando la vista Blade specificata
            $pdf = Pdf::loadView($view, $data)
                      ->setPaper('a4', $orientation);

            // 3. Forzare il download del PDF
            return $pdf->download($filename);

        } catch (\Exception $e) {
            // Gestione errori (es. vista non trovata, dati non validi)
            return response('Errore durante la generazione del PDF: ' . $e->getMessage(), 500);
        }
    }
}

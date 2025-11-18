<?php
namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function generate(Request $request)
    {
        if (!session()->has('pdf_generate')) {
            abort(404, 'Nessun PDF da generare');
        }

        $config = session()->pull('pdf_generate'); // lo prende e lo cancella

        $pdf = Pdf::loadView($config['view'], $config['data'])
                  ->setPaper('a4', $config['orientation'] ?? 'portrait');

        return $pdf->download($config['filename']);
    }
}
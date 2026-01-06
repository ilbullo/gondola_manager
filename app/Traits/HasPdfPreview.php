<?php
namespace App\Traits;

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Session;

trait HasPdfPreview
{
    public function openPdfPreview(string $view, array $data, string $orientation = 'portrait', string $paper = 'a4')
    {
        // SOLID: Incapsuliamo i dati garantendo che 'data' sia un array
        $config = [
            'view'        => $view,
            'data'        => $data, 
            'orientation' => $orientation,
            'paper'       => $paper,
        ];

        Session::put('pdf_generate', $config);
        Session::save();

        // Recuperiamo i dati tramite il bridge del controller
        $printData = (new PdfController)->getPrintData();

        // Importante: verifichiamo che i dati siano pronti prima del dispatch
        if ($printData && is_array($printData['data'])) {
            $this->dispatch('open-print-modal', data: $printData);
        }
    }
}
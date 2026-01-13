<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

//Route::view('/', 'welcome');

/*Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
    ->name('dashboard');
*/

Route::redirect('/', '/login')->name('home');

Route::get('/termini-e-condizioni', App\Livewire\Component\LegalTerms::class)
    ->middleware('auth')
    ->name('legal.terms');

Route::group(
    [
        'middleware' => ['auth','verified','legal'],
        'prefix' => '',
    ],
    function () {
        // Solo Admin e Bancale
    Route::middleware('role:admin,bancale')->group(function () {
                Route::view('agency-manager', 'livewire.pages.agency-manager')->name('agency-manager');
                Route::view('table-manager', 'livewire.pages.table-manager')->name('table-manager');
                Route::get('generate-pdf', [PdfController::class, 'generate'])->name('generate.pdf');
                Route::get('/print-receipt/{license}', function ($licenseId) {
                    // Qui recuperi i dati della licenza dal database
                    // e restituisci una vista Blade pulita
                    return view('print.thermal', ['id' => $licenseId]);
                })->name('print.receipt');

                Route::get('/print-report', function () {
                    $config = session()->get('pdf_generate'); // Usa get invece di pull

                    if (!$config || !isset($config['view'])) {
                        // Invece di un errore 500, restituiamo un messaggio leggibile
                        return "Errore: Dati di stampa non trovati in sessione. Riprova dalla tabella.";
                    }

                    $data = $config['data'];
                    return view($config['view'], $data);
                })->name('print.report');
    });

    // Solo Admin
    Route::middleware('role:admin')->group(function () {
            Route::view('user-manager', 'livewire.pages.user-manager')->name('user-manager');
    });
        Route::view('dashboard', 'dashboard')->name('dashboard');
        Route::view('profile', 'profile')->name('profile');

    });

   // Route::view('test','test')->name('test');

require __DIR__.'/auth.php';



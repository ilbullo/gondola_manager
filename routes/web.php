<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

//Route::view('/', 'welcome');

/*Route::view('dashboard', 'dashboard')
        ->middleware(['auth', 'verified'])
    ->name('dashboard');
*/

Route::redirect('/', '/login');

Route::group(
    [
        'middleware' => ['auth','verified'],
        'prefix' => '',
    ],
    function () {
        // Solo Admin e Bancale
    Route::middleware('role:admin,bancale')->group(function () {
                Route::view('agency-manager', 'livewire.pages.agency-manager')->name('agency-manager');
                Route::view('table-manager', 'livewire.pages.table-manager')->name('table-manager');
                Route::get('generate-pdf', [PdfController::class, 'generate'])->name('generate.pdf');
    });

    // Solo Admin
    Route::middleware('role:admin')->group(function () {
            Route::view('user-manager', 'livewire.pages.user-manager')->name('user-manager');
    });
        Route::view('dashboard', 'dashboard')->name('dashboard');
        Route::view('profile', 'profile')->name('profile');

    });


require __DIR__.'/auth.php';

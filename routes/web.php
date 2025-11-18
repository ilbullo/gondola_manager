<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::group(
    [
        'middleware' => ['auth',],
        'prefix' => '',
    ],
    function () {

        Route::view('agency-manager', 'livewire.pages.agency-manager')->name('agency-manager');
        Route::view('table-manager', 'livewire.pages.table-manager')->name('table-manager');
        Route::get('/generate-pdf', [PdfController::class, 'generate'])->name('generate.pdf');
    });


require __DIR__.'/auth.php';

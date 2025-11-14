<?php

use Illuminate\Support\Facades\Route;

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


    });


require __DIR__.'/auth.php';

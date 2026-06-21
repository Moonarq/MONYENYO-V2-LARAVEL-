<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrintResiController;

Route::get('/', function () {
    return view('welcome');
});

 
Route::get('/admin/print-resi/{order}', PrintResiController::class)
    ->name('print-resi')
    ->middleware(['web', 'auth']);
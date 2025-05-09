<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HashController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('main');
});

Route::post('/document/encrypt', [HashController::class, 'encrypt'])->name('document.encrypt');
Route::post('/document/decrypt', [HashController::class, 'decrypt'])->name('document.decrypt');
Route::post('/document/generateKeys',[HashController::class,'generateKeys'])->name('document.generate');
Route::post('/document/make',[HashController::class,'makePdf'])->name('makePDF');
Route::get('/document/pdf',[HashController::class,'viewPdf'])->name('viewPdf');
Route::post('/document/hash',[HashController::class,'hash'])->name('hashpdf');
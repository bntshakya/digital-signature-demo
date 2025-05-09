<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HashController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/hash/get',[HashController::class,'getHash'])->name('gethash');
Route::middleware('web')->group(function () {

});
// Route::post('/document/encrypt', [HashController::class, 'encrypt'])->name('document.encrypt');
// Route::post('/document/decrypt', [HashController::class, 'decrypt'])->name('document.decrypt');

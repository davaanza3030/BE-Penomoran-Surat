<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\KelolaUserController;
use App\Http\Controllers\NumberFormatController;
use App\Http\Controllers\IncomingLetterController;
use App\Http\Controllers\OutgoingLetterController;
// use App\Http\Controllers\LetterController;

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

Route::group(['middleware' => 'api','prefix' => 'auth'], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => ['auth:api', 'roleCheck:admin']], function () {
    Route::get('/kelola-user', [KelolaUserController::class, 'index']);
    Route::post('/kelola-user', [KelolaUserController::class, 'store']);
    Route::put('/kelola-user/{id}', [KelolaUserController::class, 'update']);
    Route::delete('/kelola-user/{id}', [KelolaUserController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:api', 'roleCheck:admin']], function () {
    Route::post('/categories', [CategoriesController::class, 'store']);
    Route::get('/categories/{id}', [CategoriesController::class, 'show']);
    Route::put('/categories/{id}', [CategoriesController::class, 'update']);
    Route::delete('/categories/{id}', [CategoriesController::class, 'destroy']);
});

// Route::group(['middleware' => ['auth:api', 'roleCheck:admin']], function () {
//     Route::get('/number-formats/{id}', [NumberFormatController::class, 'show']);
//     Route::post('/number-formats', [NumberFormatController::class, 'store']);
//     Route::put('/number-formats/{id}', [NumberFormatController::class, 'update']);
//     Route::delete('/number-formats/{id}', [NumberFormatController::class, 'destroy']);
// });

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/categories', [CategoriesController::class, 'index']);
    Route::get('/number-formats', [NumberFormatController::class, 'index']);
});

Route::get('/outgoing-letters/last-id', [OutgoingLetterController::class, 'getLastId']);
// Route::group(['middleware' => ['auth:api']], function () {
//     Route::get('/letters', [LetterController::class, 'index']);
//     Route::post('/letters', [LetterController::class, 'store']);
//     Route::get('/letters/{id}', [LetterController::class, 'show']);
//     Route::put('/letters/{id}', [LetterController::class, 'update']);
//     Route::delete('/letters/{id}', [LetterController::class, 'destroy']);
// });

// Route::get('/letters/{id}/download', [LetterController::class, 'downloadAttachment'])->name('letters.download');

Route::group(['middleware' => 'auth:api'], function () {
    // Routes untuk Surat Masuk
    Route::get('/incoming-letters', [IncomingLetterController::class, 'index']);
    Route::post('/incoming-letters', [IncomingLetterController::class, 'store']);
    Route::get('/incoming-letters/{id}', [IncomingLetterController::class, 'show']);
    Route::get('/incoming-letters/stats', [IncomingLetterController::class, 'monthlyStats']);
    Route::get('/incoming-letters/{id}/download', [IncomingLetterController::class, 'downloadAttachment'])->name('incoming-letters.download');
    Route::delete('/incoming-letters/{id}', [IncomingLetterController::class, 'destroy']);

    // Routes untuk Surat Keluar
    Route::get('/outgoing-letters', [OutgoingLetterController::class, 'index']);
    Route::post('/outgoing-letters', [OutgoingLetterController::class, 'store']);
    Route::get('/outgoing-letters/{id}', [OutgoingLetterController::class, 'show']);
    Route::get('/outgoing-letters/stats', [OutgoingLetterController::class, 'monthlyStats']);
    Route::delete('/outgoing-letters/{id}', [OutgoingLetterController::class, 'destroy']);
});

Route::get('/outgoing-letters/{id}/download', [OutgoingLetterController::class, 'downloadAttachment'])->name('outgoing-letters.download');

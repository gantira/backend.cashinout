<?php

use App\Http\Controllers\Auth\MeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Auth::loginUsingId(2);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('me', [MeController::class, '__invoke']);
});
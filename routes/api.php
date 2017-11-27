<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/balance', 'FinanceController@balance');
Route::post('/deposit', 'FinanceController@deposit');
Route::post('/withdraw', 'FinanceController@withdraw');
Route::post('/transfer', 'FinanceController@transfer');
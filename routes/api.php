<?php

use Illuminate\Http\Request;

Route::get('/balance', 'FinanceController@balance');
Route::post('/deposit', 'FinanceController@deposit');
Route::post('/withdraw', 'FinanceController@withdraw');
Route::post('/transfer', 'FinanceController@transfer');
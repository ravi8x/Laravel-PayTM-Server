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

Route::get('/products', 'Api\ProductController@getProducts');
Route::post('/orders', 'Api\OrderController@createOrder');
Route::get('/transactions', 'Api\TransactionController@getTransactions');
Route::post('/prepareOrder', 'Api\OrderController@prepareOrder');
Route::post('/verifyChecksum', 'Api\OrderController@verifyChecksum');
Route::get('/appConfig', 'Api\AppController@getAppConfig');
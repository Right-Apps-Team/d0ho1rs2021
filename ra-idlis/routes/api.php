<?php

use Illuminate\Http\Request;
use App\Http\Middleware\APIMiddleware;

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

Route::get(
    '/clients', 
    'Client\Api\ClientApiController@index'
)->middleware([APIMiddleware::class]);

Route::post(
    '/application/validate-name/',
    'Client\Api\ApplicationApiController@check'
)->middleware([APIMiddleware::class]);

Route::get(
    '/regions/get-all/',
    'Client\Api\RegionApiControlller@fetchAll'
)->middleware([APIMiddleware::class]);
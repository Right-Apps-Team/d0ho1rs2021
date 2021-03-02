<?php
use App\Http\Middleware\CustomAuthChecker;

Route::get(
    'dashboard', 
    'Client\ClientDashboardController@index'
)->middleware([CustomAuthChecker::class]);

Route::get(
    'dashboard/apply', 
    'Client\ClientDashboardController@apply'
)->middleware([CustomAuthChecker::class]);

Route::get(
    'dashboard/new-application/{appid?}', 
    'Client\ClientDashboardController@newApplication'
)->middleware([CustomAuthChecker::class]);
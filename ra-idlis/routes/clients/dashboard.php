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
    'dashboard/new-application/', 
    'Client\ClientDashboardController@newApplication'
)->middleware([CustomAuthChecker::class]);


Route::get(
    'dashboard/application/certificate-of-need/', 
    'Client\ClientDashboardController@newApplication'
)->middleware([CustomAuthChecker::class]);


Route::get(
    'dashboard/application/permit-to-construct/', 
    'Client\ClientDashboardController@permitToConstruct'
)->middleware([CustomAuthChecker::class]);

//my changes
Route::get('dashboard/application/authority-to-operate/', 
    'Client\ClientDashboardController@authorityToOperate'
)->middleware([CustomAuthChecker::class]);
<?php

use App\ProxyCheck;
use Illuminate\Support\Facades\Route;

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
    return view('proxyCheck');
});

Route::get('/history', function () {
    $proxyChecks = ProxyCheck::with('proxyResults')->latest()->get();

    return view('checkHistory', compact('proxyChecks'));
});

Route::get('/history/{proxyCheck}', function (ProxyCheck $proxyCheck) {
    return view('checkHistoryDetails', compact('proxyCheck'));
})->name('check.details');


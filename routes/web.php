<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartController;

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
Route::get('/ajuda', [App\Http\Controllers\HomeController::class, 'ajuda'])->name('ajuda');

Route::get('/chart/quantidade-eventos', [ChartController::class, 'quantidadeEventos']);
Route::get('/chart/todos-eventos', [ChartController::class, 'todosEventos']);

Route::get('/reports', [App\Http\Controllers\HomeController::class, 'reports'])->name('reports');
Route::get('/reports/evento/{id}/{tipo}', [App\Http\Controllers\HomeController::class, 'evento'])->name('reports.evento');

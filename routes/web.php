<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartController;

Route::get('/', function () {
    return redirect()->route('login');
    // return view('welcome');
});

Auth::routes();

/** Admin */
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
Route::get('/ajuda', [App\Http\Controllers\HomeController::class, 'ajuda'])->name('ajuda');
Route::get('/reports', [App\Http\Controllers\HomeController::class, 'reports'])->name('reports');
Route::get('/reports/evento/{id}/{tipo}', [App\Http\Controllers\HomeController::class, 'evento'])->name('reports.evento');

/** Charts */
Route::prefix('chart')->group(function () {
    Route::get('/quantidade-eventos', [ChartController::class, 'quantidadeEventos']);
    Route::get('/todos-eventos', [ChartController::class, 'todosEventos']);
});


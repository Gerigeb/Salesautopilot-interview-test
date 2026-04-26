<?php

use App\Http\Controllers\ListController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ListController::class, 'index'])->name('home');
Route::get('/listDetails/{id}', [ListController::class, 'show'])->name('lists.show');

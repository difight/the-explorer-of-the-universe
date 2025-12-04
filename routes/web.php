<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Защищенные маршруты
Route::middleware(['auth.inertia.api'])->group(function () {
    Route::get('/my', function () {
        return Inertia::render('my');
    })->name('my');

    Route::get('/profile', function () {
        return Inertia::render('profile');
    })->name('profile');
});

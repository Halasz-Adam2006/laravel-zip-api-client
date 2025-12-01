<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CountyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/counties', [CountyController::class, 'index'])->name('counties.show');

Route::get('/counties/{name}/alphabet', [CountyController::class, 'showAlphabet'])->name('counties.alphabet');

// Development API route for counties (returns JSON). Replace with `routes/api.php` or DB-backed endpoint as needed.
Route::get('/api/counties', [CountyController::class, 'showCounties']);

Route::middleware('auth')->group(function () {
    Route::get('/api/county/{name}/{letter}', [CountyController::class, 'getCitiesByLetter']);
});

require __DIR__.'/auth.php';

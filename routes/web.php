<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;
use Livewire\Volt\Volt;

// Home Route (Volt component for search)
Volt::route('/', 'search')->name('home');
Volt::route('/search', 'search')->name('search');

// Signature Pad Routes
Route::get('/tutorial', [SignatureController::class, 'index'])->name('signpad.index'); // Change this to /tutorial
// Route::post('/save', [SignatureController::class, 'save'])->name('signpad.save');
Route::post('/save', [SignatureController::class, 'saveSignature'])->name('signpad.save');
// Route::delete('/delete/{id}', [SignatureController::class, 'delete'])->name('signpad.delete');
Route::delete('/signpad/{id}', [SignatureController::class, 'delete'])->name('signpad.delete');


// ----- old syntax from main -----
// use Illuminate\Support\Facades\Route;
// use Livewire\Volt\Volt;

// Route::get('/', function () {
//     return view('welcome');
// });

// Volt::Route('search', 'search')->name('search');



# kalat ng routes hahahahaha
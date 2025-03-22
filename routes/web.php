<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;
use Livewire\Volt\Volt;

// Home Route (Volt component for search)
Volt::route('/', 'search')->name('home');
Volt::route('/search', 'search')->name('search');

// Signature Pad Routes
Route::get('/signature', [SignatureController::class, 'index'])->name('signpad.index');
Route::post('/signpad/save', [SignatureController::class, 'saveSignature'])->name('signpad.save');
Route::delete('/signpad/{id}/delete', [SignatureController::class, 'delete'])->name('signpad.delete');


// ----- old syntax from main -----
// use Illuminate\Support\Facades\Route;
// use Livewire\Volt\Volt;

// Route::get('/', function () {
//     return view('welcome');
// });

// Volt::Route('search', 'search')->name('search');



# kalat ng routes hahahahaha
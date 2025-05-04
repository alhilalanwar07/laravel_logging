<?php

use Illuminate\Support\Facades\{Route, Auth};
use App\Http\Controllers\LogViewController;

// disable register, reset password
Auth::routes(['register' => false, 'reset' => false]);

// jika ke /, redirect ke /login
Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::view('dashboard', 'dashboard')->name('home');
    Route::view('manajemen-user', 'manajemen-user')->name('admin.manajemen-user');
    Route::view('profil', 'profil')->name('profil');

    Route::view('logs', 'logs')->name('logs');
});
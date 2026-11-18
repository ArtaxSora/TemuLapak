<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ListingController;
use App\Http\Middleware\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', [ListingController::class, 'home'])->name('home');
Route::get('/cari', [ListingController::class, 'search']);
Route::get('/tempat/{id}', [ListingController::class, 'detail'])->whereNumber('id');
Route::get('/masuk', [AccountController::class, 'loginForm'])->name('login');
Route::post('/masuk', [AccountController::class, 'login'])->middleware('throttle:10,1');
Route::get('/daftar', [AccountController::class, 'registerForm']);
Route::post('/daftar', [AccountController::class, 'register'])->middleware('throttle:15,1');
Route::post('/keluar', [AccountController::class, 'logout']);
Route::middleware(Role::class)->group(function () {
    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/profil', [AccountController::class, 'profile']);
    Route::post('/profil', [AccountController::class, 'saveProfile']);
    Route::get('/notifikasi', [AccountController::class, 'notifications']);
    Route::post('/notifikasi/baca', [AccountController::class, 'readNotifications']);
});
Route::middleware(Role::class.':PEMILIK')->prefix('pemilik')->group(function () {
    Route::get('/tempat', [ListingController::class, 'mine']);
    Route::get('/tempat/baru', [ListingController::class, 'form']);
    Route::get('/tempat/{id}/edit', [ListingController::class, 'form']);
    Route::post('/tempat/{id?}', [ListingController::class, 'save']);
    Route::get('/booking', [BookingController::class, 'history']);
    Route::get('/booking/{id}', [BookingController::class, 'detail'])->whereNumber('id');
    Route::get('/laporan', [BookingController::class, 'history']);
    Route::get('/ulasan', [BookingController::class, 'reviews']);
    Route::post('/booking/{id}/keputusan', [BookingController::class, 'decide']);
});
Route::middleware(Role::class.':ADMIN')->prefix('admin')->group(function () {
    Route::get('/verifikasi', [ListingController::class, 'queue']);
    Route::post('/verifikasi/{id}', [ListingController::class, 'verifyListing']);
    Route::get('/pengguna', [AccountController::class, 'users']);
    Route::post('/pengguna/{id}', [AccountController::class, 'saveUser']);
});
Route::middleware(Role::class.':PENYEWA')->group(function () {
    Route::get('/tempat/{id}/estimasi', [BookingController::class, 'estimate'])->whereNumber('id');
    Route::get('/tempat/{id}/booking', [ListingController::class, 'bookingForm']);
    Route::post('/tempat/{id}/booking', [BookingController::class, 'requestBooking']);
    Route::get('/booking', [BookingController::class, 'history']);
    Route::get('/booking/{id}', [BookingController::class, 'detail'])->whereNumber('id');
    Route::post('/booking/{id}/batal', [BookingController::class, 'cancel']);
    Route::post('/booking/{id}/ulasan', [BookingController::class, 'review']);
});

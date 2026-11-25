<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeteksiController;
use App\Http\Controllers\LogAlarmController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PerangkatController;
use App\Http\Controllers\TelemetriController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Rute root: arahkan ke dashboard jika sudah login, atau login jika belum
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Autentikasi Web
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Seluruh halaman utama web & logout diwajibkan login (middleware auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'latestData'])->name('dashboard.data');
    Route::post('/dashboard/uji-sirine', [DashboardController::class, 'triggerUjiSirine'])->name('dashboard.uji-sirine');
    Route::post('/dashboard/uji-sirine/stop', [DashboardController::class, 'stopUjiSirine'])->name('dashboard.uji-sirine.stop');
    Route::post('/dashboard/situasi-aman', [DashboardController::class, 'nyatakanSituasiAman'])->name('dashboard.situasi-aman');

    Route::get('/perangkat', [PerangkatController::class, 'index'])->name('perangkat.index');
    Route::get('/perangkat/data', [PerangkatController::class, 'latestData'])->name('perangkat.data');

    Route::get('/telemetri', [TelemetriController::class, 'index'])->name('telemetri.index');
    Route::get('/telemetri/data', [TelemetriController::class, 'latestData'])->name('telemetri.data');

    Route::get('/deteksi', [DeteksiController::class, 'index'])->name('deteksi.index');
    Route::get('/deteksi/data', [DeteksiController::class, 'latestData'])->name('deteksi.data');

    Route::get('/log-alarm', [LogAlarmController::class, 'index'])->name('log-alarm.index');
    Route::get('/log-alarm/data', [LogAlarmController::class, 'latestData'])->name('log-alarm.data');
    Route::patch('/log-alarm/{logAlarm}/catatan', [LogAlarmController::class, 'updateCatatan'])
        ->name('log-alarm.catatan')
        ->where('logAlarm', '[0-9]+');

    Route::get('/lokasi', [LokasiController::class, 'index'])->name('lokasi.index');
    Route::put('/lokasi/{lokasi}', [LokasiController::class, 'update'])->name('lokasi.update');

    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::post('/pengguna', [PenggunaController::class, 'store'])->name('pengguna.store');
    Route::post('/pengguna/{pengguna}/kode-aktivasi', [PenggunaController::class, 'regenerateActivationCode'])->name('pengguna.kode-aktivasi');
    Route::post('/pengguna/{pengguna}/aktifkan', [PenggunaController::class, 'activate'])->name('pengguna.aktifkan');
});

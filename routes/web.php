<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeteksiController;
use App\Http\Controllers\LogAlarmController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PerangkatController;
use App\Http\Controllers\TelemetriController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/data', [DashboardController::class, 'latestData'])->name('dashboard.data');
Route::get('/perangkat/data', [PerangkatController::class, 'latestData'])->name('perangkat.data');
Route::get('/perangkat', [PerangkatController::class, 'index'])->name('perangkat.index');
Route::get('/telemetri/data', [TelemetriController::class, 'latestData'])->name('telemetri.data');
Route::get('/telemetri', [TelemetriController::class, 'index'])->name('telemetri.index');
Route::get('/deteksi/data', [DeteksiController::class, 'latestData'])->name('deteksi.data');
Route::get('/deteksi', [DeteksiController::class, 'index'])->name('deteksi.index');
Route::get('/log-alarm/data', [LogAlarmController::class, 'latestData'])->name('log-alarm.data');
Route::get('/log-alarm', [LogAlarmController::class, 'index'])->name('log-alarm.index');
Route::get('/lokasi', [LokasiController::class, 'index'])->name('lokasi.index');
Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');

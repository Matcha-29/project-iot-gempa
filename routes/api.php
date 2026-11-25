<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\NodeHeartbeatController;
use App\Http\Controllers\Api\ResponsWargaController;
use App\Http\Controllers\Api\SensorApiController;
use Illuminate\Support\Facades\Route;

// Autentikasi Warga
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/aktivasi', [AuthController::class, 'aktivasi'])->middleware('throttle:10,1');

// Monitoring & Status Wilayah
Route::get('/status-wilayah', [MonitoringController::class, 'statusWilayah']);
Route::get('/monitoring', [MonitoringController::class, 'statusWilayah']);
Route::get('/status', [MonitoringController::class, 'statusWilayah']);

// Riwayat Gempa & Deteksi
Route::get('/riwayat-gempa', [MonitoringController::class, 'riwayatGempa']);
Route::get('/riwayat', [MonitoringController::class, 'riwayatGempa']);

// Respons Warga (mendukung Bearer token Sanctum maupun id_pengguna)
Route::post('/respons-warga', [ResponsWargaController::class, 'store']);

// Ingest Sensor & Heartbeat dari ESP32
Route::post('/telemetri/kirim', [SensorApiController::class, 'store']);
Route::post('/telemetri/heartbeat', [NodeHeartbeatController::class, 'store'])->middleware('throttle:30,1');

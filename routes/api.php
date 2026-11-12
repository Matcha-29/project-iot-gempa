<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\NodeHeartbeatController;
use App\Http\Controllers\Api\ResponsWargaController;
use App\Http\Controllers\Api\SensorApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/aktivasi', [AuthController::class, 'aktivasi'])->middleware('throttle:10,1');

Route::get('/status-wilayah', [MonitoringController::class, 'statusWilayah']);
Route::get('/riwayat-gempa', [MonitoringController::class, 'riwayatGempa']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/respons-warga', [ResponsWargaController::class, 'store']);
});

Route::post('/telemetri/kirim', [SensorApiController::class, 'store']);
Route::post('/telemetri/heartbeat', [NodeHeartbeatController::class, 'store'])->middleware('throttle:30,1');

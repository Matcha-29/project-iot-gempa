<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('pages.dashboard');
});

Route::get('/perangkat', function () {
    return view('pages.perangkat');
});

Route::get('/telemetri', function () {
    return view('pages.telemetri');
});

Route::get('/deteksi', function () {
    return view('pages.deteksi');
});

Route::get('/log-alarm', function () {
    return view('pages.log-alarm');
});

Route::get('/lokasi', function () {
    return view('pages.lokasi');
});

Route::get('/pengguna', function () {
    return view('pages.pengguna');
});
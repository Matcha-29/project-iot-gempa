<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;

class PenggunaController extends Controller
{
    public function index()
    {
        $pengguna = Pengguna::with('lokasi')->latest('created_at')->paginate(15);
        return view('pages.pengguna', compact('pengguna'));
    }
}

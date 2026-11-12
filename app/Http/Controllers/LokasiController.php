<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;

class LokasiController extends Controller
{
    public function index()
    {
        $lokasi = Lokasi::withCount(['perangkat', 'pengguna'])->latest('created_at')->paginate(15);
        return view('pages.lokasi', compact('lokasi'));
    }
}

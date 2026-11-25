<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePetugas();

        $lokasi = $this->activeLocation();
        $peran = $request->query('peran') === 'petugas' ? 'petugas' : 'warga';
        $pencarian = trim((string) $request->query('q', ''));

        $ringkasan = $lokasi
            ? Pengguna::query()
                ->where('id_lokasi', $lokasi->getKey())
                ->selectRaw('peran, status_akun, COUNT(*) as jumlah')
                ->groupBy('peran', 'status_akun')
                ->get()
            : collect();

        $statistik = [
            'totalWarga' => (int) $ringkasan->where('peran', 'warga')->sum('jumlah'),
            'akunAktif' => (int) $ringkasan->where('peran', 'warga')->where('status_akun', 'aktif')->sum('jumlah'),
            'belumAktivasi' => (int) $ringkasan->where('peran', 'warga')->where('status_akun', 'belum_aktivasi')->sum('jumlah'),
            'petugas' => (int) $ringkasan->where('peran', 'petugas')->sum('jumlah'),
        ];

        $daftarPengguna = Pengguna::query()
            ->with('lokasi')
            ->when($lokasi, fn ($query) => $query->where('id_lokasi', $lokasi->getKey()))
            ->when(! $lokasi, fn ($query) => $query->whereRaw('1 = 0'))
            ->where('peran', $peran)
            ->when($pencarian !== '', function ($query) use ($pencarian): void {
                $query->where(function ($query) use ($pencarian): void {
                    $query->where('nama', 'like', "%{$pencarian}%")
                        ->orWhere('no_telepon', 'like', "%{$pencarian}%");
                });
            })
            ->latest('created_at')
            ->paginate(8)
            ->withQueryString();

        return view('pages.pengguna', [
            'pengguna' => $daftarPengguna,
            'peranAktif' => $peran,
            'statistik' => $statistik,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePetugas();

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_telepon' => ['required', 'string', 'max:20', Rule::unique('pengguna', 'no_telepon')],
            'nomor_rumah' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('pengguna', 'email')],
            'catatan_khusus' => ['nullable', 'string', 'max:255'],
        ]);

        $lokasi = $this->activeLocation();
        abort_if(! $lokasi, 422, 'Belum ada lokasi yang dapat digunakan untuk membuat akun warga.');

        $warga = Pengguna::create([
            'id_lokasi' => $lokasi->getKey(),
            'nama' => $data['nama'],
            'email' => $data['email'] ?? null,
            'no_telepon' => $data['no_telepon'],
            'peran' => 'warga',
            'status_akun' => 'belum_aktivasi',
            'nomor_rumah' => $data['nomor_rumah'],
            'catatan_khusus' => $data['catatan_khusus'] ?? null,
        ]);

        $kode = $this->createActivationCode($warga);

        return redirect()->route('pengguna.index')->with('kode_aktivasi_baru', [
            'nama' => $warga->nama,
            'kode' => $kode,
            'aksi' => 'dibuat',
        ]);
    }

    public function regenerateActivationCode(Pengguna $pengguna): RedirectResponse
    {
        $this->authorizePetugas();

        $warga = $this->wargaInActiveLocation($pengguna);
        abort_unless(in_array($warga->status_akun, ['belum_aktivasi', 'aktif'], true), 404);

        $kode = $this->createActivationCode($warga);

        return redirect()->route('pengguna.index')->with('kode_aktivasi_baru', [
            'nama' => $warga->nama,
            'kode' => $kode,
            'aksi' => 'diperbarui',
        ]);
    }

    public function activate(Pengguna $pengguna): RedirectResponse
    {
        $this->authorizePetugas();

        $warga = $this->wargaInActiveLocation($pengguna);
        abort_unless($warga->status_akun === 'nonaktif', 404);

        $warga->forceFill([
            'status_akun' => 'aktif',
            'kode_aktivasi' => null,
            'kode_kedaluwarsa_pada' => null,
        ])->save();

        return back()->with('success', "Akun {$warga->nama} berhasil diaktifkan.");
    }

    private function createActivationCode(Pengguna $warga): string
    {
        $kode = 'RT01-'.Str::upper(Str::random(4));

        $warga->forceFill([
            'status_akun' => 'belum_aktivasi',
            'kode_aktivasi' => $kode,
            'kode_kedaluwarsa_pada' => now()->addHours(24),
        ])->save();

        return $kode;
    }

    private function activeLocation(): ?Lokasi
    {
        $idLokasiPetugas = Auth::user()?->id_lokasi;

        return Lokasi::query()->find($idLokasiPetugas) ?? Lokasi::query()->first();
    }

    private function wargaInActiveLocation(Pengguna $pengguna): Pengguna
    {
        $lokasi = $this->activeLocation();
        abort_if(! $lokasi, 404);

        return Pengguna::query()
            ->where('id_lokasi', $lokasi->getKey())
            ->where('peran', 'warga')
            ->whereKey($pengguna->getKey())
            ->firstOrFail();
    }

    private function authorizePetugas(): void
    {
        abort_unless(Auth::check(), 403);
    }
}

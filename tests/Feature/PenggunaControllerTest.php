<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PenggunaControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('lokasi');

        Schema::create('lokasi', function (Blueprint $table): void {
            $table->bigIncrements('id_lokasi');
            $table->string('nama_lokasi', 100);
            $table->string('alamat')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('nomor_petugas', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('pengguna', function (Blueprint $table): void {
            $table->bigIncrements('id_pengguna');
            $table->unsignedBigInteger('id_lokasi');
            $table->string('nama', 100);
            $table->string('email', 150)->nullable()->unique();
            $table->string('no_telepon', 20)->unique();
            $table->string('peran', 20);
            $table->string('kata_sandi')->nullable();
            $table->string('status_akun', 20);
            $table->string('kode_aktivasi')->nullable();
            $table->dateTime('kode_kedaluwarsa_pada')->nullable();
            $table->string('nomor_rumah', 20)->nullable();
            $table->decimal('latitude_rumah', 10, 7)->nullable();
            $table->decimal('longitude_rumah', 10, 7)->nullable();
            $table->string('catatan_khusus')->nullable();
            $table->string('fcm_token')->nullable();
            $table->dateTime('terakhir_masuk_pada')->nullable();
            $table->timestamps();
        });
    }

    public function test_new_resident_gets_a_plain_24_hour_code_accepted_by_flutter_activation_api(): void
    {
        $this->freezeTime();
        $locationId = $this->createLocation();
        $this->actingAs($this->createPetugas($locationId));

        $response = $this->post(route('pengguna.store'), [
            'nama' => 'Budi Santoso',
            'no_telepon' => '081234567890',
            'nomor_rumah' => '12',
            'email' => null,
            'catatan_khusus' => 'Akun bantuan evakuasi',
        ]);

        $response->assertRedirect(route('pengguna.index'))
            ->assertSessionHas('kode_aktivasi_baru');
        $warga = Pengguna::query()->where('no_telepon', '081234567890')->firstOrFail();
        $kode = $warga->kode_aktivasi;

        $this->assertMatchesRegularExpression('/^RT01-[A-Z0-9]{4}$/', $kode);
        $this->assertSame('belum_aktivasi', $warga->status_akun);
        $this->assertSame($locationId, $warga->id_lokasi);
        $this->assertSame(now()->addHours(24)->toDateTimeString(), $warga->kode_kedaluwarsa_pada->toDateTimeString());

        $this->get(route('pengguna.index'))
            ->assertOk()
            ->assertSee($kode)
            ->assertSee('AKUN DIBUAT');

        $this->postJson('/api/aktivasi', [
            'no_telepon' => '081234567890',
            'kode_aktivasi' => $kode,
            'kata_sandi' => 'kata-sandi-baru',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('pengguna', [
            'id_pengguna' => $warga->getKey(),
            'status_akun' => 'aktif',
            'kode_aktivasi' => null,
            'kode_kedaluwarsa_pada' => null,
        ]);
    }

    public function test_unauthenticated_request_redirects_to_login(): void
    {
        $this->get(route('pengguna.index'))
            ->assertRedirect(route('login'));
    }

    public function test_forbids_a_resident_from_user_management(): void
    {
        $locationId = $this->createLocation();
        $this->actingAs($this->createPengguna($locationId, 'warga'));

        $this->get(route('pengguna.index'))->assertForbidden();
    }

    public function test_resetting_an_active_resident_requires_activation_with_a_new_code(): void
    {
        $this->freezeTime();
        $locationId = $this->createLocation();
        $this->actingAs($this->createPetugas($locationId));
        $warga = $this->createPengguna($locationId, 'warga');

        $this->post(route('pengguna.kode-aktivasi', $warga))
            ->assertRedirect(route('pengguna.index'))
            ->assertSessionHas('kode_aktivasi_baru');

        $warga->refresh();

        $this->assertSame('belum_aktivasi', $warga->status_akun);
        $this->assertMatchesRegularExpression('/^RT01-[A-Z0-9]{4}$/', $warga->kode_aktivasi);
        $this->assertSame(now()->addHours(24)->toDateTimeString(), $warga->kode_kedaluwarsa_pada->toDateTimeString());
    }

    public function test_petugas_can_reactivate_a_nonactive_resident(): void
    {
        $locationId = $this->createLocation();
        $this->actingAs($this->createPetugas($locationId));
        $warga = $this->createPengguna($locationId, 'warga');
        $warga->forceFill([
            'status_akun' => 'nonaktif',
            'kode_aktivasi' => 'RT01-OLD1',
            'kode_kedaluwarsa_pada' => now()->addHour(),
        ])->save();

        $this->post(route('pengguna.aktifkan', $warga))->assertRedirect();

        $this->assertDatabaseHas('pengguna', [
            'id_pengguna' => $warga->getKey(),
            'status_akun' => 'aktif',
            'kode_aktivasi' => null,
            'kode_kedaluwarsa_pada' => null,
        ]);
    }

    public function test_petugas_cannot_issue_a_code_for_a_resident_in_another_location(): void
    {
        $petugasLocationId = $this->createLocation('Lokasi Petugas');
        $otherLocationId = $this->createLocation('Lokasi Lain');
        $this->actingAs($this->createPetugas($petugasLocationId));
        $warga = $this->createPengguna($otherLocationId, 'warga');

        $this->post(route('pengguna.kode-aktivasi', $warga))->assertNotFound();

        $this->assertDatabaseHas('pengguna', [
            'id_pengguna' => $warga->getKey(),
            'status_akun' => 'aktif',
            'kode_aktivasi' => null,
        ]);
    }

    private function createLocation(string $name = 'RT 01'): int
    {
        return (int) DB::table('lokasi')->insertGetId([
            'nama_lokasi' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_lokasi');
    }

    private function createPetugas(int $locationId): User
    {
        return User::query()->create([
            'id_lokasi' => $locationId,
            'nama' => 'Petugas RT',
            'email' => 'petugas@example.test',
            'no_telepon' => '080000000001',
            'peran' => 'petugas',
            'kata_sandi' => 'kata-sandi-petugas',
            'status_akun' => 'aktif',
        ]);
    }

    private function createPengguna(int $locationId, string $role): Pengguna
    {
        return Pengguna::query()->create([
            'id_lokasi' => $locationId,
            'nama' => 'Warga Contoh',
            'email' => 'warga@example.test',
            'no_telepon' => '080000000002',
            'peran' => $role,
            'kata_sandi' => 'kata-sandi-warga',
            'status_akun' => 'aktif',
        ]);
    }
}

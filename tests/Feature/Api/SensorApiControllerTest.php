<?php

namespace Tests\Feature\Api;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SensorApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['log_alarm', 'deteksi', 'level_getaran', 'sensor', 'perangkat'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('perangkat', function (Blueprint $table): void {
            $table->bigIncrements('id_perangkat');
            $table->unsignedBigInteger('id_lokasi');
            $table->string('kode_perangkat', 20);
            $table->string('posisi_node', 50);
            $table->string('mikrokontroler', 30);
            $table->string('api_token');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 20);
            $table->dateTime('heartbeat_terakhir')->nullable();
            $table->timestamps();
        });

        Schema::create('sensor', function (Blueprint $table): void {
            $table->bigIncrements('id_sensor');
            $table->unsignedBigInteger('id_perangkat');
            $table->string('jenis_sensor', 30);
            $table->string('tipe_model', 50)->nullable();
            $table->integer('sensitivitas_lsb_per_g')->nullable();
            $table->string('status', 20);
            $table->dateTime('status_diperbarui_pada')->nullable();
            $table->string('keterangan_error')->nullable();
            $table->timestamps();
        });

        Schema::create('level_getaran', function (Blueprint $table): void {
            $table->bigIncrements('id_level');
            $table->unsignedBigInteger('id_perangkat');
            $table->smallInteger('akselerasi_x')->nullable();
            $table->smallInteger('akselerasi_y')->nullable();
            $table->smallInteger('akselerasi_z')->nullable();
            $table->decimal('nilai_getaran_g', 6, 3);
            $table->decimal('nilai_magnitudo', 8, 3);
            $table->boolean('status_sw420');
            $table->string('kategori_level', 20);
            $table->dateTime('dibuat_pada');
        });

        Schema::create('deteksi', function (Blueprint $table): void {
            $table->bigIncrements('id_deteksi');
            $table->unsignedBigInteger('id_perangkat_a');
            $table->unsignedBigInteger('id_perangkat_b')->nullable();
            $table->dateTime('waktu_node_a');
            $table->dateTime('waktu_node_b')->nullable();
            $table->unsignedInteger('jeda_waktu_node')->nullable();
            $table->boolean('status_konsensus');
            $table->string('jenis', 30);
            $table->string('tingkat_keparahan', 20);
            $table->decimal('puncak_getaran_g', 6, 3)->nullable();
            $table->string('deskripsi')->nullable();
            $table->dateTime('berakhir_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('log_alarm', function (Blueprint $table): void {
            $table->bigIncrements('id_log');
            $table->unsignedBigInteger('id_deteksi');
            $table->dateTime('waktu_alarm');
            $table->string('jenis_alarm', 30);
            $table->string('status_alarm', 30);
            $table->unsignedInteger('durasi_detik')->nullable();
            $table->string('catatan', 500)->nullable();
            $table->unsignedBigInteger('id_petugas')->nullable();
            $table->timestamps();
        });

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_single_node_trigger_is_local_disturbance_and_reports_gangguan(): void
    {
        $this->freezeTime();
        $devices = $this->createDevices();

        $this->postJson('/api/telemetri/kirim', $this->telemetry(
            node1Vibration: false,
            node2Vibration: true,
            consensus: false,
        ), ['X-Device-Token' => 'node-a-token'])
            ->assertCreated()
            ->assertJsonPath('data.trigger_detected', true)
            ->assertJsonPath('data.deteksi.jenis', 'gangguan_lokal')
            ->assertJsonPath('data.deteksi.status_konsensus', false);

        $this->assertDatabaseHas('deteksi', [
            'id_perangkat_a' => $devices['node2'],
            'id_perangkat_b' => null,
            'jenis' => 'gangguan_lokal',
        ]);
        $this->assertDatabaseCount('log_alarm', 0);

        $this->getJson('/api/status-wilayah')
            ->assertOk()
            ->assertJsonPath('data.status_wilayah', 'GANGGUAN');
    }

    public function test_two_node_consensus_remains_an_earthquake_and_reports_bahaya(): void
    {
        $this->freezeTime();
        $this->createDevices();

        $this->postJson('/api/telemetri/kirim', $this->telemetry(
            node1Vibration: true,
            node2Vibration: true,
            consensus: true,
        ), ['X-Device-Token' => 'node-a-token'])
            ->assertCreated()
            ->assertJsonPath('data.deteksi.jenis', 'gempa')
            ->assertJsonPath('data.deteksi.status_konsensus', true);

        $this->assertDatabaseCount('deteksi', 1);
        $this->assertDatabaseCount('log_alarm', 1);
        $this->getJson('/api/status-wilayah')
            ->assertOk()
            ->assertJsonPath('data.status_wilayah', 'BAHAYA');
    }

    public function test_local_disturbance_is_promoted_when_both_nodes_reach_consensus(): void
    {
        $this->freezeTime();
        $this->createDevices();

        $this->postJson('/api/telemetri/kirim', $this->telemetry(
            node1Vibration: true,
            node2Vibration: false,
            consensus: false,
        ), ['X-Device-Token' => 'node-a-token'])->assertCreated();

        $this->postJson('/api/telemetri/kirim', $this->telemetry(
            node1Vibration: true,
            node2Vibration: true,
            consensus: true,
        ), ['X-Device-Token' => 'node-a-token'])
            ->assertCreated()
            ->assertJsonPath('data.deteksi.jenis', 'gempa')
            ->assertJsonPath('data.deteksi.status_konsensus', true);

        $this->assertDatabaseCount('deteksi', 1);
        $this->assertDatabaseCount('log_alarm', 1);
        $this->getJson('/api/status-wilayah')
            ->assertOk()
            ->assertJsonPath('data.status_wilayah', 'BAHAYA');
    }

    /**
     * @return array{node1: int, node2: int}
     */
    private function createDevices(): array
    {
        $node1Id = $this->createDevice('Node A Diagonal 1', 'NODE-A', 'node-a-token');
        $node2Id = $this->createDevice('Node B Diagonal 2', 'NODE-B', 'node-b-token');

        return ['node1' => $node1Id, 'node2' => $node2Id];
    }

    private function createDevice(string $position, string $code, string $token): int
    {
        return (int) DB::table('perangkat')->insertGetId([
            'id_lokasi' => 1,
            'kode_perangkat' => $code,
            'posisi_node' => $position,
            'mikrokontroler' => 'ESP32',
            'api_token' => $token,
            'status' => 'online',
            'heartbeat_terakhir' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_perangkat');
    }

    /**
     * @return array<string, bool|float>
     */
    private function telemetry(bool $node1Vibration, bool $node2Vibration, bool $consensus): array
    {
        return [
            'node1_vib' => $node1Vibration,
            'node2_vib' => $node2Vibration,
            'consensus' => $consensus,
            'node1_pure_g' => 0.1,
            'node1_mag' => 0.1,
            'node2_pure_g' => 0.1,
            'node2_mag' => 0.1,
            'node2_online' => true,
        ];
    }
}

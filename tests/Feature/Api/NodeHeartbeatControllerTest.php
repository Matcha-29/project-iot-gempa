<?php

namespace Tests\Feature\Api;

use App\Services\DeviceHeartbeat;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NodeHeartbeatControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();

        parent::tearDown();
    }

    public function test_node_two_heartbeat_keeps_node_two_online_and_expires_only_stale_node_one(): void
    {
        $this->freezeTime();
        $locationId = $this->createLocation();
        $nodeAId = $this->createDevice($locationId, 'Node A Diagonal 1', 'node-a-token', 'TST-001');
        $nodeBId = $this->createDevice($locationId, 'Node B Diagonal 2', 'node-b-token', 'TST-002');

        $response = $this->postJson('/api/telemetri/heartbeat', [], [
            'X-Device-Token' => 'node-b-token',
        ]);
        DeviceHeartbeat::expireStaleDevices();

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.kode_perangkat', 'TST-002')
            ->assertJsonPath('data.status', 'online');
        $this->assertDatabaseHas('perangkat', [
            'id_perangkat' => $nodeAId,
            'status' => 'offline',
        ]);
        $this->assertDatabaseHas('perangkat', [
            'id_perangkat' => $nodeBId,
            'status' => 'online',
        ]);
    }

    public function test_node_a_token_cannot_mark_node_two_online(): void
    {
        $locationId = $this->createLocation();
        $this->createDevice($locationId, 'Node A Diagonal 1', 'node-a-token', 'TST-001');
        $nodeBId = $this->createDevice(
            $locationId,
            'Node B Diagonal 2',
            'node-b-token',
            'TST-002',
            'offline',
            false,
        );

        $response = $this->postJson('/api/telemetri/heartbeat', [], [
            'X-Device-Token' => 'node-a-token',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false);
        $this->assertDatabaseHas('perangkat', [
            'id_perangkat' => $nodeBId,
            'status' => 'offline',
            'heartbeat_terakhir' => null,
        ]);
    }

    private function createLocation(): int
    {
        return (int) DB::table('lokasi')->insertGetId([
            'nama_lokasi' => 'Heartbeat Test Location',
            'alamat' => 'Test address',
            'latitude' => -8.0,
            'longitude' => 113.0,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_lokasi');
    }

    private function createDevice(
        int $locationId,
        string $position,
        string $token,
        string $code,
        string $status = 'online',
        bool $staleHeartbeat = true,
    ): int
    {
        return (int) DB::table('perangkat')->insertGetId([
            'id_lokasi' => $locationId,
            'posisi_node' => $position,
            'kode_perangkat' => $code,
            'mikrokontroler' => 'ESP32',
            'api_token' => $token,
            'status' => $status,
            'heartbeat_terakhir' => $staleHeartbeat ? now()->subSeconds(30) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_perangkat');
    }
}

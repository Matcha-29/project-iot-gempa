<?php

namespace App\Console\Commands;

use App\Models\Lokasi;
use App\Models\Perangkat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProvisionIotNodes extends Command
{
    protected $signature = 'iot:provision-nodes
        {--location-id= : Use an existing lokasi.id_lokasi}
        {--location-name= : Name for a new monitoring location}
        {--address= : Address for a new monitoring location}
        {--latitude= : Latitude for a new monitoring location}
        {--longitude= : Longitude for a new monitoring location}
        {--node-a=ND-001 : Node A device code}
        {--node-b=ND-002 : Node B device code}
        {--show-node-b-token : Display the Node B token once for firmware setup}';

    protected $description = 'Create the Node A and Node B device records required by ESP32 ingestion';

    public function handle(): int
    {
        $locationId = $this->option('location-id');
        if ($locationId) {
            $location = Lokasi::find($locationId);
            if (! $location) {
                $this->error("Location {$locationId} was not found.");

                return self::FAILURE;
            }
        } else {
            $name = $this->option('location-name');
            $address = $this->option('address');
            $latitude = $this->option('latitude');
            $longitude = $this->option('longitude');

            if (! $name || ! $address || ! is_numeric($latitude) || ! is_numeric($longitude)) {
                $this->error('Provide --location-id, or provide --location-name, --address, --latitude, and --longitude.');

                return self::FAILURE;
            }

            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                $this->error('Latitude or longitude is outside its valid range.');

                return self::FAILURE;
            }

            $location = Lokasi::create([
                'nama_lokasi' => $name,
                'alamat' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        }

        [$masterToken, $nodeBToken] = DB::transaction(function () use ($location) {
            $master = $this->deviceForLocation(
                $location,
                'Node A Diagonal 1',
                $this->option('node-a'),
            );
            $nodeB = $this->deviceForLocation(
                $location,
                'Node B Diagonal 2',
                $this->option('node-b'),
            );

            return [$master->api_token, $nodeB->api_token];
        });

        $this->info("Location: {$location->nama_lokasi} (ID {$location->id_lokasi})");
        if ($this->option('show-node-b-token')) {
            $this->info('Node B API token (copy to the Node 2 Arduino sketch; shown only by this command):');
            $this->line($nodeBToken);
        } else {
            $this->info('Node A API token (copy to the Master sketch; shown only by this command):');
            $this->line($masterToken);
        }

        return self::SUCCESS;
    }

    private function deviceForLocation(Lokasi $location, string $position, string $code): Perangkat
    {
        $device = Perangkat::firstOrNew([
            'id_lokasi' => $location->getKey(),
            'posisi_node' => $position,
        ]);

        if (! $device->exists) {
            $device->kode_perangkat = $code;
            $device->mikrokontroler = 'ESP32';
            $device->status = 'offline';
        }

        if (! $device->api_token) {
            $device->api_token = Str::random(64);
        }

        $device->save();

        return $device;
    }
}

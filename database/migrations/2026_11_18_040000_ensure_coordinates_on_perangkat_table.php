<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            if (!Schema::hasColumn('perangkat', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('api_token');
            }
            if (!Schema::hasColumn('perangkat', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perangkat', function (Blueprint $table) {
            // Keep safe without dropping if needed by system
        });
    }
};

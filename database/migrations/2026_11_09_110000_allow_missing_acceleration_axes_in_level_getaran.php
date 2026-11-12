<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_getaran', function (Blueprint $table) {
            $table->smallInteger('akselerasi_x')->nullable()->change();
            $table->smallInteger('akselerasi_y')->nullable()->change();
            $table->smallInteger('akselerasi_z')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('level_getaran')->whereNull('akselerasi_x')->update(['akselerasi_x' => 0]);
        DB::table('level_getaran')->whereNull('akselerasi_y')->update(['akselerasi_y' => 0]);
        DB::table('level_getaran')->whereNull('akselerasi_z')->update(['akselerasi_z' => 0]);

        Schema::table('level_getaran', function (Blueprint $table) {
            $table->smallInteger('akselerasi_x')->nullable(false)->change();
            $table->smallInteger('akselerasi_y')->nullable(false)->change();
            $table->smallInteger('akselerasi_z')->nullable(false)->change();
        });
    }
};

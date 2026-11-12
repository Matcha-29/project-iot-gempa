<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('level_getaran', function (Blueprint $table) {
            $table->decimal('nilai_magnitudo', 8, 3)->nullable()->after('nilai_getaran_g');
        });
    }

    public function down(): void
    {
        Schema::table('level_getaran', function (Blueprint $table) {
            $table->dropColumn('nilai_magnitudo');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_telemetri_gabungan");

        DB::statement("
            CREATE VIEW view_telemetri_gabungan AS
            SELECT
                slots.waktu_slot,

                n1.nilai_getaran_g   AS getaran_n1,
                n1.nilai_magnitudo   AS magnitudo_n1,
                n1.status_sw420      AS sw420_n1,
                n1.kategori_level    AS kategori_n1,

                n2.nilai_getaran_g   AS getaran_n2,
                n2.nilai_magnitudo   AS magnitudo_n2,
                n2.status_sw420      AS sw420_n2,
                n2.kategori_level    AS kategori_n2,

                CASE
                    /* kedua node non-normal / sw420 aktif → ambil level tertinggi */
                    WHEN (COALESCE(n1.kategori_level,'normal') != 'normal' OR COALESCE(n1.status_sw420,0) = 1)
                     AND (COALESCE(n2.kategori_level,'normal') != 'normal' OR COALESCE(n2.status_sw420,0) = 1)
                    THEN CASE
                            WHEN COALESCE(n1.kategori_level,'normal') = 'bahaya'
                              OR COALESCE(n2.kategori_level,'normal') = 'bahaya'
                            THEN 'Bahaya'
                            WHEN COALESCE(n1.kategori_level,'normal') = 'waspada'
                              OR COALESCE(n2.kategori_level,'normal') = 'waspada'
                            THEN 'Waspada'
                            ELSE 'Waspada'
                         END

                    /* hanya 1 node non-normal */
                    WHEN (COALESCE(n1.kategori_level,'normal') != 'normal' OR COALESCE(n1.status_sw420,0) = 1)
                      OR (COALESCE(n2.kategori_level,'normal') != 'normal' OR COALESCE(n2.status_sw420,0) = 1)
                    THEN 'Gangguan'

                    ELSE 'Normal'
                END AS kategori_gabungan

            FROM (
                /* distinct 5-second slots from either node */
                SELECT DISTINCT FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(dibuat_pada)/5)*5) AS waktu_slot
                FROM level_getaran
            ) slots

            LEFT JOIN (
                SELECT FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(lg.dibuat_pada)/5)*5) AS slot,
                       lg.nilai_getaran_g,
                       lg.nilai_magnitudo,
                       lg.status_sw420,
                       lg.kategori_level,
                       ROW_NUMBER() OVER (
                           PARTITION BY FLOOR(UNIX_TIMESTAMP(lg.dibuat_pada)/5)
                           ORDER BY lg.dibuat_pada DESC, lg.id_level DESC
                       ) AS rn
                FROM level_getaran lg
                WHERE lg.id_perangkat = (SELECT id_perangkat FROM perangkat WHERE kode_perangkat = 'ND-001' LIMIT 1)
            ) n1 ON n1.slot = slots.waktu_slot AND n1.rn = 1

            LEFT JOIN (
                SELECT FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(lg.dibuat_pada)/5)*5) AS slot,
                       lg.nilai_getaran_g,
                       lg.nilai_magnitudo,
                       lg.status_sw420,
                       lg.kategori_level,
                       ROW_NUMBER() OVER (
                           PARTITION BY FLOOR(UNIX_TIMESTAMP(lg.dibuat_pada)/5)
                           ORDER BY lg.dibuat_pada DESC, lg.id_level DESC
                       ) AS rn
                FROM level_getaran lg
                WHERE lg.id_perangkat = (SELECT id_perangkat FROM perangkat WHERE kode_perangkat = 'ND-002' LIMIT 1)
            ) n2 ON n2.slot = slots.waktu_slot AND n2.rn = 1
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS view_telemetri_gabungan");
    }
};

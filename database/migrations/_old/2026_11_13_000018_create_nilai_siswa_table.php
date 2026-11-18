<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_siswa', function (Blueprint $table) {
            $table->integer('nilai_id', true)->primary();
            $table->integer('siswa_id');
            $table->integer('mapel_id');
            $table->integer('tahun_ajaran_id');
            $table->decimal('nilai_tugas', 5, 2)->nullable()->default(0);
            $table->decimal('nilai_uts', 5, 2)->nullable()->default(0);
            $table->decimal('nilai_uas', 5, 2)->nullable()->default(0);
            $table->index('siswa_id');
            $table->index('mapel_id');
            $table->index('tahun_ajaran_id');
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('mapel_id')->references('mapel_id')->on('mata_pelajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('tahun_ajaran_id')->references('tahun_ajaran_id')->on('tahun_ajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_siswa');
    }
};

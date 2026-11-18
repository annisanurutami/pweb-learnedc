<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->integer('jadwal_id', true)->primary();
            $table->integer('kelas_id');
            $table->integer('mapel_id');
            $table->integer('guru_id');
            $table->integer('tahun_ajaran_id');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->index('kelas_id');
            $table->index('mapel_id');
            $table->index('guru_id');
            $table->index('tahun_ajaran_id');
            $table->foreign('kelas_id')->references('kelas_id')->on('kelas')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('mapel_id')->references('mapel_id')->on('mata_pelajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('guru_id')->references('guru_id')->on('guru')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('tahun_ajaran_id')->references('tahun_ajaran_id')->on('tahun_ajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
    }
};

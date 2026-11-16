<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensi_siswa', function (Blueprint $table) {
            $table->integer('presensi_id', true)->primary();
            $table->integer('jadwal_id');
            $table->integer('siswa_id');
            $table->date('tanggal');
            $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpha']);
            $table->unique(['jadwal_id', 'siswa_id', 'tanggal'], 'uniq_presensi_jadwal_siswa_tanggal');
            $table->index('siswa_id');
            $table->foreign('jadwal_id')->references('jadwal_id')->on('jadwal_pelajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_siswa');
    }
};

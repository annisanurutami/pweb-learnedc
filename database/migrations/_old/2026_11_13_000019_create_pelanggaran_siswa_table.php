<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran_siswa', function (Blueprint $table) {
            $table->integer('pelanggaran_id', true)->primary();
            $table->integer('siswa_id');
            $table->integer('guru_piket_id')->nullable();
            $table->integer('jenis_pelanggaran_id')->nullable();
            $table->string('nama_pelanggaran', 150);
            $table->integer('poin')->nullable()->default(0);
            $table->date('tanggal');
            $table->index('siswa_id');
            $table->index('guru_piket_id');
            $table->index('jenis_pelanggaran_id', 'pelanggaran_siswa_ibfk_3');
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('guru_piket_id')->references('guru_id')->on('guru')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('jenis_pelanggaran_id')->references('jenis_pelanggaran_id')->on('jenis_pelanggaran')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_siswa');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestasi_murid', function (Blueprint $table) {
            $table->integer('prestasi_id', true)->primary();
            $table->integer('siswa_id');
            $table->string('nama_prestasi', 150);
            $table->enum('tingkat', ['Sekolah', 'Kota', 'Provinsi', 'Nasional']);
            $table->date('tanggal');
            $table->index('siswa_id');
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi_murid');
    }
};

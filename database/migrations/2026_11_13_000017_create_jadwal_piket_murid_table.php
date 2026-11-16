<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_piket_murid', function (Blueprint $table) {
            $table->integer('piket_murid_id', true)->primary();
            $table->integer('siswa_id');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
            $table->index('siswa_id');
            $table->foreign('siswa_id')->references('siswa_id')->on('siswa')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket_murid');
    }
};

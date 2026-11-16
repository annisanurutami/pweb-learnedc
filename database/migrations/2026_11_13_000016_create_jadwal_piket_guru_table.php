<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_piket_guru', function (Blueprint $table) {
            $table->integer('piket_guru_id', true)->primary();
            $table->integer('guru_id');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']);
            $table->string('lokasi_piket', 50)->nullable();
            $table->index('guru_id');
            $table->foreign('guru_id')->references('guru_id')->on('guru')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket_guru');
    }
};

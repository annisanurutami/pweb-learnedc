<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal_piket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->unsignedTinyInteger('hari_id');
            $table->foreignId('semester_id')->constrained('semester');
            $table->timestamps();

            $table->foreign('hari_id')->references('id')->on('hari');
            $table->unique(['semester_id', 'siswa_id', 'hari_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_piket');
    }
};

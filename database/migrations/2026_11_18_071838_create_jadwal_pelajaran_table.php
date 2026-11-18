<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_id')->constrained('guru_mapel')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semester');
            $table->unsignedTinyInteger('hari_id');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            $table->foreign('hari_id')->references('id')->on('hari');
            $table->unique(['guru_mapel_id', 'semester_id', 'hari_id', 'jam_mulai'], 'uniq_jadwal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
    }
};

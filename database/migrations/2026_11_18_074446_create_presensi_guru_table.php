<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('presensi_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->constrained('jadwal_pelajaran');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->enum('status', ['H', 'S', 'I', 'A']);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['guru_id', 'jadwal_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi_guru');
    }
};

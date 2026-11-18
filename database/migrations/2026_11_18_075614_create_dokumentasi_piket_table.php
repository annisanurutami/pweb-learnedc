<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('dokumentasi_piket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_piket_id')->constrained('jadwal_piket')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('foto_path')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['jadwal_piket_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumentasi_piket');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengerjaan_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ujian_id')->constrained('ujian')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->dateTime('mulai_at')->nullable();
            $table->dateTime('selesai_at')->nullable();
            $table->decimal('nilai_akhir', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['ujian_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengerjaan_ujian');
    }
};

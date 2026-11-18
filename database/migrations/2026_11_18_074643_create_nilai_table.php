<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_mapel_id')->constrained('guru_mapel');
            $table->foreignId('semester_id')->constrained('semester');
            $table->enum('jenis', ['tugas_harian', 'UH', 'UTS', 'UAS']);
            $table->decimal('nilai', 5, 2);
            $table->unsignedBigInteger('sumber_id')->nullable();
            $table->timestamps();

            $table->unique(
                ['siswa_id', 'guru_mapel_id', 'semester_id', 'jenis', 'sumber_id'],
                'uniq_nilai'
            );
            $table->index(['guru_mapel_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jawaban_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengerjaan_ujian_id')->constrained('pengerjaan_ujian')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal_ujian')->cascadeOnDelete();
            $table->text('jawaban')->nullable();
            $table->decimal('skor', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['pengerjaan_ujian_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawaban_ujian');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_id')->constrained('guru_mapel')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semester');
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->string('file_path')->nullable();
            $table->dateTime('deadline');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};

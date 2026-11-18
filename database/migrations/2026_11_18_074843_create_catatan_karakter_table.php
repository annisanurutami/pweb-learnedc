<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catatan_karakter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('wali_kelas_id')->constrained('guru');
            $table->foreignId('semester_id')->constrained('semester');
            $table->string('aspek', 100);
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'semester_id', 'aspek']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_karakter');
    }
};

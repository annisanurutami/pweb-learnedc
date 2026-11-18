<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('role_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semester');
            $table->enum('role', ['ketua', 'wakil', 'sekretaris', 'bendahara']);
            $table->timestamps();

            $table->unique(['kelas_id', 'semester_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_siswa');
    }
};

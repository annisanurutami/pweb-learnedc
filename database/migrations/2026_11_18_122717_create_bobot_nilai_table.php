<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bobot_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->unique()->constrained('semester')->cascadeOnDelete();
            $table->unsignedTinyInteger('bobot_tugas')->default(40);
            $table->unsignedTinyInteger('bobot_ujian')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bobot_nilai');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->integer('kelas_id', true)->primary();
            $table->string('nama_kelas', 50);
            $table->integer('tingkat_kelas_id');
            $table->string('jurusan', 50)->nullable();
            $table->integer('wali_kelas_id')->nullable();
            $table->index('wali_kelas_id');
            $table->index('tingkat_kelas_id');
            $table->foreign('wali_kelas_id')->references('guru_id')->on('guru')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('tingkat_kelas_id')->references('tingkat_kelas_id')->on('tingkat_kelas')
                ->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};

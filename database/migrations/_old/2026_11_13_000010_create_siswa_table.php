<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->integer('siswa_id', true)->primary();
            $table->string('nisn', 10)->unique();
            $table->string('nama_siswa', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->integer('kelas_id')->nullable();
            $table->integer('tahun_ajaran_id')->nullable();
            $table->index('kelas_id');
            $table->index('tahun_ajaran_id');
            $table->foreign('kelas_id')->references('kelas_id')->on('kelas')
                ->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('tahun_ajaran_id')->references('tahun_ajaran_id')->on('tahun_ajaran')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};

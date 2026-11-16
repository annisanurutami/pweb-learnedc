<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->integer('tahun_ajaran_id', true)->primary();
            $table->string('tahun_ajaran', 10);
            $table->enum('semester', ['Ganjil', 'Genap']);
            $table->boolean('status_aktif')->nullable()->default(true);
            $table->unique(['tahun_ajaran', 'semester'], 'uniq_tahun_ajaran_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_ajaran');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tingkat_kelas', function (Blueprint $table) {
            $table->integer('tingkat_kelas_id', true)->primary();
            $table->string('nama_tingkat', 30)->unique();
            $table->integer('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tingkat_kelas');
    }
};

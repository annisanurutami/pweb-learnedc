<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurusan', function (Blueprint $table) {
            $table->integer('jurusan_id', true)->primary();
            $table->string('nama_jurusan', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurusan');
    }
};

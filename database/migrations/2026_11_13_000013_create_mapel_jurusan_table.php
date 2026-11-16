<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel_jurusan', function (Blueprint $table) {
            $table->integer('mapel_id');
            $table->integer('jurusan_id');
            $table->primary(['mapel_id', 'jurusan_id']);
            $table->index('jurusan_id');
            $table->foreign('mapel_id')->references('mapel_id')->on('mata_pelajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('jurusan_id')->references('jurusan_id')->on('jurusan')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel_jurusan');
    }
};

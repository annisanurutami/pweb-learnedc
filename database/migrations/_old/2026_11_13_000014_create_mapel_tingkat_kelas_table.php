<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel_tingkat_kelas', function (Blueprint $table) {
            $table->integer('mapel_id');
            $table->integer('tingkat_kelas_id');
            $table->primary(['mapel_id', 'tingkat_kelas_id']);
            $table->index('tingkat_kelas_id');
            $table->foreign('mapel_id')->references('mapel_id')->on('mata_pelajaran')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('tingkat_kelas_id')->references('tingkat_kelas_id')->on('tingkat_kelas')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel_tingkat_kelas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->integer('pengumuman_id', true)->primary();
            $table->string('judul', 150);
            $table->text('isi_pengumuman');
            $table->enum('audiens', ['Semua', 'Guru'])->default('Semua');
            $table->dateTime('tanggal_post')->nullable()->useCurrent();
            $table->integer('admin_id')->nullable();
            $table->index('admin_id');
            $table->foreign('admin_id')->references('admin_id')->on('admin')
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};

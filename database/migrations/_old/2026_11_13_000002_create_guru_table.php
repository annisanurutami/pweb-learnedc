<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->integer('guru_id', true)->primary();
            $table->string('nip', 20)->unique();
            $table->string('nama_guru', 100);
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->set('peran', ['Petugas Perpustakaan', 'Guru Mapel', 'Guru Piket', 'Wali Kelas'])
                ->default('Guru Mapel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};

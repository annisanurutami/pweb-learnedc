<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kas_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->date('tanggal');
            $table->decimal('jumlah', 12, 2);
            $table->string('keterangan')->nullable();
            $table->string('bukti_path')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['kelas_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_pengeluaran');
    }
};

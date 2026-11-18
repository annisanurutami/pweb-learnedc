<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nilai_id')->nullable()->constrained('nilai')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('nilai_lama', 5, 2)->nullable();
            $table->decimal('nilai_baru', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_nilai');
    }
};

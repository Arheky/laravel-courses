<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();

            // 🔗 Kurs ve kullanıcı ilişkileri
            $table->foreignId('course_id')
                  ->constrained()
                  ->onDelete('cascade'); // ✅ Kurs silinirse pivot kayıtları da silinsin

            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade'); // ✅ Kullanıcı silinirse kayıtlar da silinsin

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_user');
    }
};

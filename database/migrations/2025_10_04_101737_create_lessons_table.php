<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            // 🔗 Kurs ilişkisi
            $table->foreignId('course_id')
                  ->constrained()
                  ->onDelete('cascade');

            // 📘 Ders bilgileri
            $table->string('title');                 // Ders başlığı
            $table->text('content')->nullable();     // Açıklama veya metin içeriği
            $table->string('video_url')->nullable(); // Video linki (YouTube, Vimeo, vs.)
            $table->integer('order')->default(1);    // İsteğe bağlı sıralama numarası

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

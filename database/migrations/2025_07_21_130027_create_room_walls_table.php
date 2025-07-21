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
        Schema::create('room_walls', function (Blueprint $table) {
            $table->id();

            //$table->foreignId('room_id')->references('id')->on('rooms');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->decimal('length', 7, 2); // Длина стены в метрах (например, 9999.99)
            $table->decimal('angle', 5, 2); // Угол разворота в градусах (например, 359.99)
            $table->unsignedInteger('order')->default(0); // Порядок стены в многоугольнике

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_walls');
    }
};

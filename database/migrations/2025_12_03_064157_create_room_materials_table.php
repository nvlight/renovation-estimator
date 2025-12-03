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
        Schema::create('room_materials', function (Blueprint $table) {
            $table->id();

            // вариант 1
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->constrained()->onDelete('cascade');

            // вариант 2 (классический)
            // $table->unsignedBigInteger('room_id');
            // $table->unsignedBigInteger('material_id');
            // $table->foreign('room_id')->references('id')->on('rooms')->onDelete('CASCADE');
            // $table->foreign('material_id')->references('id')->on('materials')->onDelete('CASCADE');

            $table->unsignedInteger('amount')->default(0);
            $table->decimal('sum', 12, 2)->default(0);
            $table->string('notice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_materials');
    }
};

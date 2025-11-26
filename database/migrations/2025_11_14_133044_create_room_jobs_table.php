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
        Schema::create('room_jobs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('room_id');
            $table->string('title', 111)->default('');
            $table->bigInteger('sum')->default(0);

            $table->jsonb('more_info')->nullable();

            $table->timestamps();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_jobs');
    }
};

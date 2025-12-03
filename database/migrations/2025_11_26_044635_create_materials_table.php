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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->string('title')->comment('короткое описание товара');;
            $table->string('description', 555)->nullable()->comment('длинное описание товара');
            $table->unsignedInteger('product_code')->nullable()->comment('код продукта');
            $table->string('product_url')->nullable()->comment('ссылка на товар');

            $table->decimal('price', 12, 2)->nullable()->comment('цена товара');
            $table->boolean('is_free')->default(false)->comment('флаг для бесплатного товара');

            $table->jsonb('characteristics')->nullable()->comment('характеристики товара');
            $table->json('advantages')->nullable()->comment('Преимущества продукта в формате массива');
            $table->json('packaging_info')->nullable()->comment('Информация об упаковке');

            $table->string('brand', 111)->nullable()->comment('бренд товара');
            $table->string('producing_country', 111)->nullable()->comment('страна-производитель');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};

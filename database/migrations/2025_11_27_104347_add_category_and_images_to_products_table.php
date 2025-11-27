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
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->json('images')->nullable()->after('category');
            $table->string('sku')->unique()->nullable()->after('images');
            $table->boolean('is_active')->default(true)->after('sku');
            $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            $table->integer('views')->default(0)->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category', 'images', 'sku', 'is_active', 'discount_price', 'views']);
        });
    }
};

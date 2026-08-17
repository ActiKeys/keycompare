<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('link')->unique(); // unique identifier (URL)
            $table->decimal('price', 12, 4);
            $table->string('currency', 3);
            $table->string('region', 32)->nullable()->index();
            $table->boolean('in_stock')->default(true);
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index('price');
            $table->index(['product_id', 'price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};

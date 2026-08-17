<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('link')->unique(); // unique identifier
            $table->string('name');
            $table->string('image_link')->nullable();
            $table->text('description')->nullable();
            $table->string('platform')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

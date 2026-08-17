<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            // Polymorphic relation: Product, Offer, Store, etc.
            $table->string('mediable_type');   // App\Models\Product
            $table->unsignedBigInteger('mediable_id');
            $table->string('collection', 64)->default('default'); // 'featured', 'gallery', 'icon', etc.
            $table->string('disk', 32)->default('public');
            $table->string('file_path');           // 'media/products/2026/08/foo.jpg'
            $table->string('original_name')->nullable();
            $table->string('mime_type', 64)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('source_url', 2048)->nullable(); // Original URL if downloaded
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id']);
            $table->index(['mediable_type', 'mediable_id', 'collection']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

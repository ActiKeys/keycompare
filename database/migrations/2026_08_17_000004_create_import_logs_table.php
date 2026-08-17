<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // 'manual', 'api', 'cli'
            $table->string('status'); // 'success', 'partial', 'failed'
            $table->integer('products_count')->default(0);
            $table->integer('offers_count')->default(0);
            $table->integer('products_created')->default(0);
            $table->integer('products_updated')->default(0);
            $table->integer('offers_created')->default(0);
            $table->integer('offers_updated')->default(0);
            $table->json('errors')->nullable();
            $table->json('payload')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};

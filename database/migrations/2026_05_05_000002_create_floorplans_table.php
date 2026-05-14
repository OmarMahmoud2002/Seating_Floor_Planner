<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('floorplans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);
            $table->string('unit', 20)->default('meter');
            $table->string('paper_size', 20)->default('A3');
            $table->string('orientation', 20)->default('landscape');
            $table->unsignedSmallInteger('grid_size')->default(20);
            $table->string('background_image_path')->nullable();
            $table->json('design_json')->nullable();
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('floorplans');
    }
};

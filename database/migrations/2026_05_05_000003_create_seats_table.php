<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floorplan_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('guest_id')->nullable();
            $table->string('table_key');
            $table->string('table_name')->nullable();
            $table->string('seat_key');
            $table->unsignedInteger('seat_number')->nullable();
            $table->decimal('x', 10, 2);
            $table->decimal('y', 10, 2);
            $table->decimal('rotation', 8, 2)->default(0);
            $table->string('status', 32)->default('available');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('floorplan_id');
            $table->index('guest_id');
            $table->unique(['floorplan_id', 'seat_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};

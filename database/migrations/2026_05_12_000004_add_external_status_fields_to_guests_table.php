<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('external_guest_id')->nullable()->after('organization_id');
            $table->string('status', 32)->default('registered')->after('notes');
            $table->string('gift_status', 32)->default('not_used')->after('status');
            $table->timestamp('checked_in_at')->nullable()->after('gift_status');
            $table->timestamp('gift_used_at')->nullable()->after('checked_in_at');
            $table->json('external_payload')->nullable()->after('gift_used_at');
            $table->timestamp('last_synced_at')->nullable()->after('external_payload');

            $table->unique(['organization_id', 'external_guest_id']);
            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'gift_status']);
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'external_guest_id']);
            $table->dropIndex(['event_id', 'status']);
            $table->dropIndex(['event_id', 'gift_status']);
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn([
                'external_guest_id',
                'status',
                'gift_status',
                'checked_in_at',
                'gift_used_at',
                'external_payload',
                'last_synced_at',
            ]);
        });
    }
};

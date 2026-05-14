<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('external_event_id')->nullable()->after('organization_id');
            $table->uuid('external_event_uuid')->nullable()->after('external_event_id');
            $table->string('external_status', 40)->nullable()->after('external_event_uuid');
            $table->timestamp('last_synced_at')->nullable()->after('external_status');

            $table->unique(['organization_id', 'external_event_id']);
            $table->index(['organization_id', 'event_date']);
            $table->index('external_event_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'external_event_id']);
            $table->dropIndex(['organization_id', 'event_date']);
            $table->dropIndex(['external_event_uuid']);
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn([
                'external_event_id',
                'external_event_uuid',
                'external_status',
                'last_synced_at',
            ]);
        });
    }
};

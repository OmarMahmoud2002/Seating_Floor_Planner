<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_types', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
            $table->string('key', 60)->nullable()->after('organization_id');

            $table->unique(['organization_id', 'key']);
            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::table('guest_types', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'key']);
            $table->dropIndex(['key']);
            $table->dropConstrainedForeignId('organization_id');
            $table->dropColumn('key');
        });
    }
};

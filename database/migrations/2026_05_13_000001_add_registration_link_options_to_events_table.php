<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('vip_registration_enabled')->default(false)->after('preview_enabled');
            $table->boolean('vvip_registration_enabled')->default(false)->after('vip_registration_enabled');
            $table->boolean('media_registration_enabled')->default(false)->after('vvip_registration_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'vip_registration_enabled',
                'vvip_registration_enabled',
                'media_registration_enabled',
            ]);
        });
    }
};

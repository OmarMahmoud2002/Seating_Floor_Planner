<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $fakeOrganizationIds = DB::table('organizations')
            ->where(function ($query): void {
                $query->where('email', 'like', '%@example.com')
                    ->orWhere('email', 'like', '%@example.net')
                    ->orWhere('email', 'like', '%@example.org')
                    ->orWhere('email', 'like', '%@example.test');
            })
            ->pluck('id');

        if ($fakeOrganizationIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($fakeOrganizationIds): void {
            DB::table('users')
                ->whereIn('organization_id', $fakeOrganizationIds)
                ->where('email', 'like', 'eventos-org-%@sync.local')
                ->delete();

            DB::table('guest_types')
                ->whereIn('organization_id', $fakeOrganizationIds)
                ->delete();

            DB::table('organizations')
                ->whereIn('id', $fakeOrganizationIds)
                ->delete();
        });
    }

    public function down(): void
    {
        // Demo fixture data is intentionally not restored.
    }
};

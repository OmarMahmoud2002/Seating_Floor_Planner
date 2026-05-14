<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_EXTERNAL_USER_ID = 0;

    public function up(): void
    {
        $hasLegacyData = DB::table('users')->exists()
            || DB::table('events')->exists()
            || DB::table('guests')->exists();

        if (! $hasLegacyData) {
            $this->backfillGuestTypeKeys();

            return;
        }

        $now = now();
        $organizationId = DB::table('organizations')
            ->where('external_user_id', self::DEFAULT_EXTERNAL_USER_ID)
            ->value('id');

        if (! $organizationId) {
            $organizationId = DB::table('organizations')->insertGetId([
                'external_user_id' => self::DEFAULT_EXTERNAL_USER_ID,
                'name' => 'Legacy Seating Organization',
                'metadata' => json_encode([
                    'source' => 'local_backfill',
                    'note' => 'Default organization for records created before multi-tenant support.',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('users')
            ->whereNull('organization_id')
            ->update([
                'organization_id' => $organizationId,
                'role' => 'organization_admin',
                'updated_at' => $now,
            ]);

        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId) {
            DB::table('users')
                ->where('id', $firstUserId)
                ->update([
                    'role' => 'super_admin',
                    'updated_at' => $now,
                ]);
        }

        DB::table('events')
            ->whereNull('organization_id')
            ->update([
                'organization_id' => $organizationId,
                'updated_at' => $now,
            ]);

        DB::table('guests')
            ->whereNull('organization_id')
            ->update([
                'organization_id' => $organizationId,
                'updated_at' => $now,
            ]);

        $this->backfillGuestTypeKeys();
    }

    public function down(): void
    {
        DB::table('guest_types')
            ->whereNull('organization_id')
            ->whereIn('key', array_values($this->guestTypeKeyMap()))
            ->update([
                'key' => null,
                'updated_at' => now(),
            ]);

        DB::table('guests')
            ->where('organization_id', $this->defaultOrganizationId())
            ->update(['organization_id' => null]);

        DB::table('events')
            ->where('organization_id', $this->defaultOrganizationId())
            ->update(['organization_id' => null]);

        DB::table('users')
            ->where('organization_id', $this->defaultOrganizationId())
            ->update([
                'organization_id' => null,
                'role' => 'organization_admin',
                'updated_at' => now(),
            ]);

        DB::table('organizations')
            ->where('external_user_id', self::DEFAULT_EXTERNAL_USER_ID)
            ->delete();
    }

    private function backfillGuestTypeKeys(): void
    {
        $now = now();

        foreach ($this->guestTypeKeyMap() as $name => $key) {
            DB::table('guest_types')
                ->where('name_ar', $name)
                ->whereNull('key')
                ->update([
                    'key' => $key,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function guestTypeKeyMap(): array
    {
        return [
            'عادي' => 'normal',
            'VIP' => 'vip',
            'عائلة' => 'family',
            'أصدقاء' => 'friends',
            'موظف' => 'staff',
            'إعلام' => 'media',
            'راعي' => 'sponsor',
            'ذوي احتياجات خاصة' => 'accessible',
        ];
    }

    private function defaultOrganizationId(): ?int
    {
        $id = DB::table('organizations')
            ->where('external_user_id', self::DEFAULT_EXTERNAL_USER_ID)
            ->value('id');

        return $id ? (int) $id : null;
    }
};

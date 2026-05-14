<?php

namespace Database\Seeders;

use App\Models\GuestType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuestTypeSeeder extends Seeder
{
    private const TYPES = [
        ['key' => 'normal', 'name_ar' => 'عادي', 'color' => '#64748B', 'icon' => 'user', 'sort_order' => 10],
        ['key' => 'vip', 'name_ar' => 'VIP', 'color' => '#D97706', 'icon' => 'star', 'sort_order' => 20],
        ['key' => 'vvip', 'name_ar' => 'VVIP', 'color' => '#7C3AED', 'icon' => 'gem', 'sort_order' => 30],
        ['key' => 'media', 'name_ar' => 'media', 'color' => '#0284C7', 'icon' => 'camera', 'sort_order' => 40],
    ];

    private const TYPE_ALIASES = [
        'normal' => ['normal', 'عادي'],
        'vip' => ['vip', 'VIP'],
        'vvip' => ['vvip', 'VVIP'],
        'media' => ['media', 'Media', 'MEDIA', 'إعلام'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            foreach (self::TYPES as $type) {
                GuestType::query()->updateOrCreate(
                    ['organization_id' => null, 'key' => $type['key']],
                    $type + ['organization_id' => null, 'is_default' => true]
                );
            }

            $canonicalTypes = GuestType::query()
                ->whereNull('organization_id')
                ->whereIn('key', array_column(self::TYPES, 'key'))
                ->get()
                ->keyBy('key');

            foreach ($canonicalTypes as $key => $guestType) {
                $this->mergeDuplicateTypes($guestType, self::TYPE_ALIASES[$key]);
            }

            $this->removeUnsupportedTypes(
                $canonicalTypes
                    ->pluck('id')
                    ->map(fn (int $id): int => $id)
                    ->all()
            );
        });
    }

    /**
     * @param array<int, string> $aliases
     */
    private function mergeDuplicateTypes(GuestType $canonicalType, array $aliases): void
    {
        $duplicateIds = GuestType::query()
            ->where('id', '!=', $canonicalType->id)
            ->where(function ($query) use ($canonicalType, $aliases): void {
                $query
                    ->where('key', $canonicalType->key)
                    ->orWhereIn('name_ar', $aliases);
            })
            ->pluck('id');

        if ($duplicateIds->isEmpty()) {
            return;
        }

        DB::table('guests')
            ->whereIn('guest_type_id', $duplicateIds)
            ->update([
                'guest_type_id' => $canonicalType->id,
                'updated_at' => now(),
            ]);

        GuestType::query()
            ->whereIn('id', $duplicateIds)
            ->delete();
    }

    /**
     * @param array<int, int> $canonicalIds
     */
    private function removeUnsupportedTypes(array $canonicalIds): void
    {
        $unsupportedIds = GuestType::query()
            ->whereNotIn('id', $canonicalIds)
            ->pluck('id');

        if ($unsupportedIds->isEmpty()) {
            return;
        }

        DB::table('guests')
            ->whereIn('guest_type_id', $unsupportedIds)
            ->update([
                'guest_type_id' => null,
                'updated_at' => now(),
            ]);

        GuestType::query()
            ->whereIn('id', $unsupportedIds)
            ->delete();
    }
}

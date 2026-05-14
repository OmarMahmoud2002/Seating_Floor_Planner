<?php

namespace App\Services\Guests;

use App\Imports\GuestRowsImport;
use App\Models\Event;
use App\Models\Guest;
use App\Models\GuestType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GuestImportService
{
    private const TYPE_COLORS = [
        '#4D9B97',
        '#4596CF',
        '#31719D',
        '#317C77',
        '#E7C539',
        '#A19F9E',
    ];

    /**
     * @return array<string, mixed>
     */
    public function preview(Event $event, string $path): array
    {
        return $this->analyze($event, $path);
    }

    /**
     * @return array<string, mixed>
     */
    public function import(Event $event, string $path): array
    {
        $preview = $this->analyze($event, $path);
        $imported = 0;
        $createdTypes = [];

        DB::transaction(function () use ($event, $preview, &$imported, &$createdTypes): void {
            $guestTypes = $this->guestTypeMap();
            $nextSortOrder = ((int) GuestType::query()->max('sort_order')) + 1;

            foreach ($preview['rows'] as $row) {
                if ($row['status'] !== 'valid') {
                    continue;
                }

                $guestTypeId = null;
                $typeName = $row['type'];

                if ($typeName !== '') {
                    $typeKey = $this->normalizeLookup($typeName);

                    if (! isset($guestTypes[$typeKey])) {
                        $guestType = GuestType::query()->create([
                            'name_ar' => $typeName,
                            'color' => $this->colorForType($typeName),
                            'icon' => 'user',
                            'sort_order' => $nextSortOrder++,
                            'is_default' => false,
                        ]);

                        $guestTypes[$typeKey] = $guestType;
                        $createdTypes[] = $typeName;
                    }

                    $guestTypeId = $guestTypes[$typeKey]->id;
                }

                $event->guests()->create([
                    'guest_type_id' => $guestTypeId,
                    'name' => $row['name'],
                    'phone' => $row['phone'] !== '' ? $row['phone'] : null,
                    'email' => $row['email'] !== '' ? $row['email'] : null,
                    'notes' => $row['notes'] !== '' ? $row['notes'] : null,
                ]);

                $imported++;
            }
        });

        $preview['summary']['imported_rows'] = $imported;
        $preview['summary']['created_type_names'] = array_values(array_unique($createdTypes));

        return $preview;
    }

    /**
     * @return array<string, mixed>
     */
    private function analyze(Event $event, string $path): array
    {
        $rows = $this->readRows($path);
        $guestTypes = $this->guestTypeMap();
        $existingKeys = $this->existingGuestKeys($event);
        $seenKeys = [];
        $previewRows = [];
        $newTypeNames = [];

        foreach ($rows as $index => $row) {
            $normalized = $this->normalizeRow($row);
            $errors = $this->rowErrors($normalized);
            $duplicateKey = $this->duplicateKey($normalized['name'], $normalized['phone'], $normalized['email']);
            $isDuplicate = $duplicateKey !== '' && (isset($existingKeys[$duplicateKey]) || isset($seenKeys[$duplicateKey]));

            if ($errors !== []) {
                $status = 'invalid';
            } elseif ($isDuplicate) {
                $status = 'duplicate';
            } else {
                $status = 'valid';
                $seenKeys[$duplicateKey] = true;
            }

            $typeName = $normalized['type'];
            $isNewType = $typeName !== '' && ! isset($guestTypes[$this->normalizeLookup($typeName)]);

            if ($status === 'valid' && $isNewType) {
                $newTypeNames[$this->normalizeLookup($typeName)] = $typeName;
            }

            $previewRows[] = $normalized + [
                'row_number' => $index + 2,
                'status' => $status,
                'errors' => $errors,
                'is_new_type' => $isNewType,
            ];
        }

        return [
            'rows' => $previewRows,
            'summary' => [
                'total_rows' => count($previewRows),
                'valid_rows' => count(array_filter($previewRows, fn (array $row): bool => $row['status'] === 'valid')),
                'invalid_rows' => count(array_filter($previewRows, fn (array $row): bool => $row['status'] === 'invalid')),
                'duplicate_rows' => count(array_filter($previewRows, fn (array $row): bool => $row['status'] === 'duplicate')),
                'new_type_names' => array_values($newTypeNames),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readRows(string $path): array
    {
        try {
            $sheets = Excel::toArray(new GuestRowsImport(), $path, 'local');
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'file' => 'تعذر قراءة ملف Excel. يرجى التأكد من أن الملف غير تالف ويحتوي على صف عناوين.',
            ]);
        }

        return $sheets[0] ?? [];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{name: string, phone: string, email: string, type: string, notes: string}
     */
    private function normalizeRow(array $row): array
    {
        return [
            'name' => $this->cellByAliases($row, ['name', 'guest_name', 'الاسم', 'اسم_الضيف']),
            'phone' => $this->cellByAliases($row, ['phone', 'mobile', 'phone_number', 'tel', 'telephone', 'رقم_الهاتف', 'الجوال']),
            'email' => Str::lower($this->cellByAliases($row, ['email', 'mail', 'البريد', 'البريد_الإلكتروني'])),
            'type' => $this->cellByAliases($row, ['type', 'guest_type', 'category', 'النوع', 'نوع_الضيف']),
            'notes' => $this->cellByAliases($row, ['notes', 'note', 'ملاحظات', 'ملاحظة']),
        ];
    }

    /**
     * @param array{name: string, phone: string, email: string, type: string, notes: string} $row
     * @return array<int, string>
     */
    private function rowErrors(array $row): array
    {
        $errors = [];

        if ($row['name'] === '') {
            $errors[] = 'اسم الضيف مفقود.';
        }

        if (mb_strlen($row['name']) > 255) {
            $errors[] = 'اسم الضيف أطول من الحد المسموح.';
        }

        if (mb_strlen($row['phone']) > 50) {
            $errors[] = 'رقم الهاتف أطول من الحد المسموح.';
        }

        if ($row['email'] !== '' && ! filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'البريد الإلكتروني غير صحيح.';
        }

        if (mb_strlen($row['email']) > 255) {
            $errors[] = 'البريد الإلكتروني أطول من الحد المسموح.';
        }

        if (mb_strlen($row['type']) > 255) {
            $errors[] = 'نوع الضيف أطول من الحد المسموح.';
        }

        if (mb_strlen($row['notes']) > 2000) {
            $errors[] = 'الملاحظات أطول من الحد المسموح.';
        }

        return $errors;
    }

    /**
     * @return array<string, GuestType>
     */
    private function guestTypeMap(): array
    {
        return GuestType::query()
            ->get()
            ->mapWithKeys(fn (GuestType $guestType): array => [
                $this->normalizeLookup($guestType->name_ar) => $guestType,
            ])
            ->all();
    }

    /**
     * @return array<string, bool>
     */
    private function existingGuestKeys(Event $event): array
    {
        return $event->guests()
            ->get(['name', 'phone', 'email'])
            ->mapWithKeys(fn (Guest $guest): array => [
                $this->duplicateKey($guest->name, (string) $guest->phone, (string) $guest->email) => true,
            ])
            ->filter(fn (bool $value, string $key): bool => $key !== '')
            ->all();
    }

    private function duplicateKey(string $name, string $phone, string $email): string
    {
        if ($email !== '') {
            return 'email:'.Str::lower($email);
        }

        if ($phone !== '') {
            return 'phone:'.preg_replace('/\s+/', '', $phone);
        }

        if ($name !== '') {
            return 'name:'.$this->normalizeLookup($name);
        }

        return '';
    }

    private function cleanCell(mixed $value): string
    {
        return Str::of((string) $value)->squish()->trim()->toString();
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $aliases
     */
    private function cellByAliases(array $row, array $aliases): string
    {
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $row)) {
                return $this->cleanCell($row[$alias]);
            }
        }

        return '';
    }

    private function normalizeLookup(string $value): string
    {
        return Str::lower($this->cleanCell($value));
    }

    private function colorForType(string $typeName): string
    {
        return self::TYPE_COLORS[abs(crc32($typeName)) % count(self::TYPE_COLORS)];
    }
}

<?php

namespace App\Exports;

use App\Models\Event;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuestListExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Event $event)
    {
    }

    /**
     * @return Collection<int, \App\Models\Guest>
     */
    public function collection(): Collection
    {
        return $this->event->guests()
            ->with(['guestType', 'seats.floorplan'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'اسم الضيف',
            'نوع الضيف',
            'الهاتف',
            'البريد',
            'المخطط',
            'الطاولة',
            'المقعد',
            'ملاحظات',
        ];
    }

    /**
     * @param \App\Models\Guest $guest
     * @return array<int, string>
     */
    public function map($guest): array
    {
        $seat = $guest->seats->first();

        return [
            $guest->name,
            $guest->guestType?->display_name_ar ?: 'بدون نوع',
            $guest->phone ?: '',
            $guest->email ?: '',
            $seat?->floorplan?->name ?: '',
            $seat?->table_name ?: $seat?->table_key ?: '',
            $seat?->seat_number ? (string) $seat->seat_number : '',
            $guest->notes ?: '',
        ];
    }
}

<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuestRowsImport implements ToArray, WithHeadingRow
{
    /**
     * @param array<int, array<string, mixed>> $array
     */
    public function array(array $array)
    {
        //
    }
}

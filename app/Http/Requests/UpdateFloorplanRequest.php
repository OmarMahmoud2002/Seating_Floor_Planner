<?php

namespace App\Http\Requests;

class UpdateFloorplanRequest extends StoreFloorplanRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_background_image' => ['nullable', 'boolean'],
        ]);
    }
}

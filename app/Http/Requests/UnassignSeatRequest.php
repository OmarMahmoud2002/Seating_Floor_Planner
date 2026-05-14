<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnassignSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'seat_key' => ['required', 'string', 'max:160'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'seat_key.required' => 'اختر مقعدا صحيحا.',
        ];
    }
}

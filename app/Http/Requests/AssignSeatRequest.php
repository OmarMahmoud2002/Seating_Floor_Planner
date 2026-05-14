<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guest_id' => ['required', Rule::exists('guests', 'id')],
            'seat_key' => ['required', 'string', 'max:160'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guest_id.required' => 'اختر ضيفا للتجليس.',
            'guest_id.exists' => 'الضيف المحدد غير صحيح.',
            'seat_key.required' => 'اختر مقعدا صحيحا.',
        ];
    }
}

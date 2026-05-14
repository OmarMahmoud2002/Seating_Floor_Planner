<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestTypeRequest extends FormRequest
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
            'name_ar' => ['required', 'string', 'max:120'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_ar.required' => 'اسم نوع الضيف مطلوب.',
            'name_ar.max' => 'اسم نوع الضيف طويل جدا.',
            'color.required' => 'لون نوع الضيف مطلوب.',
            'color.regex' => 'اللون يجب أن يكون بصيغة HEX مثل #4D9B97.',
            'icon.max' => 'اسم الأيقونة طويل جدا.',
            'sort_order.integer' => 'ترتيب العرض يجب أن يكون رقما.',
            'sort_order.max' => 'ترتيب العرض كبير جدا.',
        ];
    }
}

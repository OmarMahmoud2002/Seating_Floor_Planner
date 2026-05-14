<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'preview_enabled' => ['nullable', 'boolean'],
            'vip_registration_enabled' => ['nullable', 'boolean'],
            'vvip_registration_enabled' => ['nullable', 'boolean'],
            'media_registration_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الحدث مطلوب.',
            'name.max' => 'اسم الحدث طويل جدًا.',
            'event_date.date' => 'تاريخ الحدث غير صحيح.',
            'location.max' => 'الموقع طويل جدًا.',
            'description.max' => 'الوصف طويل جدًا.',
        ];
    }

}

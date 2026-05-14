<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestRequest extends FormRequest
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
            'guest_type_id' => ['nullable', Rule::exists('guest_types', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الضيف مطلوب.',
            'name.max' => 'اسم الضيف طويل جدا.',
            'guest_type_id.exists' => 'نوع الضيف المحدد غير صحيح.',
            'email.email' => 'البريد الإلكتروني غير صحيح.',
            'email.max' => 'البريد الإلكتروني طويل جدا.',
            'phone.max' => 'رقم الهاتف طويل جدا.',
            'notes.max' => 'الملاحظات طويلة جدا.',
        ];
    }
}

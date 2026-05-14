<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportGuestsRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'يرجى اختيار ملف الضيوف.',
            'file.file' => 'ملف الضيوف غير صالح.',
            'file.mimes' => 'صيغة الملف يجب أن تكون xlsx أو xls أو csv.',
            'file.max' => 'حجم ملف الضيوف يجب ألا يتجاوز 2 ميجابايت.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportFloorplanPdfRequest extends FormRequest
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
            'image_data' => [
                'required',
                'string',
                'max:6000000',
                'regex:/^data:image\/(png|jpeg);base64,[A-Za-z0-9+\/=]+$/',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'image_data.required' => 'تعذر إنشاء صورة المخطط للتصدير.',
            'image_data.max' => 'صورة المخطط كبيرة جدًا، يرجى تصغير المخطط أو تقليل حجم نافذة التصدير.',
            'image_data.regex' => 'صيغة صورة المخطط غير صالحة.',
        ];
    }
}

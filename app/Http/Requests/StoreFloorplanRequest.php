<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFloorplanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'unit' => 'meter',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'width' => ['required', 'numeric', 'min:1', 'max:10000'],
            'height' => ['required', 'numeric', 'min:1', 'max:10000'],
            'unit' => ['required', Rule::in(['meter'])],
            'paper_size' => ['required', Rule::in(['A2', 'A3', 'A4'])],
            'orientation' => ['required', Rule::in(['portrait', 'landscape'])],
            'grid_size' => ['nullable', 'integer', 'min:5', 'max:200'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم المخطط مطلوب.',
            'width.required' => 'عرض القاعة مطلوب.',
            'width.numeric' => 'عرض القاعة يجب أن يكون رقمًا.',
            'width.min' => 'عرض القاعة يجب أن يكون أكبر من صفر.',
            'height.required' => 'ارتفاع القاعة مطلوب.',
            'height.numeric' => 'ارتفاع القاعة يجب أن يكون رقمًا.',
            'height.min' => 'ارتفاع القاعة يجب أن يكون أكبر من صفر.',
            'unit.required' => 'وحدة القياس مطلوبة.',
            'unit.in' => 'وحدة القياس غير صحيحة.',
            'paper_size.required' => 'حجم الورق مطلوب.',
            'paper_size.in' => 'حجم الورق غير صحيح.',
            'orientation.required' => 'اتجاه الورق مطلوب.',
            'orientation.in' => 'اتجاه الورق غير صحيح.',
            'grid_size.integer' => 'حجم الشبكة يجب أن يكون رقمًا صحيحًا.',
            'background_image.image' => 'ملف الخلفية يجب أن يكون صورة.',
            'background_image.mimes' => 'الصورة يجب أن تكون JPG أو PNG أو WebP.',
            'background_image.max' => 'حجم الصورة يجب ألا يتجاوز 8MB.',
        ];
    }
}

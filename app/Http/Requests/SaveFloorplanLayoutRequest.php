<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveFloorplanLayoutRequest extends FormRequest
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
            'design_json' => ['required', 'array'],
            'design_json.version' => ['required', 'integer', 'min:1'],
            'design_json.elements' => ['present', 'array'],
            'design_json.elements.*.id' => ['required', 'string', 'max:120'],
            'design_json.elements.*.type' => ['required', 'string', 'max:40'],
            'design_json.elements.*.label' => ['nullable', 'string', 'max:120'],
            'design_json.elements.*.tableShape' => ['nullable', 'string', 'in:round,rectangle,square,banquet,theater'],
            'design_json.elements.*.seatCount' => ['nullable', 'integer', 'min:1', 'max:160'],
            'design_json.elements.*.theaterRows' => ['nullable', 'integer', 'min:1', 'max:160'],
            'design_json.elements.*.x' => ['nullable', 'numeric'],
            'design_json.elements.*.y' => ['nullable', 'numeric'],
            'design_json.elements.*.width' => ['nullable', 'numeric', 'min:1'],
            'design_json.elements.*.height' => ['nullable', 'numeric', 'min:1'],
            'design_json.elements.*.rotation' => ['nullable', 'numeric'],
            'design_json.elements.*.fill' => ['nullable', 'string', 'max:32'],
            'design_json.elements.*.stroke' => ['nullable', 'string', 'max:32'],
            'design_json.elements.*.opacity' => ['nullable', 'numeric', 'min:0.1', 'max:1'],
            'design_json.elements.*.seats' => ['sometimes', 'array'],
            'design_json.elements.*.seats.*.key' => ['required_with:design_json.elements.*.seats', 'string', 'max:160'],
            'design_json.elements.*.seats.*.number' => ['required_with:design_json.elements.*.seats', 'integer', 'min:1', 'max:160'],
            'design_json.elements.*.seats.*.label' => ['nullable', 'string', 'max:20'],
            'design_json.elements.*.seats.*.x' => ['required_with:design_json.elements.*.seats', 'numeric'],
            'design_json.elements.*.seats.*.y' => ['required_with:design_json.elements.*.seats', 'numeric'],
            'design_json.elements.*.seats.*.rotation' => ['nullable', 'numeric'],
            'design_json.viewport' => ['nullable', 'array'],
            'design_json.viewport.scale' => ['nullable', 'numeric', 'min:0.1', 'max:5'],
            'design_json.viewport.x' => ['nullable', 'numeric'],
            'design_json.viewport.y' => ['nullable', 'numeric'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'design_json.required' => 'بيانات المخطط مطلوبة.',
            'design_json.array' => 'صيغة بيانات المخطط غير صحيحة.',
            'design_json.version.required' => 'إصدار بيانات المخطط مطلوب.',
            'design_json.elements.present' => 'عناصر المخطط مطلوبة.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Fields are optional so the same rules serve PUT and PATCH.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes', 'required', 'string', 'email:rfc', 'max:255',
                Rule::unique(Courier::class, 'email')->ignore($this->route('courier')),
            ],
            'phone' => ['sometimes', 'required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'vehicle_type' => ['sometimes', 'required', Rule::enum(VehicleType::class)],
            'license_plate' => ['sometimes', 'nullable', 'string', 'max:15'],
            'level' => ['sometimes', 'required', 'integer', 'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

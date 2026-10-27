<?php

namespace App\Http\Requests;

use App\Enums\VehicleType;
use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique(Courier::class, 'email')],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'license_plate' => ['nullable', 'string', 'max:15'],
            'level' => ['required', 'integer', 'between:'.Courier::MIN_LEVEL.','.Courier::MAX_LEVEL],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Courier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCourierRequest extends FormRequest
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;

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
            'search' => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'regex:/^[1-5](,[1-5])*$/'],
            'sort' => ['nullable', Rule::in(['name', '-name', 'created_at', '-created_at'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
        ];
    }

    public function messages(): array
    {
        return [
            'level.regex' => 'The level must be a comma separated list of numbers between '
                .Courier::MIN_LEVEL.' and '.Courier::MAX_LEVEL.', for example 2,3.',
        ];
    }

    /**
     * @return array<int, int>
     */
    public function levels(): array
    {
        $levels = $this->validated('level');

        if ($levels === null || $levels === '') {
            return [];
        }

        return array_values(array_unique(array_map('intval', explode(',', $levels))));
    }

    /**
     * @return array{0: string, 1: string} Column and direction.
     */
    public function sortOrder(): array
    {
        $sort = $this->validated('sort') ?? 'name';

        return str_starts_with($sort, '-')
            ? [substr($sort, 1), 'desc']
            : [$sort, 'asc'];
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? self::DEFAULT_PER_PAGE);
    }
}

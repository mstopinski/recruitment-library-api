<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'author_ids' => 'sometimes|array|min:1',
            'author_ids.*' => ['integer', Rule::exists('authors', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'author_ids.*.exists' => 'Podany autor nie istnieje w bazie.',
        ];
    }
}

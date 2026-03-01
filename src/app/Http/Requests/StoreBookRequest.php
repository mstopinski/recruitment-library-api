<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author_ids' => 'required|array|min:1',
            'author_ids.*' => ['integer', Rule::exists('authors', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tytuł książki jest wymagany.',
            'author_ids.required' => 'Musisz podać przynajmniej jednego autora.',
            'author_ids.*.exists' => 'Podany autor nie istnieje w bazie.',
        ];
    }
}

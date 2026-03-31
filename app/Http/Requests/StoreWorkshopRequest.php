<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
    'title' => ['required', 'string', 'max:255'],
    'description' => ['required', 'string'],
    'starts_at' => ['required', 'date'],
    'ends_at' => ['required', 'date', 'after:starts_at'],
    'capacity' => ['required', 'integer', 'min:1'],
];
    }
}
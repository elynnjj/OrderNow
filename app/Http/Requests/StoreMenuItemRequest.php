<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                        => 'required|string|max:100',
            'description'                 => 'nullable|string|max:255',
            'price'                       => 'required|numeric|min:0',
            'is_active'                   => 'sometimes|boolean',
            'ingredients'                 => 'required|array|min:1',
            'ingredients.*.ingredient_id' => 'required|integer|exists:ingredients,id',
            'ingredients.*.quantity'      => 'required|numeric|gt:0',
        ];
    }

    public function messages(): array
    {
        return [
            'ingredients.min' => 'A menu item must have at least one ingredient.',
        ];
    }
}

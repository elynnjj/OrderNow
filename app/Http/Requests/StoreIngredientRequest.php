<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:100|unique:ingredients,name',
            'unit'          => 'required|in:g,kg,ml,l,pcs,slice',
            'current_stock' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'unit.in' => 'The unit must be one of: g, kg, ml, l, pcs, slice.',
        ];
    }

}

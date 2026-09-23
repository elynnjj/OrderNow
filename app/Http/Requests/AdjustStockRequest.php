<?php

namespace App\Http\Requests;

use App\Models\Ingredient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'change' => 'required|numeric|not_in:0',
            'reason' => 'required|in:adjustment,waste,restock',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $ingredient = $this->route('ingredient');
            if (! $ingredient instanceof Ingredient && $ingredient) {
                $ingredient = Ingredient::find($ingredient);
            }

            if ($ingredient) {
                $change = (float) $this->input('change');
                if (($ingredient->current_stock + $change) < 0) {
                    $validator->errors()->add('change', 'Adjustment would result in negative stock.');
                }
            }
        });
    }
}

<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReplaceStockReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expires_at' => ['required', 'date', 'after:now'],
            'items' => ['required', 'array', 'list', 'min:1', 'max:100'],
            'items.*' => ['required', 'array:product_id,quantity,expected_version'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.expected_version' => ['required', 'integer', 'min:1'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromotionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['draft', 'active', 'inactive'])],
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.tier' => ['required', 'integer', 'min:1'],
            'tiers.*.referral_threshold' => ['required', 'integer', 'min:1'],
            'tiers.*.reward_amount' => ['required', 'numeric', 'min:0'],
            'tiers.*.currency' => ['required', 'string', 'size:3'],
            'tiers.*.is_recurring' => ['nullable', 'boolean'],
            'tiers.*.step_increment' => ['nullable', 'integer', 'min:1'],
        ];
    }
}

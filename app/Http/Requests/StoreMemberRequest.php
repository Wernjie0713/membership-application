<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manage-members');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'terminated'])],
            'date_of_birth' => ['nullable', 'date'],
            'referral_code' => ['nullable', 'string', Rule::exists('members', 'referral_code')->where(fn ($query) => $query->whereNotNull('user_id'))],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.address_type_id' => ['required', 'exists:address_types,id'],
            'addresses.*.line_1' => ['required', 'string', 'max:255'],
            'addresses.*.line_2' => ['nullable', 'string', 'max:255'],
            'addresses.*.city' => ['required', 'string', 'max:255'],
            'addresses.*.state' => ['nullable', 'string', 'max:255'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:30'],
            'addresses.*.country' => ['required', 'string', 'max:255'],
            'addresses.*.is_primary' => ['nullable', 'boolean'],
            'addresses.*.proof_of_address' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }
}

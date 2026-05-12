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
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'phone_country_code' => ['nullable', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['nullable', 'regex:/^\d{6,15}$/'],
            'status' => ['required', Rule::in(Member::STATUSES)],
            'date_of_birth' => ['nullable', 'date'],
            'referral_code' => ['nullable', 'string', Rule::exists('members', 'referral_code')->where(fn ($query) => $query->whereNotNull('user_id'))],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.address_type_id' => ['required', 'exists:address_types,id'],
            'addresses.*.line_1' => ['required', 'string', 'max:255'],
            'addresses.*.line_2' => ['nullable', 'string', 'max:255'],
            'addresses.*.city' => ['required', 'string', 'max:255'],
            'addresses.*.state' => ['nullable', 'string', 'max:255'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:30'],
            'addresses.*.country' => ['required', 'string', 'max:255', Rule::in(config('countries', []))],
            'addresses.*.is_primary' => ['nullable', 'boolean'],
            'addresses.*.proof_of_address' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $this->validatePhoneParts($validator);
                $this->validatePrimaryAddress($validator);
                $this->validateUniqueAddressTypes($validator);
            },
        ];
    }

    protected function validatePhoneParts($validator): void
    {
        $countryCode = $this->input('phone_country_code');
        $phoneNumber = $this->input('phone_number');

        if (($countryCode && ! $phoneNumber) || (! $countryCode && $phoneNumber)) {
            $validator->errors()->add('phone', 'Both country code and phone number are required when entering a phone number.');
        }
    }

    protected function validatePrimaryAddress($validator): void
    {
        $addresses = collect($this->input('addresses', []));
        $primaryCount = $addresses->filter(fn ($address) => filter_var($address['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN))->count();

        if ($primaryCount !== 1) {
            $validator->errors()->add('addresses', 'Please select exactly one primary address.');
        }
    }

    protected function validateUniqueAddressTypes($validator): void
    {
        $typeIds = collect($this->input('addresses', []))
            ->pluck('address_type_id')
            ->filter()
            ->map(fn ($value) => (string) $value);

        if ($typeIds->count() !== $typeIds->unique()->count()) {
            $validator->errors()->add('addresses', 'Each address type can only be used once per member.');
        }
    }
}

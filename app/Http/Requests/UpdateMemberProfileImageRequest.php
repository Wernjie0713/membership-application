<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberProfileImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('complete-member-profile');
    }

    public function rules(): array
    {
        return [
            'profile_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }
}

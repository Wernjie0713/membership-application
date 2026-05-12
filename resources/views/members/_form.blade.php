@php
    $showPasswordFields = $showPasswordFields ?? ! $member->exists;
    $passwordRequired = $passwordRequired ?? $showPasswordFields;
    $showEmailField = $showEmailField ?? true;
    $readonlyEmail = $readonlyEmail ?? false;
    $showStatusField = $showStatusField ?? true;
    $memberAddresses = old('addresses', isset($member) && $member->addresses->count()
        ? $member->addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'address_type_id' => $address->address_type_id,
                'line_1' => $address->line_1,
                'line_2' => $address->line_2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'is_primary' => $address->is_primary,
            ];
        })->toArray()
        : [[
            'address_type_id' => $addressTypes->first()?->id,
            'line_1' => '',
            'line_2' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => 'Singapore',
            'is_primary' => true,
        ]]);
@endphp

<div class="grid gap-8 lg:grid-cols-3">
    <!-- Left Column: Profile Picture -->
    <div class="lg:col-span-1">
        <div class="h-full rounded-[8px] bg-white p-8 shadow-uber-card">
            <h3 class="text-xl font-bold text-uber-black">Profile Picture</h3>
            <p class="mt-2 text-sm text-body-gray">Upload a professional headshot. Maximum size 5MB.</p>
            <div class="mt-6 flex flex-col items-center">
                @if ($member->profileImage)
                    <img src="{{ Storage::url($member->profileImage->path) }}" alt="Profile" class="mb-4 h-32 w-32 rounded-full object-cover shadow-sm">
                @else
                    <div class="mb-4 flex h-32 w-32 items-center justify-center rounded-full bg-chip-gray text-body-gray">
                        <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7-7z"></path></svg>
                    </div>
                @endif
                <input id="profile_image" name="profile_image" type="file" class="block w-full cursor-pointer text-sm text-body-gray file:mr-4 file:rounded-full file:border-0 file:bg-chip-gray file:px-4 file:py-2 file:text-sm file:font-semibold file:text-uber-black hover:file:bg-hover-gray" />
                <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                @if ($member->profileImage)
                    <p class="mt-2 text-xs font-medium text-body-gray uppercase tracking-wide">Current file: {{ $member->profileImage->original_name }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Profile Details -->
    <div class="lg:col-span-2">
        <div class="h-full rounded-[8px] bg-white p-8 shadow-uber-card">
            <h3 class="text-xl font-bold text-uber-black mb-6">Profile Identity</h3>
            <div class="grid gap-6 md:grid-cols-2">
                @if ($showPasswordFields)
                    <div>
                        <x-input-label for="password" value="Login Password" />
                        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="$passwordRequired" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" value="Confirm Login Password" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="$passwordRequired" />
                    </div>
                @endif
                <div>
                    <x-input-label for="first_name" value="First Name" />
                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $member->first_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>
                <div>
                    <x-input-label for="last_name" value="Last Name" />
                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $member->last_name)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>
                @if ($showEmailField)
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full {{ $readonlyEmail ? 'bg-chip-gray text-body-gray cursor-not-allowed opacity-70' : '' }}" :value="old('email', $member->email)" :readonly="$readonlyEmail" :required="! $readonlyEmail" />
                        @if ($readonlyEmail)
                            <p class="mt-2 text-xs font-medium text-body-gray uppercase tracking-wide">Your login email is managed from the account profile page.</p>
                        @endif
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                @endif
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $member->phone)" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
                <div>
                    <x-input-label for="date_of_birth" value="Date of Birth" />
                    <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', optional($member->date_of_birth)->toDateString())" />
                    <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
                </div>
                @if ($showStatusField)
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">
                            @foreach (['pending', 'approved', 'rejected', 'terminated'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $member->status) === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>
                @endif
                <div>
                    <x-input-label for="referral_code" value="Referrer Referral Code" />
                    <x-text-input id="referral_code" name="referral_code" type="text" class="mt-1 block w-full" :value="old('referral_code', $member->referrer?->referral_code)" />
                    <x-input-error class="mt-2" :messages="$errors->get('referral_code')" />
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Full Width: Addresses -->
    <div class="lg:col-span-3">
        <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
            <h3 class="text-xl font-bold text-uber-black">Addresses</h3>
            <p class="mt-2 text-sm font-medium text-body-gray uppercase tracking-wide">Add one or more addresses. You can upload proof-of-address for each row.</p>
            <div class="mt-6 space-y-8">
                @foreach ($memberAddresses as $index => $address)
                    <div class="{{ $loop->index > 0 ? 'border-t border-chip-gray pt-8' : '' }}">
                        <input type="hidden" name="addresses[{{ $index }}][id]" value="{{ $address['id'] ?? '' }}">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label :for="'addresses_'.$index.'_type'" value="Address Type" />
                                <select id="addresses_{{ $index }}_type" name="addresses[{{ $index }}][address_type_id]" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">
                                    @foreach ($addressTypes as $type)
                                        <option value="{{ $type->id }}" @selected((string) ($address['address_type_id'] ?? '') === (string) $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-3 text-sm font-medium text-uber-black">
                                    <input type="hidden" name="addresses[{{ $index }}][is_primary]" value="0">
                                    <input type="checkbox" name="addresses[{{ $index }}][is_primary]" value="1" class="h-5 w-5 rounded border-chip-gray text-uber-black shadow-sm focus:ring-uber-black" @checked($address['is_primary'] ?? false)>
                                    Primary address
                                </label>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label :for="'addresses_'.$index.'_line_1'" value="Address Line 1" />
                                <x-text-input :id="'addresses_'.$index.'_line_1'" :name="'addresses['.$index.'][line_1]'" type="text" class="mt-1 block w-full" :value="$address['line_1'] ?? ''" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label :for="'addresses_'.$index.'_line_2'" value="Address Line 2" />
                                <x-text-input :id="'addresses_'.$index.'_line_2'" :name="'addresses['.$index.'][line_2]'" type="text" class="mt-1 block w-full" :value="$address['line_2'] ?? ''" />
                            </div>
                            <div>
                                <x-input-label :for="'addresses_'.$index.'_city'" value="City" />
                                <x-text-input :id="'addresses_'.$index.'_city'" :name="'addresses['.$index.'][city]'" type="text" class="mt-1 block w-full" :value="$address['city'] ?? ''" />
                            </div>
                            <div>
                                <x-input-label :for="'addresses_'.$index.'_state'" value="State" />
                                <x-text-input :id="'addresses_'.$index.'_state'" :name="'addresses['.$index.'][state]'" type="text" class="mt-1 block w-full" :value="$address['state'] ?? ''" />
                            </div>
                            <div>
                                <x-input-label :for="'addresses_'.$index.'_postal_code'" value="Postal Code" />
                                <x-text-input :id="'addresses_'.$index.'_postal_code'" :name="'addresses['.$index.'][postal_code]'" type="text" class="mt-1 block w-full" :value="$address['postal_code'] ?? ''" />
                            </div>
                            <div>
                                <x-input-label :for="'addresses_'.$index.'_country'" value="Country" />
                                <x-text-input :id="'addresses_'.$index.'_country'" :name="'addresses['.$index.'][country]'" type="text" class="mt-1 block w-full" :value="$address['country'] ?? ''" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label :for="'addresses_'.$index.'_proof'" value="Proof of Address" />
                                <input id="addresses_{{ $index }}_proof" name="addresses[{{ $index }}][proof_of_address]" type="file" class="mt-1 block w-full rounded-lg border border-chip-gray px-4 py-3 text-sm text-uber-black focus:border-uber-black focus:ring-uber-black" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="lg:col-span-3 flex items-center gap-4">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ $cancelRoute ?? route('members.index') }}" class="text-sm font-medium text-body-gray hover:text-uber-black transition">Cancel</a>
    </div>
</div>

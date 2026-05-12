@php
    $showPasswordFields = $showPasswordFields ?? ! $member->exists;
    $passwordRequired = $passwordRequired ?? $showPasswordFields;
    $showEmailField = $showEmailField ?? true;
    $readonlyEmail = $readonlyEmail ?? false;
    $showStatusField = $showStatusField ?? true;
    $showUsernameField = $showUsernameField ?? $showPasswordFields;
    $showProfileImageEditor = $showProfileImageEditor ?? true;
    $defaultProfileImageUrl = asset('images/default-profile-picture.jpg');
    $initialProfileImageUrl = $member->profileImage ? Storage::url($member->profileImage->path) : $defaultProfileImageUrl;
    $initialProfileImageName = $member->profileImage?->original_name;
    $countries = config('countries', []);
    $fallbackName = $member->full_name ?: 'New member';
    $fallbackEmail = $member->email ?: 'Add an email address';
    $rawPhone = old('phone', $member->phone);
    $initialPhoneCountryCode = old('phone_country_code');
    $initialPhoneNumber = old('phone_number');

    if ($initialPhoneCountryCode === null && $initialPhoneNumber === null && filled($rawPhone)) {
        if (preg_match('/^(\+\d{1,4})\s*(\d{6,15})$/', $rawPhone, $matches)) {
            $initialPhoneCountryCode = $matches[1];
            $initialPhoneNumber = $matches[2];
        } else {
            $initialPhoneNumber = preg_replace('/\D+/', '', $rawPhone);
        }
    }

    $memberAddresses = old('addresses', isset($member) && $member->addresses->count()
        ? $member->addresses->map(function ($address) {
            return [
                'key' => 'address-'.$address->id,
                'id' => $address->id,
                'address_type_id' => $address->address_type_id,
                'line_1' => $address->line_1,
                'line_2' => $address->line_2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country' => $address->country,
                'is_primary' => $address->is_primary,
                'proof_document_url' => $address->proofDocument ? Storage::url($address->proofDocument->path) : null,
                'proof_document_name' => $address->proofDocument?->original_name,
                'proof_document_mime_type' => $address->proofDocument?->mime_type,
            ];
        })->toArray()
        : [[
            'key' => 'address-new-0',
            'address_type_id' => $addressTypes->first()?->id,
            'line_1' => '',
            'line_2' => '',
            'city' => '',
            'state' => '',
            'postal_code' => '',
            'country' => 'Singapore',
            'is_primary' => true,
            'proof_document_url' => null,
            'proof_document_name' => null,
            'proof_document_mime_type' => null,
        ]]);
@endphp

<div
    x-data="memberProfileForm({
        initialFirstName: @js(old('first_name', $member->first_name)),
        initialLastName: @js(old('last_name', $member->last_name)),
        initialEmail: @js(old('email', $member->email)),
        initialProfileImageUrl: @js($initialProfileImageUrl),
        initialProfileImageName: @js($initialProfileImageName),
        defaultProfileImageUrl: @js($defaultProfileImageUrl),
        fallbackName: @js($fallbackName),
        fallbackEmail: @js($fallbackEmail),
        profileImageUploadUrl: @js(
            isset($member) && $member->exists
                ? (request()->routeIs('member.profile.edit')
                    ? route('member.profile.image.update')
                    : (request()->routeIs('members.edit') ? route('members.image.update', $member) : null))
                : null
        ),
        csrfToken: @js(csrf_token()),
    })"
    class="grid gap-8 lg:grid-cols-3"
>
    <!-- Left Column: Profile Picture -->
    <div class="lg:col-span-1">
        <div class="h-full rounded-[12px] bg-white p-8 shadow-uber-card">
            <div class="flex h-full flex-col items-center text-center">
                <div class="relative">
                    <div class="flex h-36 w-36 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-white shadow-[0_4px_16px_rgba(0,0,0,0.12)]">
                        <img :src="profileImageUrl" alt="Profile picture preview" class="h-full w-full object-cover">
                    </div>
                    @if ($showProfileImageEditor)
                        <button
                            type="button"
                            @click="triggerProfileImagePicker()"
                            :disabled="profileImageUploading"
                            class="absolute bottom-1 right-0 inline-flex h-11 w-11 items-center justify-center rounded-full border-[3px] border-white bg-uber-black text-white shadow-uber-float transition hover:bg-uber-black/90 focus:outline-none focus:ring-4 focus:ring-uber-black/10"
                            aria-label="Edit profile picture"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 20h9" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M16.5 3.5a2.121 2.121 0 013 3L8 18l-4 1 1-4 11.5-11.5z" />
                            </svg>
                        </button>
                    @endif
                </div>

                @if ($showProfileImageEditor)
                    <input
                        x-ref="profileImageInput"
                        id="profile_image"
                        name="profile_image"
                        type="file"
                        class="hidden"
                        accept="image/jpeg,image/png,image/webp"
                        @change="handleProfileImageSelection"
                    />
                @endif

                <p class="mt-5 text-[22px] font-semibold tracking-tight text-uber-black" x-text="displayName"></p>
                <p class="mt-2 text-sm text-body-gray" x-text="displayEmail"></p>
                <p class="mt-8 max-w-[18rem] text-sm leading-7 text-body-gray">
                    JPEG, PNG, or WebP only. Maximum upload size is 5 MB.
                </p>
                @if ($showProfileImageEditor)
                    <template x-if="profileImageUploading">
                        <p class="mt-4 text-sm font-medium text-uber-black">Uploading profile picture...</p>
                    </template>
                    <template x-if="profileImageClientError">
                        <p class="mt-4 text-sm font-medium text-uber-black" x-text="profileImageClientError"></p>
                    </template>
                    <x-input-error class="mt-4" :messages="$errors->get('profile_image')" />
                    <template x-if="profileImageName">
                        <p class="mt-3 text-xs font-medium uppercase tracking-[0.2em] text-muted-gray" x-text="profileImageName"></p>
                    </template>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Profile Details -->
    <div class="lg:col-span-2">
        <div class="h-full rounded-[8px] bg-white p-8 shadow-uber-card">
            <h3 class="text-xl font-bold text-uber-black mb-6">Profile Identity</h3>
            <div class="grid gap-6 md:grid-cols-2">
                @if ($showPasswordFields && $showUsernameField)
                    <div>
                        <x-input-label for="username" value="Username" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $member->user?->username)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('username')" />
                    </div>
                @endif
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
                @if (! $showPasswordFields && $showUsernameField)
                    <div>
                        <x-input-label for="username" value="Username" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" :value="old('username', $member->user?->username)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('username')" />
                    </div>
                @endif
                <div>
                    <x-input-label for="first_name" value="First Name" />
                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $member->first_name)" x-model="firstName" required />
                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                </div>
                <div>
                    <x-input-label for="last_name" value="Last Name" />
                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $member->last_name)" x-model="lastName" required />
                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                </div>
                @if ($showEmailField)
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full {{ $readonlyEmail ? 'bg-chip-gray text-body-gray cursor-not-allowed opacity-70' : '' }}" :value="old('email', $member->email)" x-model="email" :readonly="$readonlyEmail" :required="! $readonlyEmail" />
                        @if ($readonlyEmail)
                            <p class="mt-2 text-xs font-medium text-body-gray uppercase tracking-wide">Your login email is managed from the account settings page.</p>
                        @endif
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                @endif
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <div class="mt-1 grid gap-4 sm:grid-cols-[160px_minmax(0,1fr)]">
                        <div>
                            <x-text-input
                                id="phone_country_code"
                                name="phone_country_code"
                                type="text"
                                class="block w-full"
                                :value="$initialPhoneCountryCode"
                                placeholder="+60"
                                inputmode="tel"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('phone_country_code')" />
                        </div>
                        <div>
                            <x-text-input
                                id="phone_number"
                                name="phone_number"
                                type="text"
                                class="block w-full"
                                :value="$initialPhoneNumber"
                                placeholder="1135752400"
                                inputmode="numeric"
                                pattern="[0-9]{6,15}"
                            />
                            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                        </div>
                    </div>
                    <p class="mt-2 text-xs font-medium text-body-gray uppercase tracking-wide">Enter country code like +60 and the remaining phone number using digits only.</p>
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
                        <select id="status" name="status" class="field-select mt-1 block w-full">
                            @foreach (\App\Models\Member::STATUSES as $status)
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
            <div
                x-data="addressRepeater({
                    addresses: @js($memberAddresses),
                    addressTypes: @js($addressTypes->map(fn ($type) => ['id' => $type->id, 'name' => $type->name])->values()),
                    countries: @js($countries),
                    defaultCountry: 'Singapore',
                })"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-uber-black">Addresses</h3>
                        <p class="mt-2 text-sm font-medium text-body-gray uppercase tracking-wide">Add one or more addresses. You can upload proof-of-address for each row.</p>
                    </div>
                    <button
                        type="button"
                        @click="addAddress()"
                        :disabled="!canAddAddress"
                        class="inline-flex items-center justify-center rounded-full border border-[#d8d8d8] bg-white px-4 py-2 text-sm font-medium text-uber-black transition hover:bg-chip-gray disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Add address
                    </button>
                </div>

                <div class="mt-6 space-y-8">
                    <template x-for="(address, index) in addresses" :key="address.key">
                        <div class="border-t border-chip-gray pt-8 first:border-t-0 first:pt-0">
                            <input type="hidden" :name="addressFieldName(index, 'id')" x-model="address.id">
                            <div class="mb-6 flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-muted-gray" x-text="`Address ${index + 1}`"></p>
                                    <p class="mt-2 text-base font-semibold text-uber-black">Member address details</p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeAddress(index)"
                                    x-show="addresses.length > 1"
                                    class="text-sm font-medium text-body-gray transition hover:text-uber-black"
                                    style="display: none;"
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="grid gap-6 md:grid-cols-2">
                                <div>
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'type')">Address Type</label>
                                    <select
                                        :id="addressFieldId(index, 'type')"
                                        :name="addressFieldName(index, 'address_type_id')"
                                        class="field-select mt-1 block w-full"
                                        x-model="address.address_type_id"
                                    >
                                        <template x-for="type in addressTypes" :key="type.id">
                                            <option
                                                :value="type.id"
                                                x-text="type.name"
                                                :disabled="isAddressTypeTaken(type.id, address.key)"
                                            ></option>
                                        </template>
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'is_primary')">Primary Address</label>
                                    <div class="mt-1 flex min-h-[52px] items-center">
                                        <label class="inline-flex items-center gap-3 text-sm font-medium text-uber-black">
                                            <input type="hidden" :name="addressFieldName(index, 'is_primary')" value="0">
                                            <input
                                                :id="addressFieldId(index, 'is_primary')"
                                                :name="addressFieldName(index, 'is_primary')"
                                                type="checkbox"
                                                class="field-checkbox"
                                                value="1"
                                                :checked="address.is_primary"
                                                @change="setPrimary(index, $event.target.checked)"
                                            >
                                            <span>Set as primary address</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'line_1')">Address Line 1</label>
                                    <input :id="addressFieldId(index, 'line_1')" :name="addressFieldName(index, 'line_1')" type="text" class="field-base mt-1 block w-full" x-model="address.line_1">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'line_2')">Address Line 2</label>
                                    <input :id="addressFieldId(index, 'line_2')" :name="addressFieldName(index, 'line_2')" type="text" class="field-base mt-1 block w-full" x-model="address.line_2">
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'city')">City</label>
                                    <input :id="addressFieldId(index, 'city')" :name="addressFieldName(index, 'city')" type="text" class="field-base mt-1 block w-full" x-model="address.city">
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'state')">State</label>
                                    <input :id="addressFieldId(index, 'state')" :name="addressFieldName(index, 'state')" type="text" class="field-base mt-1 block w-full" x-model="address.state">
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'postal_code')">Postal Code</label>
                                    <input :id="addressFieldId(index, 'postal_code')" :name="addressFieldName(index, 'postal_code')" type="text" class="field-base mt-1 block w-full" x-model="address.postal_code">
                                </div>

                                <div>
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'country')">Country</label>
                                    <div
                                        x-data="countryPicker({
                                            countries: countries,
                                            value: address.country || defaultCountry,
                                        })"
                                        x-effect="address.country = selectedCountry"
                                        @click.outside="closePanel()"
                                        class="field-shell mt-1"
                                    >
                                        <input type="hidden" :name="addressFieldName(index, 'country')" x-model="selectedCountry">
                                        <button
                                            type="button"
                                            @click="togglePanel()"
                                            class="field-select flex w-full items-center justify-between text-left"
                                            :id="addressFieldId(index, 'country')"
                                        >
                                            <span x-text="selectedCountry || 'Select country'"></span>
                                        </button>

                                        <div x-show="open" x-transition class="field-combobox-panel" style="display: none;">
                                            <input
                                                x-ref="searchInput"
                                                type="text"
                                                x-model="query"
                                                class="field-base mb-3"
                                                placeholder="Search country"
                                            />
                                            <div class="max-h-64 overflow-y-auto space-y-1 pr-1">
                                                <template x-for="country in filteredCountries" :key="country">
                                                    <button
                                                        type="button"
                                                        @click="selectCountry(country)"
                                                        class="field-combobox-option"
                                                        :class="{ 'bg-chip-gray font-medium': selectedCountry === country }"
                                                        x-text="country"
                                                    ></button>
                                                </template>
                                                <p x-show="filteredCountries.length === 0" class="px-3 py-2 text-sm text-body-gray" style="display: none;">No countries found.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block font-medium text-sm text-uber-black" :for="addressFieldId(index, 'proof')">Proof of Address</label>
                                    <div
                                        x-data="proofDocumentField({
                                            initialUrl: address.proof_document_url,
                                            initialName: address.proof_document_name,
                                            initialMimeType: address.proof_document_mime_type,
                                        })"
                                    >
                                        <input
                                            :id="addressFieldId(index, 'proof')"
                                            :name="addressFieldName(index, 'proof_of_address')"
                                            type="file"
                                            class="field-file mt-1 block w-full"
                                            accept=".jpg,.jpeg,.png,.pdf,application/pdf,image/jpeg,image/png"
                                            @change="handleSelection"
                                        />

                                        <div x-show="hasDocument" class="field-proof-card" style="display: none;">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-muted-gray" x-text="previewLabel"></p>
                                                    <p class="mt-2 text-sm font-medium text-uber-black break-all" x-text="currentFileName"></p>
                                                </div>
                                                <a
                                                    x-show="currentFileUrl"
                                                    :href="currentFileUrl"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="text-sm font-medium text-[#1d4ed8] hover:underline"
                                                    style="display: none;"
                                                >
                                                    Open file
                                                </a>
                                            </div>

                                            <template x-if="isImage">
                                                <img :src="currentFileUrl" alt="Proof of address preview" class="field-proof-thumb mt-4">
                                            </template>

                                            <template x-if="isPdf">
                                                <div class="mt-4 rounded-[14px] border border-dashed border-[#d4d4d4] bg-white px-4 py-5 text-sm text-body-gray">
                                                    PDF document uploaded. Use “Open file” to preview it in a new tab.
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Buttons -->
    <div class="lg:col-span-3 flex items-center gap-4">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ $cancelRoute ?? route('members.index') }}" class="text-sm font-medium text-body-gray hover:text-uber-black transition">Cancel</a>
    </div>
</div>

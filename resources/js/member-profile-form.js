const PROFILE_IMAGE_MAX_BYTES = 5 * 1024 * 1024;
const PROFILE_IMAGE_ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

const createObjectUrl = (file) => URL.createObjectURL(file);

const revokeObjectUrl = (url) => {
    if (url && url.startsWith('blob:')) {
        URL.revokeObjectURL(url);
    }
};

const generateAddressKey = () => `address-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;

const normalizeBoolean = (value) => value === true || value === 1 || value === '1';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('memberProfileForm', (config = {}) => ({
        firstName: config.initialFirstName ?? '',
        lastName: config.initialLastName ?? '',
        email: config.initialEmail ?? '',
        profileImageUrl: config.initialProfileImageUrl ?? '',
        profileImageName: config.initialProfileImageName ?? '',
        defaultProfileImageUrl: config.defaultProfileImageUrl ?? '',
        fallbackName: config.fallbackName ?? 'New member',
        fallbackEmail: config.fallbackEmail ?? 'Add an email address',
        profileImageUploadUrl: config.profileImageUploadUrl ?? null,
        csrfToken: config.csrfToken ?? '',
        profileImageClientError: '',
        profileImageUploading: false,

        get displayName() {
            const fullName = [this.firstName, this.lastName]
                .map((value) => value?.trim())
                .filter(Boolean)
                .join(' ');

            return fullName || this.fallbackName;
        },

        get displayEmail() {
            return this.email?.trim() || this.fallbackEmail;
        },

        get shouldPersistProfileImageImmediately() {
            return Boolean(this.profileImageUploadUrl);
        },

        triggerProfileImagePicker() {
            this.profileImageClientError = '';
            this.$refs.profileImageInput?.click();
        },

        async handleProfileImageSelection(event) {
            const [file] = event.target.files ?? [];

            if (!file) {
                return;
            }

            const validationMessage = this.validateProfileImage(file);

            if (validationMessage) {
                this.profileImageClientError = validationMessage;
                this.resetProfileImageInput();
                return;
            }

            this.profileImageClientError = '';

            if (this.shouldPersistProfileImageImmediately) {
                await this.persistProfileImage(file);
                this.resetProfileImageInput();
                return;
            }

            revokeObjectUrl(this.profileImageUrl);
            this.profileImageUrl = createObjectUrl(file);
            this.profileImageName = file.name;
        },

        validateProfileImage(file) {
            if (!PROFILE_IMAGE_ALLOWED_TYPES.includes(file.type)) {
                return 'Please choose a JPEG, PNG, or WebP image.';
            }

            if (file.size > PROFILE_IMAGE_MAX_BYTES) {
                return 'The profile picture must be 5 MB or smaller.';
            }

            return '';
        },

        async persistProfileImage(file) {
            this.profileImageUploading = true;

            try {
                const formData = new FormData();

                formData.append('profile_image', file);

                const response = await fetch(this.profileImageUploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        Accept: 'application/json',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('Unable to save profile image.');
                }

                const payload = await response.json();

                revokeObjectUrl(this.profileImageUrl);
                this.profileImageUrl = payload.profile_image_url;
                this.profileImageName = payload.profile_image_name ?? file.name;
            } catch (error) {
                this.profileImageClientError = 'We could not upload this image. Please try again.';
            } finally {
                this.profileImageUploading = false;
            }
        },

        resetProfileImageInput() {
            if (this.$refs.profileImageInput) {
                this.$refs.profileImageInput.value = '';
            }
        },
    }));

    window.Alpine.data('addressRepeater', (config = {}) => ({
        addresses: [],
        addressTypes: config.addressTypes ?? [],
        countries: config.countries ?? [],
        defaultCountry: config.defaultCountry ?? 'Singapore',

        init() {
            const initialAddresses = Array.isArray(config.addresses) ? config.addresses : [];

            this.addresses = initialAddresses.length
                ? initialAddresses.map((address) => this.createAddress(address))
                : [this.createAddress()];

            this.normalizePrimary();
        },

        get canAddAddress() {
            return this.addresses.length < this.addressTypes.length;
        },

        createAddress(address = {}) {
            return {
                key: address.key ?? generateAddressKey(),
                id: address.id ?? '',
                address_type_id: address.address_type_id ?? this.nextAvailableAddressTypeId() ?? '',
                line_1: address.line_1 ?? '',
                line_2: address.line_2 ?? '',
                city: address.city ?? '',
                state: address.state ?? '',
                postal_code: address.postal_code ?? '',
                country: address.country ?? this.defaultCountry,
                is_primary: normalizeBoolean(address.is_primary),
                proof_document_url: address.proof_document_url ?? null,
                proof_document_name: address.proof_document_name ?? null,
                proof_document_mime_type: address.proof_document_mime_type ?? null,
            };
        },

        nextAvailableAddressTypeId(currentKey = null) {
            const usedTypeIds = this.addresses
                .filter((address) => address.key !== currentKey)
                .map((address) => String(address.address_type_id))
                .filter(Boolean);

            const nextType = this.addressTypes.find((type) => !usedTypeIds.includes(String(type.id)));

            return nextType?.id ?? this.addressTypes[0]?.id ?? '';
        },

        isAddressTypeTaken(typeId, currentKey) {
            return this.addresses.some((address) => address.key !== currentKey && String(address.address_type_id) === String(typeId));
        },

        addAddress() {
            if (!this.canAddAddress) {
                return;
            }

            this.addresses.push(this.createAddress({
                address_type_id: this.nextAvailableAddressTypeId(),
                is_primary: false,
            }));

            this.normalizePrimary();
        },

        removeAddress(index) {
            const removedAddress = this.addresses[index];

            if (removedAddress?.proof_document_url) {
                revokeObjectUrl(removedAddress.proof_document_url);
            }

            this.addresses.splice(index, 1);

            if (!this.addresses.length) {
                this.addresses.push(this.createAddress({
                    is_primary: true,
                }));
            }

            this.normalizePrimary();
        },

        setPrimary(index, checked) {
            if (checked) {
                this.addresses.forEach((address, addressIndex) => {
                    address.is_primary = addressIndex === index;
                });

                return;
            }

            if (!this.addresses.some((address, addressIndex) => addressIndex !== index && address.is_primary)) {
                this.addresses[index].is_primary = true;
            }
        },

        normalizePrimary() {
            const currentPrimaryIndex = this.addresses.findIndex((address) => address.is_primary);
            const resolvedPrimaryIndex = currentPrimaryIndex >= 0 ? currentPrimaryIndex : 0;

            this.addresses.forEach((address, index) => {
                address.is_primary = index === resolvedPrimaryIndex;
            });
        },

        addressFieldName(index, field) {
            return `addresses[${index}][${field}]`;
        },

        addressFieldId(index, field) {
            return `addresses_${index}_${field}`;
        },
    }));

    window.Alpine.data('countryPicker', (config = {}) => ({
        countries: config.countries ?? [],
        selectedCountry: config.value ?? '',
        query: '',
        open: false,

        get filteredCountries() {
            const search = this.query.trim().toLowerCase();

            if (!search) {
                return this.countries;
            }

            return this.countries.filter((country) => country.toLowerCase().includes(search));
        },

        openPanel() {
            this.open = true;

            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        },

        closePanel() {
            this.open = false;
            this.query = '';
        },

        togglePanel() {
            if (this.open) {
                this.closePanel();
                return;
            }

            this.openPanel();
        },

        selectCountry(country) {
            this.selectedCountry = country;
            this.closePanel();
        },
    }));

    window.Alpine.data('proofDocumentField', (config = {}) => ({
        currentFileUrl: config.initialUrl ?? '',
        currentFileName: config.initialName ?? '',
        currentFileMimeType: config.initialMimeType ?? '',
        isPersisted: Boolean(config.initialUrl),

        get hasDocument() {
            return Boolean(this.currentFileUrl && this.currentFileName);
        },

        get isImage() {
            return this.currentFileMimeType.startsWith('image/');
        },

        get isPdf() {
            return this.currentFileMimeType === 'application/pdf'
                || this.currentFileName.toLowerCase().endsWith('.pdf');
        },

        get previewLabel() {
            return this.isPersisted ? 'Saved file' : 'Selected file';
        },

        handleSelection(event) {
            const [file] = event.target.files ?? [];

            if (!file) {
                return;
            }

            revokeObjectUrl(this.currentFileUrl);
            this.currentFileUrl = createObjectUrl(file);
            this.currentFileName = file.name;
            this.currentFileMimeType = file.type;
            this.isPersisted = false;
        },
    }));
});

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">{{ $member->full_name }}</h2>
            <a href="{{ route('members.edit', $member) }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">Edit Member</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-8 lg:grid-cols-3 mt-8">
                <div class="rounded-[8px] bg-white p-8 shadow-uber-card lg:col-span-2">
                    <h3 class="text-2xl font-bold text-uber-black">Member Details</h3>
                    <dl class="mt-6 grid gap-6 md:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Login Account</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->user?->email ?: 'Not linked' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Email</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Phone</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->phone ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Status</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ ucfirst($member->status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Referral Code</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->referral_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Referrer</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->referrer?->full_name ?: '-' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Profile Image</dt>
                            <dd class="mt-4 flex items-center gap-5">
                                <div class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-white shadow-[0_4px_16px_rgba(0,0,0,0.12)]">
                                    <img
                                        src="{{ $member->profileImage ? Storage::url($member->profileImage->path) : asset('images/default-profile-picture.jpg') }}"
                                        alt="{{ $member->full_name }} profile picture"
                                        class="h-full w-full object-cover"
                                    >
                                </div>
                                <div>
                                    <p class="text-base font-medium text-uber-black">{{ $member->profileImage?->original_name ?: 'Default profile picture' }}</p>
                                    <p class="mt-1 text-sm text-body-gray">{{ $member->profileImage ? 'Uploaded member profile image' : 'No uploaded profile image available.' }}</p>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Referral Summary</h3>
                    <p class="mt-6 text-[36px] font-bold text-uber-black">{{ $member->referrals->count() }}</p>
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Direct referrals</p>
                    <p class="mt-8 text-[36px] font-bold text-uber-black">{{ $referralTree->count() }}</p>
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Total referral tree size</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2 mt-8">
                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Addresses</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($member->addresses as $address)
                            <div class="border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <p class="font-medium text-uber-black text-lg">{{ $address->addressType?->name }}</p>
                                <p class="mt-2 text-sm text-body-gray">{{ $address->line_1 }}</p>
                                @if ($address->line_2)
                                    <p class="text-sm text-body-gray">{{ $address->line_2 }}</p>
                                @endif
                                <p class="text-sm text-body-gray">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                <p class="text-sm text-body-gray">{{ $address->country }}</p>
                                <p class="mt-2 text-xs text-muted-gray uppercase tracking-wide">Proof: {{ $address->proofDocument?->original_name ?: 'Not uploaded' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-muted-gray">No addresses found.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Referral Tree</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($referralTree as $entry)
                            <div class="flex items-center justify-between border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-medium text-uber-black text-lg">{{ $entry['member']->full_name }}</p>
                                    <p class="text-sm text-body-gray">{{ $entry['member']->referral_code }}</p>
                                </div>
                                <span class="rounded-full bg-chip-gray px-3 py-1 text-xs font-semibold uppercase text-uber-black">Level {{ $entry['level'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-muted-gray">No referral descendants yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

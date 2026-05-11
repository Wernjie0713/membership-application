<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $member->full_name }}</h2>
            <a href="{{ route('members.edit', $member) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Edit Member</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900">Member Details</h3>
                    <dl class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Login Account</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->user?->email ?: 'Not linked' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->phone ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($member->status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Referral Code</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->referral_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Referrer</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->referrer?->full_name ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Profile Image</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->profileImage?->original_name ?: 'Not uploaded' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Referral Summary</h3>
                    <p class="mt-4 text-3xl font-semibold text-gray-900">{{ $member->referrals->count() }}</p>
                    <p class="text-sm text-gray-500">Direct referrals</p>
                    <p class="mt-6 text-sm text-gray-500">Total referral tree size</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ $referralTree->count() }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Addresses</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($member->addresses as $address)
                            <div class="rounded-md border border-gray-100 p-4">
                                <p class="font-medium text-gray-900">{{ $address->addressType?->name }}</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $address->line_1 }}</p>
                                @if ($address->line_2)
                                    <p class="text-sm text-gray-700">{{ $address->line_2 }}</p>
                                @endif
                                <p class="text-sm text-gray-700">{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                <p class="text-sm text-gray-700">{{ $address->country }}</p>
                                <p class="mt-2 text-xs text-gray-500">Proof: {{ $address->proofDocument?->original_name ?: 'Not uploaded' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No addresses found.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Referral Tree</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($referralTree as $entry)
                            <div class="flex items-center justify-between rounded-md border border-gray-100 px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $entry['member']->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $entry['member']->referral_code }}</p>
                                </div>
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold uppercase text-indigo-700">Level {{ $entry['level'] }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No referral descendants yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

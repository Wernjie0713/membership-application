<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Member Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500">Your referral code, referrals, tree, and rewards.</p>
            </div>
            <a href="{{ route('member.profile.edit') }}" class="rounded-md bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-sky-500">Edit Profile</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Referral Code</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $member->referral_code }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Direct Referrals</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $member->referrals->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Tree Size</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $referralTree->count() }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Rewards Earned</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $member->rewardAchievers->count() }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Your Referral Details</h3>
                    <dl class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Referrer</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->referrer?->full_name ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $member->phone ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Rewards</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentRewards as $reward)
                            <div class="rounded-md border border-gray-100 px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $reward->promotion?->name }}</p>
                                <p class="text-sm text-gray-500">Tier {{ $reward->promotionRewardTier?->tier }} • Threshold {{ $reward->threshold_reached }}</p>
                                <p class="mt-1 text-sm text-gray-700">{{ $reward->currency }} {{ number_format($reward->reward_amount, 2) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No rewards earned yet.</p>
                        @endforelse
                    </div>
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
                            <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase text-sky-700">Level {{ $entry['level'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No referral descendants yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

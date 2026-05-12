<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-[32px] text-uber-black leading-tight">Member Dashboard</h2>
                <p class="mt-2 text-sm text-body-gray">Your referral code, referrals, tree, and rewards.</p>
            </div>
            <a href="{{ route('member.profile.edit') }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">Edit Profile</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 md:grid-cols-4">
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Referral Code</p>
                    <p class="mt-2 text-[28px] font-bold text-uber-black">{{ $member->referral_code }}</p>
                </div>
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Direct Referrals</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $member->referrals->count() }}</p>
                </div>
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Tree Size</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $referralTree->count() }}</p>
                </div>
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Rewards Earned</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $member->rewardAchievers->count() }}</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2 mt-8">
                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Your Referral Details</h3>
                    <dl class="mt-6 grid gap-6 md:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Name</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Referrer</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->referrer?->full_name ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Email</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Phone</dt>
                            <dd class="mt-2 text-base font-medium text-uber-black">{{ $member->phone ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Recent Rewards</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($recentRewards as $reward)
                            <div class="border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <p class="font-medium text-uber-black text-lg">{{ $reward->promotion?->name }}</p>
                                <p class="text-sm text-body-gray mt-1">Tier {{ $reward->promotionRewardTier?->tier }} • Threshold {{ $reward->threshold_reached }}</p>
                                <p class="mt-2 text-sm font-medium text-uber-black">{{ $reward->currency }} {{ number_format($reward->reward_amount, 2) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-muted-gray">No rewards earned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="rounded-[8px] bg-white p-8 shadow-uber-card mt-8">
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
</x-app-layout>

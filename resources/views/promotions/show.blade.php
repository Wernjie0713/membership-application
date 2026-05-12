<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">{{ $promotion->name }}</h2>
            <a href="{{ route('promotions.edit', $promotion) }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">Edit Promotion</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                <h3 class="text-2xl font-bold text-uber-black">Promotion Details</h3>
                <dl class="mt-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Status</dt>
                        <dd class="mt-2 text-base font-medium text-uber-black">{{ ucfirst($promotion->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Date Range</dt>
                        <dd class="mt-2 text-base font-medium text-uber-black">{{ $promotion->start_date->toDateString() }} to {{ $promotion->end_date->toDateString() }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-body-gray uppercase tracking-wide">Description</dt>
                        <dd class="mt-2 text-base font-medium text-uber-black">{{ $promotion->description ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Reward Tiers</h3>
                    <div class="mt-6 space-y-4">
                        @foreach ($promotion->rewardTiers->sortBy('tier') as $tier)
                            <div class="border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <p class="font-medium text-uber-black text-lg">Tier {{ $tier->tier }}</p>
                                <p class="mt-2 text-sm text-body-gray">Threshold: {{ $tier->referral_threshold }}</p>
                                <p class="text-sm text-body-gray">Reward: {{ $tier->currency }} {{ number_format($tier->reward_amount, 2) }}</p>
                                <p class="mt-2 text-xs text-muted-gray uppercase tracking-wide">{{ $tier->is_recurring ? 'Recurring every '.$tier->step_increment.' referrals' : 'One-time reward' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Reward Achievers</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($promotion->rewardAchievers->sortByDesc('earned_at') as $reward)
                            <div class="border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <p class="font-medium text-uber-black text-lg">{{ $reward->member?->full_name }}</p>
                                <p class="mt-2 text-sm text-body-gray">Tier {{ $reward->promotionRewardTier?->tier }} at {{ $reward->threshold_reached }} referrals</p>
                                <p class="mt-2 text-xs text-muted-gray uppercase tracking-wide">{{ $reward->earned_at->toDayDateTimeString() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-muted-gray">No rewards earned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

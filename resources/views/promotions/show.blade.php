<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $promotion->name }}</h2>
            <a href="{{ route('promotions.edit', $promotion) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">Edit Promotion</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Promotion Details</h3>
                <dl class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($promotion->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Date Range</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $promotion->start_date->toDateString() }} to {{ $promotion->end_date->toDateString() }}</dd>
                    </div>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $promotion->description ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Reward Tiers</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($promotion->rewardTiers->sortBy('tier') as $tier)
                            <div class="rounded-md border border-gray-100 px-4 py-3">
                                <p class="font-medium text-gray-900">Tier {{ $tier->tier }}</p>
                                <p class="text-sm text-gray-700">Threshold: {{ $tier->referral_threshold }}</p>
                                <p class="text-sm text-gray-700">Reward: {{ $tier->currency }} {{ number_format($tier->reward_amount, 2) }}</p>
                                <p class="text-sm text-gray-500">{{ $tier->is_recurring ? 'Recurring every '.$tier->step_increment.' referrals' : 'One-time reward' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Reward Achievers</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($promotion->rewardAchievers->sortByDesc('earned_at') as $reward)
                            <div class="rounded-md border border-gray-100 px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $reward->member?->full_name }}</p>
                                <p class="text-sm text-gray-700">Tier {{ $reward->promotionRewardTier?->tier }} at {{ $reward->threshold_reached }} referrals</p>
                                <p class="text-sm text-gray-500">{{ $reward->earned_at->toDayDateTimeString() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No rewards earned yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

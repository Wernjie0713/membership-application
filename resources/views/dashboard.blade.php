<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">
                Membership Dashboard
            </h2>
            <a href="{{ route('members.create') }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">
                New Member
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-4">
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Members</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $stats['members'] }}</p>
                </div>
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Active Promotions</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $stats['active_promotions'] }}</p>
                </div>
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Rewards Logged</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $stats['rewards'] }}</p>
                </div>
                <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                    <p class="text-sm font-medium text-body-gray uppercase tracking-wide">Deactivated Members</p>
                    <p class="mt-2 text-[36px] font-bold text-uber-black">{{ $stats['deactivated_members'] }}</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2 mt-8">
                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Recent Members</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($recentMembers as $member)
                            <div class="flex items-center justify-between border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-medium text-uber-black text-lg">{{ $member->full_name }}</p>
                                    <p class="text-sm text-body-gray">{{ $member->email }}</p>
                                </div>
                                <span class="rounded-full bg-chip-gray px-3 py-1 text-xs font-semibold uppercase text-uber-black">{{ $member->status }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-muted-gray">No members yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                    <h3 class="text-2xl font-bold text-uber-black">Recent Rewards</h3>
                    <div class="mt-6 space-y-4">
                        @forelse ($recentRewards as $reward)
                            <div class="border-b border-chip-gray pb-4 last:border-0 last:pb-0">
                                <p class="font-medium text-uber-black text-lg">{{ $reward->member?->full_name }}</p>
                                <p class="text-sm text-body-gray mt-1">{{ $reward->promotion?->name }}</p>
                                <p class="mt-2 text-sm font-medium text-uber-black">Tier {{ $reward->promotionRewardTier?->tier }} - {{ $reward->currency }} {{ number_format($reward->reward_amount, 2) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-muted-gray">No rewards processed yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

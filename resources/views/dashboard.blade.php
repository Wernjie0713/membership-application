<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Membership Dashboard
            </h2>
            <a href="{{ route('members.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                New Member
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Members</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['members'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Active Promotions</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['active_promotions'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Rewards Logged</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['rewards'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Pending Members</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['pending_members'] }}</p>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Members</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentMembers as $member)
                            <div class="flex items-center justify-between rounded-md border border-gray-100 px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $member->full_name }}</p>
                                    <p class="text-sm text-gray-500">{{ $member->email }}</p>
                                </div>
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-600">{{ $member->status }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No members yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Rewards</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentRewards as $reward)
                            <div class="rounded-md border border-gray-100 px-4 py-3">
                                <p class="font-medium text-gray-900">{{ $reward->member?->full_name }}</p>
                                <p class="text-sm text-gray-500">{{ $reward->promotion?->name }}</p>
                                <p class="mt-1 text-sm text-gray-700">Tier {{ $reward->promotionRewardTier?->tier }} - {{ $reward->currency }} {{ number_format($reward->reward_amount, 2) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No rewards processed yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

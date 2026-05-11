<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reward Report</h2>
            <a href="{{ route('rewards.export', request()->query()) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Export</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('rewards.index') }}" class="grid gap-4 md:grid-cols-4">
                    <div>
                        <x-input-label for="member_id" value="Member" />
                        <select id="member_id" name="member_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All members</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected((string) ($filters['member_id'] ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="promotion_id" value="Promotion" />
                        <select id="promotion_id" name="promotion_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All promotions</option>
                            @foreach ($promotions as $promotion)
                                <option value="{{ $promotion->id }}" @selected((string) ($filters['promotion_id'] ?? '') === (string) $promotion->id)>{{ $promotion->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="date_from" value="Date From" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1 block w-full" :value="$filters['date_from'] ?? ''" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="Date To" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1 block w-full" :value="$filters['date_to'] ?? ''" />
                    </div>
                    <div class="md:col-span-4 flex items-center gap-3">
                        <x-primary-button>Apply Filters</x-primary-button>
                        <a href="{{ route('rewards.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Earned At</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Member</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Promotion</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Tier</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Threshold</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Referrals</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Reward</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($rewards as $reward)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">{{ $reward->earned_at->toDateTimeString() }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $reward->member?->full_name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $reward->promotion?->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $reward->promotionRewardTier?->tier }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $reward->threshold_reached }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $reward->referral_count }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $reward->currency }} {{ number_format($reward->reward_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">No rewards found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $rewards->links() }}
        </div>
    </div>
</x-app-layout>

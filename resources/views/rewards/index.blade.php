<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">Reward Report</h2>
            <a href="{{ route('rewards.export', request()->query()) }}" class="rounded-full border border-chip-gray bg-white px-4 py-2 text-sm font-medium text-uber-black shadow-sm hover:bg-chip-gray transition">Export</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                <form method="GET" action="{{ route('rewards.index') }}" class="grid gap-6 md:grid-cols-4">
                    <div>
                        <x-input-label for="member_id" value="Member" />
                        <select id="member_id" name="member_id" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">
                            <option value="">All members</option>
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}" @selected((string) ($filters['member_id'] ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="promotion_id" value="Promotion" />
                        <select id="promotion_id" name="promotion_id" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">
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
                    <div class="md:col-span-4 flex items-center gap-4">
                        <x-primary-button>Apply Filters</x-primary-button>
                        <a href="{{ route('rewards.index') }}" class="text-sm font-medium text-body-gray hover:text-uber-black transition">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-[8px] bg-white shadow-uber-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-chip-gray text-sm">
                        <thead class="bg-chip-gray">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Earned At</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Member</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Promotion</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Tier</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Threshold</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Referrals</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Reward</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-chip-gray bg-white">
                            @forelse ($rewards as $reward)
                                <tr>
                                    <td class="px-6 py-4 text-body-gray">{{ $reward->earned_at->toDateTimeString() }}</td>
                                    <td class="px-6 py-4 font-medium text-uber-black">{{ $reward->member?->full_name }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $reward->promotion?->name }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $reward->promotionRewardTier?->tier }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $reward->threshold_reached }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $reward->referral_count }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $reward->currency }} {{ number_format($reward->reward_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-muted-gray">No rewards found.</td>
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

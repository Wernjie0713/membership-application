<x-app-layout>
    @php
        $hasActiveFilters = filled($filters['search'] ?? null)
            || filled($filters['promotion_id'] ?? null)
            || (($filters['sort'] ?? 'latest') !== 'latest')
            || (($perPage ?? 10) !== 10);

        $sortOptions = [
            'latest' => 'Newest first',
            'oldest' => 'Oldest first',
            'member_asc' => 'Member A-Z',
            'promotion_asc' => 'Promotion A-Z',
            'reward_desc' => 'Highest reward',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">Reward Report</h2>
            <a href="{{ route('rewards.export', request()->query()) }}" class="rounded-full border border-chip-gray bg-white px-4 py-2 text-sm font-medium text-uber-black shadow-sm hover:bg-chip-gray transition">Export</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                <form method="GET" action="{{ route('rewards.index') }}" class="grid gap-6 lg:grid-cols-[1.4fr_1fr_0.9fr_auto]">
                    <div>
                        <x-input-label for="search" value="Search" />
                        <x-text-input id="search" name="search" type="text" class="mt-2 block w-full" :value="$filters['search'] ?? ''" placeholder="Member or promotion" />
                    </div>
                    <div>
                        <x-input-label for="promotion_id" value="Promotion" />
                        <select id="promotion_id" name="promotion_id" class="field-select mt-2 block w-full">
                            <option value="">All promotions</option>
                            @foreach ($promotions as $promotion)
                                <option value="{{ $promotion->id }}" @selected((string) ($filters['promotion_id'] ?? '') === (string) $promotion->id)>{{ $promotion->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="sort" value="Sort By" />
                        <select id="sort" name="sort" class="field-select mt-2 block w-full">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'latest') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-end gap-3 lg:justify-end">
                        <x-primary-button class="min-w-[126px] justify-center">Apply Filters</x-primary-button>
                        <a
                            href="{{ $hasActiveFilters ? route('rewards.index') : '#' }}"
                            @class([
                                'inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-medium transition',
                                'border border-chip-gray bg-white text-uber-black hover:bg-chip-gray' => $hasActiveFilters,
                                'cursor-not-allowed border border-chip-gray bg-chip-gray text-muted-gray pointer-events-none' => ! $hasActiveFilters,
                            ])
                            aria-disabled="{{ $hasActiveFilters ? 'false' : 'true' }}"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="rounded-[8px] bg-white shadow-uber-card">
                <div class="overflow-x-auto lg:overflow-visible">
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
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-uber-black">{{ $reward->earned_at->format('d/m/Y') }}</div>
                                        <div class="mt-1 text-xs text-body-gray/70">{{ $reward->earned_at->format('H:i:s') }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-medium">
                                        @if ($reward->member)
                                            <a href="{{ route('members.show', $reward->member) }}" class="text-sky-700 transition hover:text-sky-800 hover:underline">
                                                {{ $reward->member->full_name }}
                                            </a>
                                        @else
                                            <span class="text-body-gray">-</span>
                                        @endif
                                    </td>
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

            <div class="flex flex-col gap-4 pt-2 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('rewards.index') }}" class="flex flex-wrap items-center gap-3 lg:flex-nowrap">
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="promotion_id" value="{{ $filters['promotion_id'] ?? '' }}">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] ?? 'latest' }}">

                    <label for="per_page" class="max-w-[72px] text-sm font-medium leading-5 text-body-gray lg:max-w-none">Rows per page:</label>
                    <select
                        id="per_page"
                        name="per_page"
                        onchange="this.form.submit()"
                        class="field-select w-[112px] shrink-0 text-sm font-medium"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="lg:ml-auto">
                    {{ $rewards->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    @php
        $hasActiveFilters = filled($filters['search'] ?? null)
            || filled($filters['status'] ?? null)
            || (($filters['sort'] ?? 'latest') !== 'latest')
            || (($perPage ?? 10) !== 10);

        $statusStyles = [
            'approved' => [
                'dot' => 'bg-emerald-500',
            ],
            'pending' => [
                'dot' => 'bg-amber-500',
            ],
            'rejected' => [
                'dot' => 'bg-red-500',
            ],
            'terminated' => [
                'dot' => 'bg-slate-500',
            ],
        ];

        $sortOptions = [
            'latest' => 'Newest first',
            'oldest' => 'Oldest first',
            'name_asc' => 'Name A-Z',
            'name_desc' => 'Name Z-A',
            'referrals_desc' => 'Most referrals',
            'status_asc' => 'Status',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">Members</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('members.export', request()->query()) }}" class="rounded-full border border-chip-gray bg-white px-4 py-2 text-sm font-medium text-uber-black shadow-sm hover:bg-chip-gray transition">Export</a>
                <a href="{{ route('members.create') }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">New Member</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-[8px] bg-white p-6 shadow-uber-card">
                <form method="GET" action="{{ route('members.index') }}" class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr_0.8fr_auto]">
                    <div class="lg:col-span-1">
                        <x-input-label for="search" value="Search" />
                        <x-text-input id="search" name="search" type="text" class="mt-2 block w-full" :value="$filters['search'] ?? ''" placeholder="Name, email, referral code, or referrer" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="field-select mt-2 block w-full">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'approved', 'rejected', 'terminated'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
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
                            href="{{ $hasActiveFilters ? route('members.index') : '#' }}"
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
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Name</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Email</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Referral</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Referrer</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Status</th>
                                <th class="px-6 py-4 text-right font-semibold text-uber-black uppercase tracking-wide text-xs">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-chip-gray bg-white">
                            @forelse ($members as $member)
                                @php($statusStyle = $statusStyles[$member->status] ?? $statusStyles['terminated'])
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-uber-black">{{ $member->full_name }}</div>
                                        <div class="text-xs text-body-gray mt-1">{{ $member->phone ?: 'No phone' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-body-gray">{{ $member->email }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $member->referral_code }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $member->referrer?->full_name ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-2 text-sm font-medium text-uber-black">
                                            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $statusStyle['dot'] }}"></span>
                                            {{ ucfirst($member->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end">
                                            <x-dropdown align="right" width="48" contentClasses="rounded-[12px] bg-white p-2">
                                                <x-slot name="trigger">
                                                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-chip-gray bg-white px-4 py-2 text-sm font-medium text-uber-black transition hover:bg-chip-gray">
                                                        Actions
                                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                            <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                </x-slot>

                                                <x-slot name="content">
                                                    <div class="space-y-1">
                                                        <x-dropdown-link :href="route('members.show', $member)">View</x-dropdown-link>
                                                        <x-dropdown-link :href="route('members.edit', $member)">Edit</x-dropdown-link>

                                                        <div class="my-2 border-t border-chip-gray"></div>

                                                        @foreach (['pending', 'approved', 'rejected', 'terminated'] as $status)
                                                            @continue($status === $member->status)

                                                            <form method="POST" action="{{ route('members.status.update', ['member' => $member] + request()->query()) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $status }}">
                                                                <button type="submit" class="flex w-full items-start gap-3 rounded-[8px] px-4 py-2.5 text-left text-sm font-medium text-uber-black transition hover:bg-chip-gray">
                                                                    <span class="mt-[0.35rem] h-3 w-3 shrink-0 rounded-full {{ $statusStyles[$status]['dot'] }}"></span>
                                                                    <span class="leading-6">Change to {{ ucfirst($status) }}</span>
                                                                </button>
                                                            </form>
                                                        @endforeach
                                                    </div>
                                                </x-slot>
                                            </x-dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-muted-gray">No members found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-4 pt-2 lg:flex-row lg:items-center lg:justify-between">
                <form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-center gap-3 lg:flex-nowrap">
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <input type="hidden" name="status" value="{{ $filters['status'] ?? '' }}">
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
                    {{ $members->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

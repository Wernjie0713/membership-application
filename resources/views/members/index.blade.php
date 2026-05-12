<x-app-layout>
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
                <form method="GET" action="{{ route('members.index') }}" class="grid gap-6 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <x-input-label for="search" value="Search" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="$filters['search'] ?? ''" placeholder="Name, email, referral code, or referrer" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-lg border-chip-gray text-uber-black shadow-sm focus:border-uber-black focus:ring-uber-black">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'approved', 'rejected', 'terminated'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 flex items-center gap-4">
                        <x-primary-button>Apply Filters</x-primary-button>
                        <a href="{{ route('members.index') }}" class="text-sm font-medium text-body-gray hover:text-uber-black transition">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-[8px] bg-white shadow-uber-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-chip-gray text-sm">
                        <thead class="bg-chip-gray">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Name</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Email</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Referral</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Referrer</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Status</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-chip-gray bg-white">
                            @forelse ($members as $member)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-uber-black">{{ $member->full_name }}</div>
                                        <div class="text-xs text-body-gray mt-1">{{ $member->phone ?: 'No phone' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-body-gray">{{ $member->email }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $member->referral_code }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $member->referrer?->full_name ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-chip-gray px-3 py-1 text-xs font-semibold uppercase text-uber-black">{{ $member->status }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <a href="{{ route('members.show', $member) }}" class="text-sm font-medium text-uber-black hover:opacity-70 transition">View</a>
                                            <a href="{{ route('members.edit', $member) }}" class="text-sm font-medium text-uber-black hover:opacity-70 transition">Edit</a>
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

            {{ $members->links() }}
        </div>
    </div>
</x-app-layout>

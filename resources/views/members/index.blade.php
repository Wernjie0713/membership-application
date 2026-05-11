<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Members</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('members.export', request()->query()) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Export</a>
                <a href="{{ route('members.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">New Member</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="GET" action="{{ route('members.index') }}" class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <x-input-label for="search" value="Search" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" :value="$filters['search'] ?? ''" placeholder="Name, email, referral code, or referrer" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All statuses</option>
                            @foreach (['pending', 'approved', 'rejected', 'terminated'] as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 flex items-center gap-3">
                        <x-primary-button>Apply Filters</x-primary-button>
                        <a href="{{ route('members.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Email</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Referral</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Referrer</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($members as $member)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $member->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $member->phone ?: 'No phone' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $member->email }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $member->referral_code }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $member->referrer?->full_name ?: '-' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase text-gray-600">{{ $member->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('members.show', $member) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                                            <a href="{{ route('members.edit', $member) }}" class="text-gray-600 hover:text-gray-800">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No members found.</td>
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

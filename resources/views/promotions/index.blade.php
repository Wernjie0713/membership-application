<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Promotions</h2>
            <a href="{{ route('promotions.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">New Promotion</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Period</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Tiers</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Rewards</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($promotions as $promotion)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $promotion->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ ucfirst($promotion->status) }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $promotion->start_date->toDateString() }} to {{ $promotion->end_date->toDateString() }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $promotion->reward_tiers_count }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $promotion->reward_achievers_count }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('promotions.show', $promotion) }}" class="text-indigo-600 hover:text-indigo-800">View</a>
                                            <a href="{{ route('promotions.edit', $promotion) }}" class="text-gray-600 hover:text-gray-800">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">No promotions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{ $promotions->links() }}
        </div>
    </div>
</x-app-layout>

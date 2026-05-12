<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">Promotions</h2>
            <a href="{{ route('promotions.create') }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">New Promotion</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="overflow-hidden rounded-[8px] bg-white shadow-uber-card">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-chip-gray text-sm">
                        <thead class="bg-chip-gray">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Name</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Status</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Period</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Tiers</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Rewards</th>
                                <th class="px-6 py-4 text-left font-semibold text-uber-black uppercase tracking-wide text-xs">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-chip-gray bg-white">
                            @forelse ($promotions as $promotion)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-uber-black">{{ $promotion->name }}</td>
                                    <td class="px-6 py-4 text-body-gray">
                                        <span class="rounded-full bg-chip-gray px-3 py-1 text-xs font-semibold uppercase text-uber-black">{{ $promotion->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-body-gray">{{ $promotion->start_date->toDateString() }} to {{ $promotion->end_date->toDateString() }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $promotion->reward_tiers_count }}</td>
                                    <td class="px-6 py-4 text-body-gray">{{ $promotion->reward_achievers_count }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <a href="{{ route('promotions.show', $promotion) }}" class="text-sm font-medium text-uber-black hover:opacity-70 transition">View</a>
                                            <a href="{{ route('promotions.edit', $promotion) }}" class="text-sm font-medium text-uber-black hover:opacity-70 transition">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-muted-gray">No promotions found.</td>
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

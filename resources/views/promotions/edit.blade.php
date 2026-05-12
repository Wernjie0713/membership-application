<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">Edit Promotion</h2>
            <form method="POST" action="{{ route('promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-full border border-chip-gray bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-chip-gray transition">Delete</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />
            <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                <form method="POST" action="{{ route('promotions.update', $promotion) }}">
                    @csrf
                    @method('PUT')
                    @include('promotions._form', ['submitLabel' => 'Update Promotion'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

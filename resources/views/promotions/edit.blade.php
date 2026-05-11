<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Promotion</h2>
            <form method="POST" action="{{ route('promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-md border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('promotions.update', $promotion) }}">
                    @csrf
                    @method('PUT')
                    @include('promotions._form', ['submitLabel' => 'Update Promotion'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Promotion</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('promotions.store') }}">
                    @csrf
                    @include('promotions._form', ['submitLabel' => 'Create Promotion'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

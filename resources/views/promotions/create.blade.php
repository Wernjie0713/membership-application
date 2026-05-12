<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-[32px] text-uber-black leading-tight">Create Promotion</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="rounded-[8px] bg-white p-8 shadow-uber-card">
                <form method="POST" action="{{ route('promotions.store') }}">
                    @csrf
                    @include('promotions._form', ['submitLabel' => 'Create Promotion'])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

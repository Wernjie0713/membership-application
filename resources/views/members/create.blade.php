<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Member Account</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="mb-6 text-sm text-gray-600">This flow creates both the login account and the linked member profile in one step.</p>
                <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('members._form', [
                        'submitLabel' => 'Create Member',
                        'showPasswordFields' => true,
                        'passwordRequired' => true,
                        'showEmailField' => true,
                        'readonlyEmail' => false,
                        'showStatusField' => true,
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

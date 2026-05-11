<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Member Profile</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    @include('members._form', [
                        'submitLabel' => 'Save Profile',
                        'showPasswordFields' => false,
                        'showEmailField' => true,
                        'readonlyEmail' => true,
                        'showStatusField' => false,
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

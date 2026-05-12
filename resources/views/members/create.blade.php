<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-[32px] text-uber-black leading-tight">Create Member Account</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <p class="mb-6 text-sm text-body-gray">This flow creates both the login account and the linked member profile in one step.</p>
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
</x-app-layout>

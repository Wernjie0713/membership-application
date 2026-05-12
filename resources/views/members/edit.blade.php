<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-[32px] text-uber-black leading-tight">Edit Member</h2>
            <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Delete this member?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-full border border-chip-gray bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-chip-gray transition">Delete</button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <x-flash-message />
            <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('members._form', [
                    'submitLabel' => 'Update Member',
                    'showPasswordFields' => false,
                    'showUsernameField' => false,
                    'showProfileImageEditor' => false,
                    'showEmailField' => true,
                    'readonlyEmail' => false,
                    'showStatusField' => true,
                ])
            </form>
        </div>
    </div>
</x-app-layout>

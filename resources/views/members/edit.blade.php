<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Member</h2>
            <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Delete this member?')">
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
                <form method="POST" action="{{ route('members.update', $member) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('members._form', [
                        'submitLabel' => 'Update Member',
                        'showPasswordFields' => true,
                        'passwordRequired' => false,
                        'showEmailField' => true,
                        'readonlyEmail' => false,
                        'showStatusField' => true,
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Complete Membership Profile</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="mb-6 text-sm text-gray-600">Step 2 completes your membership registration. Finish your profile, addresses, uploads, and referral code to activate your referral tree and reward eligibility.</p>

                <form method="POST" action="{{ route('member.onboarding.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('members._form', [
                        'submitLabel' => 'Complete Profile',
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

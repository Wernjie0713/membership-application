<nav x-data="{ open: false }" class="bg-white">
    @php($user = Auth::user())
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-2 sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('member.dashboard')">
                        {{ $user->isAdmin() ? __('Dashboard') : __('Home') }}
                    </x-nav-link>
                    @if ($user->can('manage-members'))
                        <x-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                            {{ __('Members') }}
                        </x-nav-link>
                    @endif
                    @if ($user->can('manage-promotions'))
                        <x-nav-link :href="route('promotions.index')" :active="request()->routeIs('promotions.*')">
                            {{ __('Promotions') }}
                        </x-nav-link>
                    @endif
                    @if ($user->can('view-reward-reports'))
                        <x-nav-link :href="route('rewards.index')" :active="request()->routeIs('rewards.*')">
                            {{ __('Reward Report') }}
                        </x-nav-link>
                    @endif
                    @if ($user->isMember())
                        <x-nav-link :href="$user->hasCompletedMemberProfile() ? route('member.profile.edit') : route('member.onboarding.create')" :active="request()->routeIs('member.*') && !request()->routeIs('member.dashboard')">
                            {{ __('Member Portal') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-4 py-2 rounded-full bg-chip-gray text-uber-black text-sm font-medium hover:bg-hover-gray focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-full text-uber-black hover:bg-chip-gray focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden px-4 pb-4">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('member.dashboard')">
                {{ $user->isAdmin() ? __('Dashboard') : __('Home') }}
            </x-responsive-nav-link>
            @if ($user->can('manage-members'))
                <x-responsive-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                    {{ __('Members') }}
                </x-responsive-nav-link>
            @endif
            @if ($user->can('manage-promotions'))
                <x-responsive-nav-link :href="route('promotions.index')" :active="request()->routeIs('promotions.*')">
                    {{ __('Promotions') }}
                </x-responsive-nav-link>
            @endif
            @if ($user->can('view-reward-reports'))
                <x-responsive-nav-link :href="route('rewards.index')" :active="request()->routeIs('rewards.*')">
                    {{ __('Reward Report') }}
                </x-responsive-nav-link>
            @endif
            @if ($user->isMember())
                <x-responsive-nav-link :href="$user->hasCompletedMemberProfile() ? route('member.profile.edit') : route('member.onboarding.create')" :active="request()->routeIs('member.*') && !request()->routeIs('member.dashboard')">
                    {{ __('Member Portal') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-2">
                <div class="font-medium text-base text-uber-black">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-body-gray">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>

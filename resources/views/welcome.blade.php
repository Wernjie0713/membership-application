<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Blackwell Members') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white text-body-gray font-sans antialiased">
        <!-- Navigation -->
        <nav class="bg-white px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between max-w-7xl mx-auto animate-fade-in">
            <div class="text-uber-black font-bold text-2xl tracking-tight">
                Blackwell<span class="font-normal">Members</span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full bg-chip-gray px-4 py-2 text-sm font-medium text-uber-black hover:bg-hover-gray transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-uber-black hover:text-body-gray transition">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-uber-black px-4 py-2 text-sm font-medium text-white hover:bg-uber-black/90 transition">Sign up</a>
                @endauth
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-24">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Text Left -->
                <div class="animate-fade-in-up">
                    <h1 class="text-[52px] font-bold text-uber-black leading-[1.23] tracking-tight">
                        Go anywhere with our membership platform.
                    </h1>
                    <p class="mt-6 text-base text-body-gray leading-relaxed max-w-lg">
                        A clean, role-based platform built for admins and members. Register to unlock your profile, refer others, and earn rewards.
                    </p>
                    <div class="mt-8 flex gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-uber-black px-6 py-3 text-base font-medium text-white hover:bg-uber-black/90 transition">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-full bg-uber-black px-6 py-3 text-base font-medium text-white hover:bg-uber-black/90 transition">
                                Get started
                            </a>
                        @endauth
                    </div>
                </div>
                <!-- Visual Right -->
                <div class="bg-chip-gray rounded-[12px] h-[500px] flex items-center justify-center shadow-uber-card animate-fade-in opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.2s;">
                    <svg class="w-32 h-32 text-muted-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>

            <!-- Features Section -->
            <div class="mt-32" x-data="{ shown: false }" x-intersect.once="shown = true">
                <h2 :class="shown ? 'animate-fade-in-up' : 'opacity-0'" class="text-4xl font-bold text-uber-black mb-12 opacity-0" style="animation-fill-mode: forwards;">Built for scale.</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div :class="shown ? 'animate-fade-in-up' : 'opacity-0'" class="bg-white rounded-[8px] p-8 shadow-uber-card opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.1s;">
                        <div class="w-12 h-12 bg-chip-gray rounded-full flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-uber-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-uber-black mb-4">Secure Roles</h3>
                        <p class="text-body-gray text-base leading-relaxed">
                            Robust separation between administrators and members using Bouncer. Admin manage the system while members access their dedicated portal.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div :class="shown ? 'animate-fade-in-up' : 'opacity-0'" class="bg-white rounded-[8px] p-8 shadow-uber-card opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.2s;">
                        <div class="w-12 h-12 bg-chip-gray rounded-full flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-uber-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-uber-black mb-4">Onboarding Flow</h3>
                        <p class="text-body-gray text-base leading-relaxed">
                            Deliberate two-step registration. Secure your login account first, then complete your membership profile, addresses, and document uploads.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div :class="shown ? 'animate-fade-in-up' : 'opacity-0'" class="bg-white rounded-[8px] p-8 shadow-uber-card opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.3s;">
                        <div class="w-12 h-12 bg-chip-gray rounded-full flex items-center justify-center mb-6">
                            <svg class="w-6 h-6 text-uber-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-uber-black mb-4">Rewards</h3>
                        <p class="text-body-gray text-base leading-relaxed">
                            Tier-based promotional rewards calculated daily. Build your referral tree and get rewarded for fully onboarded, approved members.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-uber-black text-white pt-16 pb-8 mt-24" x-data="{ shown: false }" x-intersect.once="shown = true">
            <div :class="shown ? 'animate-fade-in' : 'opacity-0'" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 opacity-0" style="animation-fill-mode: forwards;">
                <div class="grid md:grid-cols-2 gap-8 mb-12">
                    <div>
                        <h2 class="text-2xl font-bold mb-4">Blackwell Members</h2>
                        <a href="{{ route('login') }}" class="text-muted-gray hover:text-white transition text-sm">Log in to your account</a>
                    </div>
                </div>
                <div class="border-t border-body-gray/30 pt-8 flex flex-col md:flex-row items-center justify-between text-sm text-muted-gray">
                    <p>&copy; {{ date('Y') }} Blackwell Members. Built with Laravel 11.</p>
                </div>
            </div>
        </footer>
    </body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Membership Application</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(14,165,233,0.18),_transparent_40%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.16),_transparent_35%),linear-gradient(135deg,_#e2e8f0,_#f8fafc)]"></div>
            <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-10 lg:px-10">
                <header class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-sky-700">Membership Application</p>
                        <h1 class="mt-3 max-w-2xl text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Referral membership platform with a clean split between admins and members.</h1>
                    </div>

                    <nav class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Open Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-white">Log In</a>
                            <a href="{{ route('register') }}" class="rounded-full bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-sky-500">Create Member Account</a>
                        @endauth
                    </nav>
                </header>

                <main class="mt-16 grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
                    <section class="rounded-[2rem] border border-white/60 bg-white/80 p-8 shadow-[0_25px_80px_rgba(15,23,42,0.08)] backdrop-blur">
                        <div class="grid gap-6 md:grid-cols-3">
                            <div class="rounded-3xl bg-slate-900 p-6 text-white">
                                <p class="text-sm uppercase tracking-[0.2em] text-slate-300">Admin</p>
                                <p class="mt-4 text-2xl font-semibold">Manage members, promotions, rewards, and exports.</p>
                            </div>
                            <div class="rounded-3xl bg-sky-50 p-6">
                                <p class="text-sm uppercase tracking-[0.2em] text-sky-700">Member</p>
                                <p class="mt-4 text-2xl font-semibold text-slate-900">Register, complete profile, refer others, and track rewards.</p>
                            </div>
                            <div class="rounded-3xl bg-emerald-50 p-6">
                                <p class="text-sm uppercase tracking-[0.2em] text-emerald-700">Referral Tree</p>
                                <p class="mt-4 text-2xl font-semibold text-slate-900">Rewards are based on completed member profiles only.</p>
                            </div>
                        </div>

                        <div class="mt-10 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <p class="text-sm font-semibold text-slate-500">1. Account Signup</p>
                                <p class="mt-2 text-sm text-slate-700">Public registration creates the member login account as the first step.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <p class="text-sm font-semibold text-slate-500">2. Member Onboarding</p>
                                <p class="mt-2 text-sm text-slate-700">The registration is completed after sign in with profile details, addresses, uploads, and referral code.</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                <p class="text-sm font-semibold text-slate-500">3. Rewards</p>
                                <p class="mt-2 text-sm text-slate-700">Only completed members enter the referral tree and promotion reward calculations.</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[2rem] bg-slate-900 p-8 text-white shadow-[0_25px_80px_rgba(15,23,42,0.18)]">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-300">Built For The Task</p>
                        <h2 class="mt-4 text-3xl font-semibold">Laravel 11 implementation with role-based access using Bouncer.</h2>
                        <ul class="mt-8 space-y-4 text-sm text-slate-200">
                            <li>Separate admin dashboard and member portal.</li>
                            <li>Role-aware redirects after registration, login, and email verification.</li>
                            <li>Admin-created members get login access plus linked member profiles.</li>
                            <li>Referral and reward processing ignores incomplete signups.</li>
                        </ul>
                    </section>
                </main>
            </div>
        </div>
    </body>
</html>

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            RoleAbilitySeeder::class,
            AddressTypeSeeder::class,
            PromotionSeeder::class,
            MemberSeeder::class,
        ]);

        $admin->assign('admin');

        User::query()
            ->whereHas('member')
            ->get()
            ->each(fn (User $user) => $user->assign('member'));
    }
}

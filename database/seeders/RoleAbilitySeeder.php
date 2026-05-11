<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class RoleAbilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bouncer::role()->firstOrCreate(['name' => 'admin'], ['title' => 'Administrator']);
        Bouncer::role()->firstOrCreate(['name' => 'member'], ['title' => 'Member']);

        Bouncer::allow('admin')->to([
            'view-admin-dashboard',
            'manage-members',
            'manage-promotions',
            'view-reward-reports',
            'export-members',
            'export-rewards',
        ]);

        Bouncer::allow('member')->to([
            'complete-member-profile',
            'access-member-portal',
            'view-own-referrals',
            'view-own-rewards',
        ]);
    }
}

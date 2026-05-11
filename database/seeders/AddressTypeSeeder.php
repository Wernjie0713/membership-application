<?php

namespace Database\Seeders;

use App\Models\AddressType;
use Illuminate\Database\Seeder;

class AddressTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Residential Address', 'Correspondence Address'] as $name) {
            AddressType::updateOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );
        }
    }
}

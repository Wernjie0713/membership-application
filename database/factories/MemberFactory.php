<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'referral_code' => Str::upper(fake()->unique()->bothify('REF####')),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
        ];
    }

    public function completed(): static
    {
        return $this->afterCreating(function (Member $member) {
            if ($member->user_id) {
                return;
            }

            $user = User::query()->firstOrCreate(
                ['email' => $member->email],
                [
                    'name' => $member->full_name,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );

            $user->assign('member');

            $member->forceFill([
                'user_id' => $user->id,
            ])->saveQuietly();
        });
    }
}

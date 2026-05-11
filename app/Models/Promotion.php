<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function rewardTiers(): HasMany
    {
        return $this->hasMany(PromotionRewardTier::class);
    }

    public function rewardAchievers(): HasMany
    {
        return $this->hasMany(RewardAchiever::class);
    }
}

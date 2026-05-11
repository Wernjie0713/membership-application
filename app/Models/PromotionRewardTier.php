<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromotionRewardTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'tier',
        'referral_threshold',
        'reward_amount',
        'currency',
        'is_recurring',
        'step_increment',
    ];

    protected function casts(): array
    {
        return [
            'is_recurring' => 'boolean',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function rewardAchievers(): HasMany
    {
        return $this->hasMany(RewardAchiever::class);
    }
}

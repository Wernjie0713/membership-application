<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RewardAchiever extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'promotion_id',
        'promotion_reward_tier_id',
        'threshold_reached',
        'referral_count',
        'reward_amount',
        'currency',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function promotionRewardTier(): BelongsTo
    {
        return $this->belongsTo(PromotionRewardTier::class);
    }
}

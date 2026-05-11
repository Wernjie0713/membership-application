<?php

namespace App\Services;

use App\Models\RewardAchiever;
use Illuminate\Database\Eloquent\Builder;

class RewardReportService
{
    public function query(array $filters): Builder
    {
        return RewardAchiever::query()
            ->with(['member', 'promotion', 'promotionRewardTier'])
            ->when($filters['member_id'] ?? null, fn (Builder $query, $memberId) => $query->where('member_id', $memberId))
            ->when($filters['promotion_id'] ?? null, fn (Builder $query, $promotionId) => $query->where('promotion_id', $promotionId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $dateFrom) => $query->whereDate('earned_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $dateTo) => $query->whereDate('earned_at', '<=', $dateTo))
            ->latest('earned_at');
    }
}

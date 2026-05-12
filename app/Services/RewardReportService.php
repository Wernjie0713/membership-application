<?php

namespace App\Services;

use App\Models\RewardAchiever;
use Illuminate\Database\Eloquent\Builder;

class RewardReportService
{
    public function query(array $filters): Builder
    {
        $query = RewardAchiever::query()
            ->with(['member', 'promotion', 'promotionRewardTier'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $like = '%'.$search.'%';

                $query->where(function (Builder $builder) use ($like) {
                    $builder
                        ->whereHas('member', function (Builder $memberQuery) use ($like) {
                            $memberQuery
                                ->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhereRaw("first_name || ' ' || last_name like ?", [$like]);
                        })
                        ->orWhereHas('promotion', function (Builder $promotionQuery) use ($like) {
                            $promotionQuery->where('name', 'like', $like);
                        });
                });
            })
            ->when($filters['promotion_id'] ?? null, fn (Builder $query, $promotionId) => $query->where('promotion_id', $promotionId));

        return match ($filters['sort'] ?? 'latest') {
            'oldest' => $query->oldest('earned_at'),
            'member_asc' => $query->leftJoin('members', 'members.id', '=', 'reward_achievers.member_id')
                ->select('reward_achievers.*')
                ->orderBy('members.first_name')
                ->orderBy('members.last_name')
                ->orderByDesc('reward_achievers.earned_at'),
            'reward_desc' => $query->orderByDesc('reward_amount')->orderByDesc('earned_at'),
            'promotion_asc' => $query->leftJoin('promotions', 'promotions.id', '=', 'reward_achievers.promotion_id')
                ->select('reward_achievers.*')
                ->orderBy('promotions.name')
                ->orderByDesc('reward_achievers.earned_at'),
            default => $query->latest('earned_at'),
        };
    }
}

<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Builder;

class PromotionQueryService
{
    public function applyFilters(Builder $query, array $filters): Builder
    {
        $query = $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $like = '%'.$search.'%';

                $query->where(function (Builder $builder) use ($like) {
                    $builder
                        ->where('name', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));

        return $this->applySorting($query, $filters['sort'] ?? 'latest');
    }

    public function baseQuery(): Builder
    {
        return Promotion::query()
            ->withCount(['rewardTiers', 'rewardAchievers']);
    }

    protected function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'status_asc' => $query->orderBy('status')->orderBy('name'),
            'start_date_asc' => $query->orderBy('start_date')->orderBy('name'),
            'end_date_desc' => $query->orderByDesc('end_date')->orderBy('name'),
            'rewards_desc' => $query->orderByDesc('reward_achievers_count')->orderBy('name'),
            default => $query->latest(),
        };
    }
}

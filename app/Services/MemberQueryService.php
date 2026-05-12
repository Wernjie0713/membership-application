<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;

class MemberQueryService
{
    public function applyFilters(Builder $query, array $filters): Builder
    {
        $query = $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $like = '%'.$search.'%';

                $query->where(function (Builder $builder) use ($like) {
                    $builder
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('referral_code', 'like', $like)
                        ->orWhereHas('referrer', function (Builder $referrerQuery) use ($like) {
                            $referrerQuery
                                ->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhere('referral_code', 'like', $like);
                        });
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status));

        return $this->applySorting($query, $filters['sort'] ?? 'latest');
    }

    public function baseQuery(): Builder
    {
        return Member::query()
            ->withTrashed()
            ->where(function (Builder $query) {
                $query
                    ->whereNull('deleted_at')
                    ->orWhereNotNull('user_id');
            })
            ->with(['referrer', 'addresses.addressType', 'profileImage'])
            ->withCount('referrals');
    }

    protected function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('first_name')->orderBy('last_name'),
            'name_desc' => $query->orderByDesc('first_name')->orderByDesc('last_name'),
            'referrals_desc' => $query->orderByDesc('referrals_count')->orderBy('first_name'),
            'status_asc' => $query->orderBy('status')->orderBy('first_name'),
            default => $query->latest(),
        };
    }
}

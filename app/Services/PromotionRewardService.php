<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Promotion;
use App\Models\PromotionRewardTier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromotionRewardService
{
    public function processActivePromotions(?Carbon $asOf = null): int
    {
        $asOf ??= now();

        $promotions = Promotion::query()
            ->with('rewardTiers')
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $asOf->toDateString())
            ->whereDate('end_date', '>=', $asOf->toDateString())
            ->get();

        $created = 0;

        foreach ($promotions as $promotion) {
            $created += $this->processPromotion($promotion, $asOf);
        }

        return $created;
    }

    public function processPromotion(Promotion $promotion, ?Carbon $asOf = null): int
    {
        $asOf ??= now();
        $promotion->loadMissing('rewardTiers');

        $created = 0;

        Member::query()
            ->rewardEligible()
            ->with('referrals')
            ->chunk(100, function ($members) use ($promotion, $asOf, &$created) {
                foreach ($members as $member) {
                    $created += $this->processMemberRewards($promotion, $member, $asOf);
                }
            });

        return $created;
    }

    public function processMemberRewards(Promotion $promotion, Member $member, Carbon $asOf): int
    {
        $referralCount = Member::query()
            ->rewardEligible()
            ->where('referrer_id', $member->id)
            ->whereBetween('created_at', [
                $promotion->start_date->startOfDay(),
                min($promotion->end_date->endOfDay(), $asOf),
            ])
            ->count();

        $inserted = 0;

        foreach ($promotion->rewardTiers->sortBy('referral_threshold') as $tier) {
            foreach ($this->qualifyingThresholds($tier, $referralCount) as $threshold) {
                $exists = $promotion->rewardAchievers()
                    ->where('member_id', $member->id)
                    ->where('promotion_reward_tier_id', $tier->id)
                    ->where('threshold_reached', $threshold)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::transaction(function () use ($promotion, $member, $tier, $threshold, $referralCount, $asOf) {
                    $promotion->rewardAchievers()->create([
                        'member_id' => $member->id,
                        'promotion_reward_tier_id' => $tier->id,
                        'threshold_reached' => $threshold,
                        'referral_count' => $referralCount,
                        'reward_amount' => $tier->reward_amount,
                        'currency' => $tier->currency,
                        'earned_at' => $asOf,
                    ]);
                });

                $inserted++;
            }
        }

        return $inserted;
    }

    protected function qualifyingThresholds(PromotionRewardTier $tier, int $referralCount): Collection
    {
        if ($referralCount < $tier->referral_threshold) {
            return collect();
        }

        if (! $tier->is_recurring) {
            return collect([$tier->referral_threshold]);
        }

        $step = $tier->step_increment ?: 10;
        $thresholds = [];

        for ($threshold = $tier->referral_threshold; $threshold <= $referralCount; $threshold += $step) {
            $thresholds[] = $threshold;
        }

        return collect($thresholds);
    }
}

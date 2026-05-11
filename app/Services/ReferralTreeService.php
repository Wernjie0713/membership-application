<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Collection;

class ReferralTreeService
{
    public function buildFor(Member $member): Collection
    {
        $member->loadMissing('referrals');

        return $this->descendants($member, 1);
    }

    protected function descendants(Member $member, int $level): Collection
    {
        $member->loadMissing('referrals');

        return $member->referrals
            ->sortBy('first_name')
            ->values()
            ->flatMap(function (Member $referral) use ($level) {
                $entry = collect([[
                    'member' => $referral,
                    'level' => $level,
                ]]);

                return $entry->concat($this->descendants($referral, $level + 1));
            })
            ->values();
    }
}

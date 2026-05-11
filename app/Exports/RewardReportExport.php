<?php

namespace App\Exports;

use App\Services\RewardReportService;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RewardReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected array $filters,
        protected RewardReportService $rewardReportService,
    ) {
    }

    public function collection(): Collection
    {
        return $this->rewardReportService->query($this->filters)->get();
    }

    public function headings(): array
    {
        return [
            'Earned At',
            'Member',
            'Promotion',
            'Tier',
            'Threshold Reached',
            'Referral Count',
            'Reward Amount',
            'Currency',
        ];
    }

    public function map($reward): array
    {
        return [
            optional($reward->earned_at)->toDateTimeString(),
            $reward->member?->full_name,
            $reward->promotion?->name,
            $reward->promotionRewardTier?->tier,
            $reward->threshold_reached,
            $reward->referral_count,
            $reward->reward_amount,
            $reward->currency,
        ];
    }
}

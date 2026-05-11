<?php

namespace App\Console\Commands;

use App\Services\PromotionRewardService;
use Illuminate\Console\Command;

class ProcessPromotionRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:process-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process active promotion rewards based on referral milestones.';

    /**
     * Execute the console command.
     */
    public function handle(PromotionRewardService $promotionRewardService): int
    {
        $created = $promotionRewardService->processActivePromotions();

        $this->info("Processed rewards successfully. New rewards created: {$created}.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Promotion;
use App\Models\RewardAchiever;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'stats' => [
                'members' => Member::count(),
                'active_promotions' => Promotion::where('status', 'active')->count(),
                'rewards' => RewardAchiever::count(),
                'deactivated_members' => Member::withTrashed()
                    ->where('status', Member::STATUS_DEACTIVATED)
                    ->whereNotNull('user_id')
                    ->count(),
            ],
            'recentMembers' => Member::latest()->take(5)->get(),
            'recentRewards' => RewardAchiever::with(['member', 'promotion'])->latest('earned_at')->take(5)->get(),
        ]);
    }
}

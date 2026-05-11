<?php

namespace App\Http\Controllers;

use App\Exports\RewardReportExport;
use App\Models\Member;
use App\Models\Promotion;
use App\Services\RewardReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RewardReportController extends Controller
{
    public function __construct(
        protected RewardReportService $rewardReportService,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['member_id', 'promotion_id', 'date_from', 'date_to']);

        return view('rewards.index', [
            'filters' => $filters,
            'rewards' => $this->rewardReportService->query($filters)->paginate(10)->withQueryString(),
            'members' => Member::completed()->orderBy('first_name')->get(),
            'promotions' => Promotion::orderBy('name')->get(),
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['member_id', 'promotion_id', 'date_from', 'date_to']);

        return Excel::download(
            new RewardReportExport($filters, $this->rewardReportService),
            'reward-report.xlsx'
        );
    }
}

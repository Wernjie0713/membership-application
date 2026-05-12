<?php

namespace App\Http\Controllers;

use App\Exports\RewardReportExport;
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
        $filters = $request->only(['search', 'promotion_id', 'sort']);
        $perPageOptions = [10, 20, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        return view('rewards.index', [
            'filters' => $filters,
            'rewards' => $this->rewardReportService->query($filters)->paginate($perPage)->withQueryString(),
            'promotions' => Promotion::orderBy('name')->get(),
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'promotion_id', 'sort']);

        return Excel::download(
            new RewardReportExport($filters, $this->rewardReportService),
            'reward-report.xlsx'
        );
    }
}

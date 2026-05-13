<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\PromotionQueryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct(protected PromotionQueryService $promotionQueryService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'sort']);
        $perPageOptions = [10, 20, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        $promotions = $this->promotionQueryService
            ->applyFilters($this->promotionQueryService->baseQuery(), $filters)
            ->paginate($perPage)
            ->withQueryString();

        return view('promotions.index', [
            'promotions' => $promotions,
            'filters' => $filters,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('promotions.create', [
            'promotion' => new Promotion([
                'status' => 'draft',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
            ]),
            'tiers' => $this->defaultTiers(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $promotion = Promotion::create($request->safe()->except('tiers'));
        $promotion->rewardTiers()->createMany($this->normalizeTiers($request->validated('tiers')));

        return redirect()
            ->route('promotions.show', $promotion)
            ->with('status', 'Promotion created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion): View
    {
        $promotion->load(['rewardTiers', 'rewardAchievers.member']);

        return view('promotions.show', [
            'promotion' => $promotion,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion): View
    {
        $promotion->load('rewardTiers');

        return view('promotions.edit', [
            'promotion' => $promotion,
            'tiers' => $promotion->rewardTiers->sortBy('tier')->values()->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($request->safe()->except('tiers'));

        $submittedTiers = collect($this->normalizeTiers($request->validated('tiers')));

        $submittedTiers->each(function (array $tier) use ($promotion) {
            $promotion->rewardTiers()->updateOrCreate(
                ['tier' => $tier['tier']],
                $tier
            );
        });

        $promotion->rewardTiers()
            ->whereNotIn('tier', $submittedTiers->pluck('tier'))
            ->doesntHave('rewardAchievers')
            ->delete();

        return redirect()
            ->route('promotions.show', $promotion)
            ->with('status', 'Promotion updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        if ($promotion->rewardAchievers()->exists() || $promotion->rewardTiers()->exists()) {
            return redirect()
                ->route('promotions.edit', $promotion)
                ->with('status', 'This promotion cannot be deleted because it has configured tiers or reward history. Change its status instead.');
        }

        $promotion->delete();

        return redirect()
            ->route('promotions.index')
            ->with('status', 'Promotion deleted successfully.');
    }

    public function updateStatus(Request $request, Promotion $promotion): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,active,inactive'],
        ]);

        $promotion->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('promotions.index', $request->query())
            ->with('status', 'Promotion status updated successfully.');
    }

    protected function defaultTiers(): array
    {
        return [
            ['tier' => 1, 'referral_threshold' => 10, 'reward_amount' => 100, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 2, 'referral_threshold' => 50, 'reward_amount' => 500, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 3, 'referral_threshold' => 100, 'reward_amount' => 1000, 'currency' => 'USD', 'is_recurring' => false, 'step_increment' => null],
            ['tier' => 4, 'referral_threshold' => 110, 'reward_amount' => 150, 'currency' => 'USD', 'is_recurring' => true, 'step_increment' => 10],
        ];
    }

    protected function normalizeTiers(array $tiers): array
    {
        return collect($tiers)
            ->map(fn (array $tier) => [
                'tier' => $tier['tier'],
                'referral_threshold' => $tier['referral_threshold'],
                'reward_amount' => $tier['reward_amount'],
                'currency' => strtoupper($tier['currency']),
                'is_recurring' => (bool) ($tier['is_recurring'] ?? false),
                'step_increment' => $tier['step_increment'] ?? null,
            ])
            ->all();
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromotionRequest;
use App\Http\Requests\UpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('promotions.index', [
            'promotions' => Promotion::withCount(['rewardTiers', 'rewardAchievers'])->latest()->paginate(10),
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
        $promotion->delete();

        return redirect()
            ->route('promotions.index')
            ->with('status', 'Promotion deleted successfully.');
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

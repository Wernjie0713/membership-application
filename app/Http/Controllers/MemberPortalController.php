<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteMemberProfileRequest;
use App\Http\Requests\UpdateMemberProfileImageRequest;
use App\Models\AddressType;
use App\Models\Member;
use App\Services\MemberService;
use App\Services\ReferralTreeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberPortalController extends Controller
{
    public function __construct(
        protected MemberService $memberService,
        protected ReferralTreeService $referralTreeService,
    ) {
    }

    public function onboarding(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasCompletedMemberProfile()) {
            return redirect()->route('member.dashboard');
        }

        return view('member.onboarding', [
            'member' => new Member([
                'email' => $request->user()->email,
                'status' => Member::STATUS_ACTIVE,
            ]),
            'addressTypes' => AddressType::orderBy('name')->get(),
            'cancelRoute' => route('dashboard'),
        ]);
    }

    public function storeOnboarding(CompleteMemberProfileRequest $request): RedirectResponse
    {
        $this->memberService->createForUser($request->user(), $request->validated());

        return redirect()
            ->route('member.dashboard')
            ->with('status', 'Member profile completed successfully.');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasCompletedMemberProfile()) {
            return redirect()->route('member.onboarding.create');
        }

        $member = $request->user()->member()->with([
            'addresses.addressType',
            'addresses.proofDocument',
            'profileImage',
            'referrer',
            'referrals',
            'rewardAchievers.promotion',
            'rewardAchievers.promotionRewardTier',
        ])->firstOrFail();

        return view('member.dashboard', [
            'member' => $member,
            'referralTree' => $this->referralTreeService->buildFor($member),
            'recentRewards' => $member->rewardAchievers->sortByDesc('earned_at')->take(10),
        ]);
    }

    public function edit(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasCompletedMemberProfile()) {
            return redirect()->route('member.onboarding.create');
        }

        $member = $request->user()->member()->with([
            'addresses.proofDocument',
            'profileImage',
            'referrer',
        ])->firstOrFail();

        return view('member.edit', [
            'member' => $member,
            'addressTypes' => AddressType::orderBy('name')->get(),
            'cancelRoute' => route('member.dashboard'),
        ]);
    }

    public function update(CompleteMemberProfileRequest $request): RedirectResponse
    {
        $member = $request->user()->member()->firstOrFail();

        $this->memberService->updateForUser($member, $request->user(), $request->validated());

        return redirect()
            ->route('member.dashboard')
            ->with('status', 'Member profile updated successfully.');
    }

    public function updateProfileImage(UpdateMemberProfileImageRequest $request)
    {
        $member = $request->user()->member()->firstOrFail();
        $member = $this->memberService->updateProfileImage($member, $request->file('profile_image'));

        return response()->json([
            'message' => 'Profile picture updated successfully.',
            'profile_image_url' => $member->profileImage
                ? asset('storage/'.$member->profileImage->path)
                : asset('images/default-profile-picture.jpg'),
            'profile_image_name' => $member->profileImage?->original_name,
        ]);
    }
}

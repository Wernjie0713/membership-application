<?php

namespace App\Http\Controllers;

use App\Exports\MembersExport;
use App\Http\Requests\AdminUpdateMemberProfileImageRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\AddressType;
use App\Models\Member;
use App\Services\MemberQueryService;
use App\Services\MemberService;
use App\Services\ReferralTreeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class MemberController extends Controller
{
    public function __construct(
        protected MemberService $memberService,
        protected MemberQueryService $memberQueryService,
        protected ReferralTreeService $referralTreeService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'sort']);
        $allowedPerPage = [10, 20, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);

        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $members = $this->memberQueryService
            ->applyFilters($this->memberQueryService->baseQuery(), $filters)
            ->paginate($perPage)
            ->withQueryString();

        return view('members.index', [
            'members' => $members,
            'filters' => $filters,
            'perPage' => $perPage,
            'perPageOptions' => $allowedPerPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('members.create', [
            'member' => new Member(['status' => 'pending']),
            'addressTypes' => AddressType::orderBy('name')->get(),
            'cancelRoute' => route('members.index'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $member = $this->memberService->create($request->validated());

        return redirect()
            ->route('members.show', $member)
            ->with('status', 'Member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member): View
    {
        $member->load([
            'user',
            'addresses.addressType',
            'addresses.proofDocument',
            'documents',
            'profileImage',
            'referrer',
            'referrals',
        ]);

        return view('members.show', [
            'member' => $member,
            'referralTree' => $this->referralTreeService->buildFor($member),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member): View
    {
        $member->load(['addresses.proofDocument', 'profileImage']);

        return view('members.edit', [
            'member' => $member,
            'addressTypes' => AddressType::orderBy('name')->get(),
            'cancelRoute' => route('members.show', $member),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $member = $this->memberService->update($member, $request->validated());

        return redirect()
            ->route('members.show', $member)
            ->with('status', 'Member updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member): RedirectResponse
    {
        DB::transaction(function () use ($member) {
            $member->loadMissing('user', 'addresses.documents', 'documents');

            $member->addresses->each(function ($address) {
                $address->documents->each(function ($document) {
                    Storage::disk($document->disk)->delete($document->path);
                    $document->delete();
                });
                $address->delete();
            });

            $member->documents->each(function ($document) {
                Storage::disk($document->disk)->delete($document->path);
                $document->delete();
            });

            if ($member->user) {
                $archivedEmail = 'deleted+member-'.$member->id.'-'.$member->user->id.'@archived.local';

                $member->user->retract('member');
                $member->user->update([
                    'email' => $archivedEmail,
                    'password' => bcrypt(\Illuminate\Support\Str::random(40)),
                    'remember_token' => \Illuminate\Support\Str::random(60),
                ]);
            }

            $member->delete();
        });

        return redirect()
            ->route('members.index')
            ->with('status', 'Member deleted successfully. The linked member login has been archived.');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'sort']);

        return Excel::download(
            new MembersExport($filters, $this->memberQueryService),
            'members-report.xlsx'
        );
    }

    public function updateStatus(Request $request, Member $member): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'terminated'])],
        ]);

        $member->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('members.index', $request->query())
            ->with('status', "Member status updated to {$validated['status']}.");
    }

    public function updateProfileImage(AdminUpdateMemberProfileImageRequest $request, Member $member)
    {
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

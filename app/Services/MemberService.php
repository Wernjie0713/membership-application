<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Document;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberService
{
    public function create(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $user->assign('member');

            $member = Member::create($this->memberPayload($data, null, $user));

            $this->syncAddresses($member, $data['addresses'] ?? []);
            $this->storeProfileImage($member, $data['profile_image'] ?? null);

            if (($data['status'] ?? Member::STATUS_ACTIVE) === Member::STATUS_DEACTIVATED) {
                $this->syncAdminStatus($member, Member::STATUS_DEACTIVATED);
            }

            return $member->fresh(['user', 'addresses.addressType', 'documents', 'referrer', 'referrals']);
        });
    }

    public function update(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data) {
            $originalStatus = $member->status;
            $wasTrashed = $member->trashed();
            $userWasDeactivated = $member->user?->isDeactivated() ?? false;

            $member->update($this->memberPayload($data, $member, $member->user));
            $this->syncUserFromMemberData($member->user, $data);

            $this->syncAddresses($member, $data['addresses'] ?? []);
            $this->storeProfileImage($member, $data['profile_image'] ?? null);

            if (
                array_key_exists('status', $data)
                && (
                    $data['status'] !== $originalStatus
                    || $wasTrashed
                    || ($data['status'] === Member::STATUS_ACTIVE && $userWasDeactivated)
                )
            ) {
                $this->syncAdminStatus($member, $data['status']);
            }

            return $member->fresh(['user', 'addresses.addressType', 'documents', 'referrer', 'referrals']);
        });
    }

    public function createForUser(User $user, array $data): Member
    {
        return DB::transaction(function () use ($user, $data) {
            if ($user->member()->exists()) {
                return $user->member()->firstOrFail();
            }

            $user->assign('member');

            $member = Member::create($this->memberPayload(
                $data + ['email' => $user->email, 'status' => Member::STATUS_ACTIVE],
                null,
                $user
            ));

            $this->syncAddresses($member, $data['addresses'] ?? []);
            $this->storeProfileImage($member, $data['profile_image'] ?? null);
            $this->syncUserFromMemberData($user, $data);

            return $member->fresh(['user', 'addresses.addressType', 'documents', 'referrer', 'referrals']);
        });
    }

    public function updateForUser(Member $member, User $user, array $data): Member
    {
        return DB::transaction(function () use ($member, $user, $data) {
            $member->update($this->memberPayload(
                $data + ['email' => $user->email, 'status' => $member->status],
                $member,
                $user
            ));

            $this->syncAddresses($member, $data['addresses'] ?? []);
            $this->storeProfileImage($member, $data['profile_image'] ?? null);
            $this->syncUserFromMemberData($user, $data);

            return $member->fresh(['user', 'addresses.addressType', 'documents', 'referrer', 'referrals']);
        });
    }

    public function updateProfileImage(Member $member, UploadedFile $file): Member
    {
        return DB::transaction(function () use ($member, $file) {
            $this->storeProfileImage($member, $file);

            return $member->fresh(['profileImage']);
        });
    }

    public function deactivateByAdmin(Member $member): void
    {
        $this->syncAdminStatus($member, Member::STATUS_DEACTIVATED);
    }

    public function syncAdminStatus(Member $member, string $status): void
    {
        DB::transaction(function () use ($member, $status) {
            $member->loadMissing('user');

            $member->forceFill([
                'status' => $status,
            ])->saveQuietly();

            if ($member->user) {
                $member->user->update([
                    'deactivated_at' => $status === Member::STATUS_DEACTIVATED ? now() : null,
                    'remember_token' => Str::random(60),
                ]);
            }

            if ($status === Member::STATUS_DEACTIVATED && ! $member->trashed()) {
                $member->delete();
            }

            if ($status === Member::STATUS_ACTIVE && $member->trashed()) {
                $member->restore();
            }
        });
    }

    public function permanentlyDeleteForUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $member = $user->member()->with([
                'addresses.documents',
                'documents',
                'rewardAchievers',
            ])->first();

            if ($member) {
                $this->deleteMemberOwnedFilesAndAddresses($member);

                $member->update([
                    'first_name' => 'Deleted',
                    'last_name' => 'Member',
                    'email' => $this->uniqueDeletedMemberEmail($member),
                    'phone' => null,
                    'date_of_birth' => null,
                    'user_id' => null,
                    'status' => Member::STATUS_DEACTIVATED,
                ]);

                if (! $member->trashed()) {
                    $member->delete();
                }
            }

            $user->delete();
        });
    }

    protected function memberPayload(array $data, ?Member $member = null, ?User $user = null): array
    {
        $payload = Arr::only($data, [
            'user_id',
            'first_name',
            'last_name',
            'email',
            'phone',
            'status',
            'date_of_birth',
        ]);

        if ($user) {
            $payload['user_id'] = $user->id;
            $payload['email'] = $user->email;
        }

        $payload['referrer_id'] = $member?->referrer_id;

        if (! empty($data['referral_code'])) {
            $referrer = Member::query()
                ->completed()
                ->when($member, fn ($query) => $query->whereKeyNot($member->id))
                ->where('referral_code', $data['referral_code'])
                ->first();

            $payload['referrer_id'] = $referrer?->id;
        } elseif (! $member) {
            $payload['referrer_id'] = null;
        }

        return $payload;
    }

    protected function syncUserFromMemberData(?User $user, array $data): void
    {
        if (! $user) {
            return;
        }

        $payload = [
            'username' => $data['username'] ?? $user->username,
        ];

        if (! empty($data['email'])) {
            $payload['email'] = $data['email'];
        }

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);
    }

    protected function syncAddresses(Member $member, array $addresses): void
    {
        $keptIds = [];

        foreach ($addresses as $addressData) {
            $payload = Arr::only($addressData, [
                'address_type_id',
                'line_1',
                'line_2',
                'city',
                'state',
                'postal_code',
                'country',
                'is_primary',
            ]);

            $payload['is_primary'] = (bool) ($payload['is_primary'] ?? false);

            $address = $member->addresses()->updateOrCreate(
                ['id' => $addressData['id'] ?? null],
                $payload
            );

            $keptIds[] = $address->id;

            if (! empty($addressData['proof_of_address']) && $addressData['proof_of_address'] instanceof UploadedFile) {
                $this->storeProofOfAddress($address, $addressData['proof_of_address']);
            }
        }

        $member->addresses()
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(function (Address $address) {
                $this->deleteAddressWithDocuments($address);
            });
    }

    protected function storeProfileImage(Member $member, ?UploadedFile $file): void
    {
        if (! $file instanceof UploadedFile) {
            return;
        }

        $existing = $member->profileImage;

        if ($existing) {
            Storage::disk($existing->disk)->delete($existing->path);
            $existing->delete();
        }

        $path = $file->store('members/profile-images', 'public');

        $member->documents()->create([
            'type' => 'profile_image',
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    protected function deleteMemberOwnedFilesAndAddresses(Member $member): void
    {
        $member->addresses->each(function (Address $address) {
            $this->deleteAddressWithDocuments($address);
        });

        $member->documents->each(function (Document $document) {
            $this->deleteDocumentFile($document);
        });
    }

    protected function deleteAddressWithDocuments(Address $address): void
    {
        $address->documents()->get()->each(function (Document $document) {
            $this->deleteDocumentFile($document);
        });

        $address->delete();
    }

    protected function deleteDocumentFile(Document $document): void
    {
        Storage::disk($document->disk)->delete($document->path);
        $document->delete();
    }

    protected function storeProofOfAddress(Address $address, UploadedFile $file): void
    {
        $existing = $address->proofDocument;

        if ($existing) {
            Storage::disk($existing->disk)->delete($existing->path);
            $existing->delete();
        }

        $path = $file->store('members/proof-of-address', 'public');

        $address->documents()->create([
            'type' => 'proof_of_address',
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    protected function uniqueDeletedMemberEmail(Member $member): string
    {
        do {
            $email = 'deleted-member-'.$member->id.'-'.Str::lower(Str::random(12)).'@deleted.local';
        } while (Member::withTrashed()->where('email', $email)->exists());

        return $email;
    }
}

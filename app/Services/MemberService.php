<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MemberService
{
    public function create(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $user->assign('member');

            $member = Member::create($this->memberPayload($data, null, $user));

            $this->syncAddresses($member, $data['addresses'] ?? []);
            $this->storeProfileImage($member, $data['profile_image'] ?? null);

            return $member->fresh(['user', 'addresses.addressType', 'documents', 'referrer', 'referrals']);
        });
    }

    public function update(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data) {
            $member->update($this->memberPayload($data, $member, $member->user));
            $this->syncUserFromMemberData($member->user, $data);

            $this->syncAddresses($member, $data['addresses'] ?? []);
            $this->storeProfileImage($member, $data['profile_image'] ?? null);

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
                $data + ['email' => $user->email, 'status' => 'pending'],
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
            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
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
                $address->documents()->each(function ($document) {
                    Storage::disk($document->disk)->delete($document->path);
                    $document->delete();
                });

                $address->delete();
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
}

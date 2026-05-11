<?php

namespace App\Services;

use App\Models\User;

class UserRedirectService
{
    public function routeNameFor(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin.dashboard';
        }

        if ($user->isMember()) {
            if ($user->hasCompletedMemberProfile()) {
                return 'member.dashboard';
            }

            return 'member.onboarding.create';
        }

        return 'profile.edit';
    }

    public function pathFor(User $user, bool $absolute = false): string
    {
        return route($this->routeNameFor($user), absolute: $absolute);
    }
}

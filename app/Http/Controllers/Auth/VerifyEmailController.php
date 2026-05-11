<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserRedirectService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request, UserRedirectService $userRedirectService): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to($userRedirectService->pathFor($request->user(), absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->to($userRedirectService->pathFor($request->user(), absolute: false).'?verified=1');
    }
}

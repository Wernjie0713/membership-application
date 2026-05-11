<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\UserRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request, UserRedirectService $userRedirectService): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->to($userRedirectService->pathFor($request->user(), absolute: false))
                    : view('auth.verify-email');
    }
}

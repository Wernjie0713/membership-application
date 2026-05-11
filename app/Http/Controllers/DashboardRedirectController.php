<?php

namespace App\Http\Controllers;

use App\Services\UserRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request, UserRedirectService $userRedirectService): RedirectResponse
    {
        return redirect()->to($userRedirectService->pathFor($request->user()));
    }
}

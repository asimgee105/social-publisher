<?php

namespace App\Http\Controllers;

use App\Services\Social\SocialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function __construct(
        protected SocialAccountService $accountService
    ) {}

    public function callback(Request $request, string $platform): RedirectResponse
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if (!$code) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'OAuth authorization was cancelled or denied by platform.');
        }

        try {
            $account = $this->accountService->handleCallback($platform, $code, $state ?? '');
            return redirect()->route('social-accounts.index')
                ->with('success', "Successfully connected @{$account->account_name} on " . ucfirst($platform) . "!");
        } catch (\Throwable $e) {
            return redirect()->route('social-accounts.index')
                ->with('error', 'OAuth Connection failed: ' . $e->getMessage());
        }
    }
}

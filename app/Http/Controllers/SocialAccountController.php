<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\SocialPlatform;
use App\Services\Social\SocialAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialAccountController extends Controller
{
    public function __construct(
        protected SocialAccountService $accountService
    ) {}

    public function index(): Response
    {
        $platforms = SocialPlatform::all();
        $accounts = SocialAccount::all();

        return Inertia::render('Settings/SocialAccounts', [
            'platforms' => $platforms,
            'accounts' => $accounts,
        ]);
    }

    public function connect(string $platform): RedirectResponse
    {
        $url = $this->accountService->generateAuthUrl($platform);
        return redirect()->away($url);
    }

    public function testConnection(SocialAccount $account): RedirectResponse
    {
        $isValid = $this->accountService->testConnection($account);
        $statusMsg = $isValid ? "Connection to @{$account->account_name} is active and verified!" : "Connection failed. Token may be expired.";
        return back()->with($isValid ? 'success' : 'error', $statusMsg);
    }

    public function disconnect(SocialAccount $account): RedirectResponse
    {
        $account->delete();
        return back()->with('success', "Account @{$account->account_name} disconnected.");
    }
}

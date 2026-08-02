<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Services\Social\Contracts\SocialPlatformProvider;
use App\Services\Social\Providers\FacebookProvider;
use App\Services\Social\Providers\InstagramProvider;
use App\Services\Social\Providers\LinkedInProvider;
use App\Services\Social\Providers\TikTokProvider;
use App\Services\Social\Providers\YouTubeProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class SocialAccountService
{
    public function getProvider(string $platform): SocialPlatformProvider
    {
        return match (strtolower($platform)) {
            'instagram' => app(InstagramProvider::class),
            'facebook' => app(FacebookProvider::class),
            'tiktok' => app(TikTokProvider::class),
            'youtube' => app(YouTubeProvider::class),
            'linkedin' => app(LinkedInProvider::class),
            default => throw new \InvalidArgumentException("Unsupported social platform: {$platform}"),
        };
    }

    public function generateAuthUrl(string $platform): string
    {
        $provider = $this->getProvider($platform);
        $state = Str::random(40);
        Session::put("oauth_state_{$platform}", $state);

        $redirectUri = route('oauth.callback', ['platform' => $platform]);
        return $provider->getAuthUrl($redirectUri, $state);
    }

    public function handleCallback(string $platform, string $code, string $state): SocialAccount
    {
        $savedState = Session::get("oauth_state_{$platform}");
        if ($savedState && $savedState !== $state) {
            throw new \InvalidArgumentException('Invalid OAuth state parameter. Possible CSRF security issue.');
        }

        Session::forget("oauth_state_{$platform}");

        $provider = $this->getProvider($platform);
        $redirectUri = route('oauth.callback', ['platform' => $platform]);

        $tokenData = $provider->handleCallback($code, $redirectUri);

        return SocialAccount::updateOrCreate(
            [
                'platform' => $platform,
                'platform_user_id' => $tokenData['platform_user_id'] ?? null,
            ],
            [
                'account_name' => $tokenData['account_name'] ?? ucfirst($platform) . ' Account',
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => $tokenData['expires_at'] ?? null,
                'status' => 'active',
                'scopes' => $tokenData['scopes'] ?? [],
                'last_synced_at' => now(),
            ]
        );
    }

    public function testConnection(SocialAccount $account): bool
    {
        try {
            $provider = $this->getProvider($account->platform);
            $isValid = $provider->validateConnection($account);
            $account->update([
                'status' => $isValid ? 'active' : 'needs_reauth',
                'last_synced_at' => now(),
            ]);
            return $isValid;
        } catch (\Throwable $e) {
            $account->update(['status' => 'needs_reauth']);
            return false;
        }
    }
}

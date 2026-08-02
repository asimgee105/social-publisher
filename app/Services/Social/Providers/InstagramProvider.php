<?php

namespace App\Services\Social\Providers;

use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\SocialAccount;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class InstagramProvider extends BasePlatformProvider
{
    protected function getPlatformKey(): string
    {
        return 'instagram';
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $credential = $this->getCredential();
        $clientId = $credential->client_id ?? 'INSTAGRAM_CLIENT_ID';

        $scopes = implode(',', $credential->scopes ?? [
            'instagram_basic',
            'instagram_content_publish',
            'pages_show_list',
            'pages_read_engagement',
        ]);

        return "https://www.facebook.com/v19.0/dialog/oauth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => $scopes,
            'response_type' => 'code',
        ]);
    }

    public function handleCallback(string $code, string $redirectUri): array
    {
        $credential = $this->getCredential();
        if ($this->isDryRun()) {
            return [
                'platform_user_id' => 'ig_' . Str::random(8),
                'account_name' => '@instagram_demo_account',
                'access_token' => 'mock_ig_token_' . Str::random(24),
                'refresh_token' => 'mock_ig_refresh_' . Str::random(24),
                'expires_at' => now()->addDays(60),
                'scopes' => ['instagram_basic', 'instagram_content_publish'],
            ];
        }

        $response = Http::asForm()->post("https://graph.facebook.com/v19.0/oauth/access_token", [
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Instagram OAuth Token Exchange failed: ' . $response->body());
        }

        $data = $response->json();
        return [
            'platform_user_id' => $data['user_id'] ?? null,
            'account_name' => '@instagram_account',
            'access_token' => $data['access_token'],
            'refresh_token' => null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
            'scopes' => ['instagram_basic', 'instagram_content_publish'],
        ];
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if ($this->isDryRun()) {
            $account->update(['token_expires_at' => now()->addDays(60)]);
            return $account;
        }

        $credential = $this->getCredential();
        $response = Http::get("https://graph.facebook.com/v19.0/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'fb_exchange_token' => $account->access_token,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $account->update([
                'access_token' => $data['access_token'],
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
                'status' => 'active',
            ]);
        } else {
            $account->update(['status' => 'needs_reauth']);
        }

        return $account;
    }

    public function validateConnection(SocialAccount $account): bool
    {
        if ($this->isDryRun()) {
            return true;
        }

        $response = Http::get("https://graph.facebook.com/v19.0/me", [
            'access_token' => $account->access_token,
        ]);

        return $response->successful();
    }

    public function publish(SocialAccount $account, PostContent $content, MediaAsset $media, bool $isDryRun = false): array
    {
        if ($this->isDryRun($isDryRun)) {
            return $this->mockPublishSuccess($account, $content, $media);
        }

        // Official Meta Instagram Container & Media Publishing API Flow
        $igAccountId = $account->platform_user_id;
        $caption = trim($content->caption . "\n\n" . implode(' ', $content->hashtags ?? []));
        $videoUrl = asset('storage/' . $media->path);

        // 1. Create Media Container
        $createRes = Http::post("https://graph.facebook.com/v19.0/{$igAccountId}/media", [
            'media_type' => 'REELS',
            'video_url' => $videoUrl,
            'caption' => $caption,
            'access_token' => $account->access_token,
        ]);

        if (!$createRes->successful()) {
            $err = $createRes->json('error.message') ?? $createRes->body();
            return [
                'success' => false,
                'error_code' => (string) ($createRes->json('error.code') ?? 'IG_CONTAINER_ERR'),
                'error_message' => 'Instagram Container Creation Failed: ' . $err,
            ];
        }

        $creationId = $createRes->json('id');

        // 2. Poll Status (max 10 attempts)
        for ($i = 0; $i < 10; $i++) {
            sleep(3);
            $statusRes = Http::get("https://graph.facebook.com/v19.0/{$creationId}", [
                'fields' => 'status_code',
                'access_token' => $account->access_token,
            ]);

            $statusCode = $statusRes->json('status_code');
            if ($statusCode === 'FINISHED') {
                break;
            } elseif ($statusCode === 'ERROR') {
                return [
                    'success' => false,
                    'error_code' => 'IG_PROCESSING_FAILED',
                    'error_message' => 'Instagram media processing failed on Meta servers.',
                ];
            }
        }

        // 3. Publish Container
        $publishRes = Http::post("https://graph.facebook.com/v19.0/{$igAccountId}/media_publish", [
            'creation_id' => $creationId,
            'access_token' => $account->access_token,
        ]);

        if (!$publishRes->successful()) {
            return [
                'success' => false,
                'error_code' => (string) ($publishRes->json('error.code') ?? 'IG_PUBLISH_ERR'),
                'error_message' => 'Instagram Media Publish Failed: ' . ($publishRes->json('error.message') ?? $publishRes->body()),
            ];
        }

        $mediaId = $publishRes->json('id');

        return [
            'success' => true,
            'platform_post_id' => $mediaId,
            'platform_post_url' => $this->getPostUrl($mediaId),
            'status' => 'published',
            'is_mock' => false,
        ];
    }

    public function schedule(SocialAccount $account, PostContent $content, MediaAsset $media, DateTimeInterface $scheduledAt, bool $isDryRun = false): array
    {
        return $this->publish($account, $content, $media, $isDryRun);
    }

    public function getPostStatus(SocialAccount $account, string $platformPostId): string
    {
        return 'published';
    }

    public function getPostUrl(string $platformPostId): ?string
    {
        return "https://www.instagram.com/p/{$platformPostId}/";
    }
}

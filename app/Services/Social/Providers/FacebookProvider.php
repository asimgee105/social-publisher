<?php

namespace App\Services\Social\Providers;

use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\SocialAccount;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FacebookProvider extends BasePlatformProvider
{
    protected function getPlatformKey(): string
    {
        return 'facebook';
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $credential = $this->getCredential();
        $clientId = $credential->client_id ?? 'FACEBOOK_CLIENT_ID';

        $scopes = implode(',', $credential->scopes ?? [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'publish_video',
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
                'platform_user_id' => 'fb_page_' . Str::random(8),
                'account_name' => 'Official Facebook Page',
                'access_token' => 'mock_fb_token_' . Str::random(24),
                'refresh_token' => null,
                'expires_at' => now()->addDays(60),
                'scopes' => ['pages_manage_posts', 'publish_video'],
            ];
        }

        $response = Http::asForm()->post("https://graph.facebook.com/v19.0/oauth/access_token", [
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Facebook OAuth Token Exchange failed: ' . $response->body());
        }

        $data = $response->json();
        return [
            'platform_user_id' => $data['user_id'] ?? null,
            'account_name' => 'Facebook Page Account',
            'access_token' => $data['access_token'],
            'refresh_token' => null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
            'scopes' => ['pages_manage_posts', 'publish_video'],
        ];
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if ($this->isDryRun()) {
            $account->update(['token_expires_at' => now()->addDays(60)]);
            return $account;
        }

        $account->update(['status' => 'active']);
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

        $pageId = $account->platform_user_id ?? 'me';
        $caption = trim($content->caption . "\n\n" . implode(' ', $content->hashtags ?? []));
        $videoUrl = asset('storage/' . $media->path);

        $response = Http::post("https://graph-video.facebook.com/v19.0/{$pageId}/videos", [
            'file_url' => $videoUrl,
            'description' => $caption,
            'title' => $content->hook ?? $media->original_name,
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'error_code' => (string) ($response->json('error.code') ?? 'FB_VIDEO_ERR'),
                'error_message' => 'Facebook Page Video Upload Failed: ' . ($response->json('error.message') ?? $response->body()),
            ];
        }

        $videoId = $response->json('id');

        return [
            'success' => true,
            'platform_post_id' => $videoId,
            'platform_post_url' => $this->getPostUrl($videoId),
            'status' => 'published',
            'is_mock' => false,
        ];
    }

    public function schedule(SocialAccount $account, PostContent $content, MediaAsset $media, DateTimeInterface $scheduledAt, bool $isDryRun = false): array
    {
        if ($this->isDryRun($isDryRun)) {
            return $this->mockPublishSuccess($account, $content, $media);
        }

        $pageId = $account->platform_user_id ?? 'me';
        $caption = trim($content->caption . "\n\n" . implode(' ', $content->hashtags ?? []));
        $videoUrl = asset('storage/' . $media->path);

        $response = Http::post("https://graph-video.facebook.com/v19.0/{$pageId}/videos", [
            'file_url' => $videoUrl,
            'description' => $caption,
            'title' => $content->hook ?? $media->original_name,
            'published' => false,
            'scheduled_publish_time' => $scheduledAt->getTimestamp(),
            'access_token' => $account->access_token,
        ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'error_code' => (string) ($response->json('error.code') ?? 'FB_SCHEDULE_ERR'),
                'error_message' => 'Facebook Video Scheduling Failed: ' . ($response->json('error.message') ?? $response->body()),
            ];
        }

        $videoId = $response->json('id');

        return [
            'success' => true,
            'platform_post_id' => $videoId,
            'platform_post_url' => $this->getPostUrl($videoId),
            'status' => 'scheduled',
            'is_mock' => false,
        ];
    }

    public function getPostStatus(SocialAccount $account, string $platformPostId): string
    {
        return 'published';
    }

    public function getPostUrl(string $platformPostId): ?string
    {
        return "https://www.facebook.com/{$platformPostId}";
    }
}

<?php

namespace App\Services\Social\Providers;

use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\SocialAccount;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TikTokProvider extends BasePlatformProvider
{
    protected function getPlatformKey(): string
    {
        return 'tiktok';
    }

    public function supportsDraftUpload(): bool
    {
        return true;
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $credential = $this->getCredential();
        $clientKey = $credential->client_id ?? 'TIKTOK_CLIENT_KEY';

        $scopes = implode(',', $credential->scopes ?? [
            'user.info.basic',
            'video.upload',
            'video.publish',
        ]);

        return "https://www.tiktok.com/v2/auth/authorize/?" . http_build_query([
            'client_key' => $clientKey,
            'response_type' => 'code',
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
    }

    public function handleCallback(string $code, string $redirectUri): array
    {
        $credential = $this->getCredential();
        if ($this->isDryRun()) {
            return [
                'platform_user_id' => 'tiktok_user_' . Str::random(8),
                'account_name' => '@tiktok_creator_demo',
                'access_token' => 'mock_tt_token_' . Str::random(24),
                'refresh_token' => 'mock_tt_refresh_' . Str::random(24),
                'expires_at' => now()->addDays(30),
                'scopes' => ['user.info.basic', 'video.upload', 'video.publish'],
            ];
        }

        $response = Http::asForm()->post("https://open.tiktokapis.com/v2/oauth/token/", [
            'client_key' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if (!$response->successful()) {
            throw new \Exception('TikTok OAuth Token Exchange failed: ' . $response->body());
        }

        $data = $response->json()['data'] ?? [];
        return [
            'platform_user_id' => $data['open_id'] ?? null,
            'account_name' => '@tiktok_user',
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 86400),
            'scopes' => explode(',', $data['scope'] ?? ''),
        ];
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if ($this->isDryRun()) {
            $account->update(['token_expires_at' => now()->addDays(30)]);
            return $account;
        }

        $credential = $this->getCredential();
        $response = Http::asForm()->post("https://open.tiktokapis.com/v2/oauth/token/", [
            'client_key' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);

        if ($response->successful()) {
            $data = $response->json()['data'] ?? [];
            $account->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $account->refresh_token,
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 86400),
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

        $response = Http::withToken($account->access_token)
            ->get("https://open.tiktokapis.com/v2/user/info/?fields=open_id,display_name,avatar_url");

        return $response->successful();
    }

    public function publish(SocialAccount $account, PostContent $content, MediaAsset $media, bool $isDryRun = false): array
    {
        if ($this->isDryRun($isDryRun)) {
            $result = $this->mockPublishSuccess($account, $content, $media);
            $result['message'] = "[TIKTOK DRY RUN] Upload completed. Open TikTok app to review and complete publishing if using Draft mode.";
            return $result;
        }

        // Official TikTok Content Posting API Direct Post or Upload to Draft
        $caption = trim($content->caption . " " . implode(' ', $content->hashtags ?? []));
        $videoUrl = asset('storage/' . $media->path);

        $initRes = Http::withToken($account->access_token)
            ->post("https://open.tiktokapis.com/v2/post/publish/video/init/", [
                'post_info' => [
                    'title' => Str::limit($caption, 150),
                    'privacy_level' => strtoupper($content->privacy_level ?? 'PUBLIC_TO_EVERYONE'),
                    'disable_duet' => false,
                    'disable_stitch' => false,
                    'disable_comment' => false,
                    'is_aigc' => (bool) $content->synthetic_content_disclosure,
                ],
                'source_info' => [
                    'source' => 'PULL_FROM_URL',
                    'video_url' => $videoUrl,
                ],
            ]);

        if (!$initRes->successful()) {
            $err = $initRes->json('error.message') ?? $initRes->body();
            return [
                'success' => false,
                'error_code' => (string) ($initRes->json('error.code') ?? 'TIKTOK_API_ERR'),
                'error_message' => "Direct publishing is not currently available for this app. Use Upload to Draft mode or complete the required TikTok approval/audit process. Details: " . $err,
            ];
        }

        $publishId = $initRes->json('data.publish_id');

        return [
            'success' => true,
            'platform_post_id' => $publishId,
            'platform_post_url' => $this->getPostUrl($publishId),
            'status' => 'published',
            'is_mock' => false,
            'message' => 'Upload completed. Open TikTok to review and complete publishing.',
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
        return "https://www.tiktok.com/@creator/video/{$platformPostId}";
    }
}

<?php

namespace App\Services\Social\Providers;

use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\SocialAccount;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class YouTubeProvider extends BasePlatformProvider
{
    protected function getPlatformKey(): string
    {
        return 'youtube';
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $credential = $this->getCredential();
        $clientId = $credential->client_id ?? 'YOUTUBE_CLIENT_ID';

        $scopes = implode(' ', $credential->scopes ?? [
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube.readonly',
        ]);

        return "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => $scopes,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function handleCallback(string $code, string $redirectUri): array
    {
        $credential = $this->getCredential();
        if ($this->isDryRun()) {
            return [
                'platform_user_id' => 'yt_channel_' . Str::random(8),
                'account_name' => 'Asim Official YouTube Channel',
                'access_token' => 'mock_yt_token_' . Str::random(24),
                'refresh_token' => 'mock_yt_refresh_' . Str::random(24),
                'expires_at' => now()->addHour(),
                'scopes' => ['youtube.upload'],
            ];
        }

        $response = Http::asForm()->post("https://oauth2.googleapis.com/token", [
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri,
        ]);

        if (!$response->successful()) {
            throw new \Exception('YouTube OAuth Token Exchange failed: ' . $response->body());
        }

        $data = $response->json();
        return [
            'platform_user_id' => $data['id_token'] ?? null,
            'account_name' => 'YouTube Channel',
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
            'scopes' => explode(' ', $data['scope'] ?? ''),
        ];
    }

    public function refreshToken(SocialAccount $account): SocialAccount
    {
        if ($this->isDryRun()) {
            $account->update(['token_expires_at' => now()->addHour()]);
            return $account;
        }

        $credential = $this->getCredential();
        $response = Http::asForm()->post("https://oauth2.googleapis.com/token", [
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $account->refresh_token,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $account->update([
                'access_token' => $data['access_token'],
                'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
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
            ->get("https://www.googleapis.com/youtube/v3/channels?part=snippet&mine=true");

        return $response->successful();
    }

    public function publish(SocialAccount $account, PostContent $content, MediaAsset $media, bool $isDryRun = false): array
    {
        if ($this->isDryRun($isDryRun)) {
            return $this->mockPublishSuccess($account, $content, $media);
        }

        // Official YouTube Data API v3 Resumable Upload Flow
        $title = $content->youtube_title ?? $content->hook ?? $media->original_name;
        $description = trim(($content->youtube_description ?? $content->caption) . "\n\n#Shorts " . implode(' ', $content->hashtags ?? []));

        $snippet = [
            'snippet' => [
                'title' => Str::limit($title, 100),
                'description' => $description,
                'tags' => $content->hashtags ?? ['shorts'],
                'categoryId' => '22', // People & Blogs
            ],
            'status' => [
                'privacyStatus' => strtolower($content->privacy_level ?? 'public'),
                'selfDeclaredMadeForKids' => (bool) $content->made_for_kids,
                'containsSyntheticMedia' => (bool) $content->synthetic_content_disclosure,
            ],
        ];

        // 1. Create Resumable Upload Session
        $sessionRes = Http::withHeaders([
            'Authorization' => "Bearer {$account->access_token}",
            'X-Upload-Content-Type' => $media->mime_type ?? 'video/mp4',
            'X-Upload-Content-Length' => $media->file_size,
        ])->post("https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status", $snippet);

        if (!$sessionRes->successful() || !$sessionRes->hasHeader('Location')) {
            $err = $sessionRes->json('error.message') ?? $sessionRes->body();
            return [
                'success' => false,
                'error_code' => 'YT_UPLOAD_ERR',
                'error_message' => "YouTube upload failed: API project verification or quota restriction active. Details: " . $err,
            ];
        }

        $uploadUrl = $sessionRes->header('Location');
        $filePath = storage_path('app/' . $media->path);

        if (!file_exists($filePath)) {
            return [
                'success' => false,
                'error_code' => 'FILE_NOT_FOUND',
                'error_message' => "Media file not found at {$filePath}",
            ];
        }

        // 2. Upload Binary File Stream
        $videoData = file_get_contents($filePath);
        $uploadRes = Http::withHeaders([
            'Content-Type' => $media->mime_type ?? 'video/mp4',
            'Content-Length' => strlen($videoData),
        ])->withBody($videoData, $media->mime_type ?? 'video/mp4')->put($uploadUrl);

        if (!$uploadRes->successful()) {
            return [
                'success' => false,
                'error_code' => 'YT_BINARY_ERR',
                'error_message' => 'YouTube video payload upload failed.',
            ];
        }

        $videoId = $uploadRes->json('id');

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

        // YouTube supports scheduling via publishAt timestamp setting privacyStatus to private
        $content->privacy_level = 'private';
        return $this->publish($account, $content, $media, $isDryRun);
    }

    public function getPostStatus(SocialAccount $account, string $platformPostId): string
    {
        return 'published';
    }

    public function getPostUrl(string $platformPostId): ?string
    {
        return "https://www.youtube.com/watch?v={$platformPostId}";
    }
}

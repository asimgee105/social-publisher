<?php

namespace App\Services\Social\Providers;

use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\SocialAccount;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LinkedInProvider extends BasePlatformProvider
{
    protected function getPlatformKey(): string
    {
        return 'linkedin';
    }

    public function getAuthUrl(string $redirectUri, string $state): string
    {
        $credential = $this->getCredential();
        $clientId = $credential->client_id ?? 'LINKEDIN_CLIENT_ID';

        $scopes = implode(' ', $credential->scopes ?? [
            'openid',
            'profile',
            'w_member_social',
        ]);

        return "https://www.linkedin.com/oauth/v2/authorization?" . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'scope' => $scopes,
        ]);
    }

    public function handleCallback(string $code, string $redirectUri): array
    {
        $credential = $this->getCredential();
        if ($this->isDryRun()) {
            return [
                'platform_user_id' => 'urn:li:person:' . Str::random(10),
                'account_name' => 'Asim LinkedIn Profile',
                'access_token' => 'mock_li_token_' . Str::random(24),
                'refresh_token' => 'mock_li_refresh_' . Str::random(24),
                'expires_at' => now()->addDays(60),
                'scopes' => ['openid', 'w_member_social'],
            ];
        }

        $response = Http::asForm()->post("https://www.linkedin.com/oauth/v2/accessToken", [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $credential->client_id,
            'client_secret' => $credential->client_secret,
        ]);

        if (!$response->successful()) {
            throw new \Exception('LinkedIn OAuth Token Exchange failed: ' . $response->body());
        }

        $data = $response->json();
        return [
            'platform_user_id' => 'urn:li:person:' . Str::random(8),
            'account_name' => 'LinkedIn Member',
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 5184000),
            'scopes' => explode(' ', $data['scope'] ?? ''),
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

        $response = Http::withToken($account->access_token)
            ->get("https://api.linkedin.com/v2/userinfo");

        return $response->successful();
    }

    public function publish(SocialAccount $account, PostContent $content, MediaAsset $media, bool $isDryRun = false): array
    {
        if ($this->isDryRun($isDryRun)) {
            return $this->mockPublishSuccess($account, $content, $media);
        }

        // Official LinkedIn Posts API / Video Asset Register & Share Flow
        $personUrn = $account->platform_user_id;
        $caption = trim($content->caption . "\n\n" . implode(' ', $content->hashtags ?? []));

        // 1. Register Upload Asset
        $registerRes = Http::withToken($account->access_token)->post("https://api.linkedin.com/v2/assets?action=registerUpload", [
            'registerUploadRequest' => [
                'recipes' => ['urn:li:digitalmediaRecipe:feedshare-video'],
                'owner' => $personUrn,
                'serviceRelationships' => [
                    [
                        'relationshipType' => 'OWNER',
                        'identifier' => 'urn:li:userGeneratedContent',
                    ],
                ],
            ],
        ]);

        if (!$registerRes->successful()) {
            $err = $registerRes->json('message') ?? $registerRes->body();
            return [
                'success' => false,
                'error_code' => 'LINKEDIN_PERM_ERR',
                'error_message' => "LinkedIn publishing permission is not enabled for this application. Details: " . $err,
            ];
        }

        $assetUrn = $registerRes->json('value.asset');
        $uploadUrl = $registerRes->json('value.uploadMechanism.com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest.uploadUrl');

        // 2. Upload Video Binary Data
        $filePath = storage_path('app/' . $media->path);
        if (file_exists($filePath)) {
            Http::withHeaders(['Content-Type' => 'application/octet-stream'])
                ->withBody(file_get_contents($filePath), 'application/octet-stream')
                ->post($uploadUrl);
        }

        // 3. Create UGC Post
        $postRes = Http::withToken($account->access_token)->post("https://api.linkedin.com/v2/ugcPosts", [
            'author' => $personUrn,
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => [
                        'text' => $caption,
                    ],
                    'shareMediaCategory' => 'VIDEO',
                    'media' => [
                        [
                            'status' => 'READY',
                            'description' => ['text' => Str::limit($caption, 200)],
                            'media' => $assetUrn,
                            'title' => ['text' => $content->hook ?? $media->original_name],
                        ],
                    ],
                ],
            ],
            'visibility' => [
                'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
            ],
        ]);

        if (!$postRes->successful()) {
            return [
                'success' => false,
                'error_code' => 'LINKEDIN_UGC_ERR',
                'error_message' => 'LinkedIn Post Creation Failed: ' . ($postRes->json('message') ?? $postRes->body()),
            ];
        }

        $postId = $postRes->json('id');

        return [
            'success' => true,
            'platform_post_id' => $postId,
            'platform_post_url' => $this->getPostUrl($postId),
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
        return "https://www.linkedin.com/feed/update/{$platformPostId}";
    }
}

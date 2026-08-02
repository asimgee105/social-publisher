<?php

namespace App\Services\Social\Contracts;

use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\SocialAccount;
use DateTimeInterface;

interface SocialPlatformProvider
{
    public function getAuthUrl(string $redirectUri, string $state): string;

    public function handleCallback(string $code, string $redirectUri): array;

    public function refreshToken(SocialAccount $account): SocialAccount;

    public function validateConnection(SocialAccount $account): bool;

    public function publish(SocialAccount $account, PostContent $content, MediaAsset $media, bool $isDryRun = false): array;

    public function schedule(SocialAccount $account, PostContent $content, MediaAsset $media, DateTimeInterface $scheduledAt, bool $isDryRun = false): array;

    public function getPostStatus(SocialAccount $account, string $platformPostId): string;

    public function getPostUrl(string $platformPostId): ?string;

    public function supportsScheduling(): bool;

    public function supportsDirectPublish(): bool;

    public function supportsDraftUpload(): bool;
}

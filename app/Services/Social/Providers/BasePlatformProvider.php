<?php

namespace App\Services\Social\Providers;

use App\Models\ApiCredential;
use App\Models\MediaAsset;
use App\Models\PostContent;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Services\Social\Contracts\SocialPlatformProvider;
use DateTimeInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

abstract class BasePlatformProvider implements SocialPlatformProvider
{
    abstract protected function getPlatformKey(): string;

    protected function getCredential(): ?ApiCredential
    {
        return ApiCredential::where('platform', $this->getPlatformKey())
            ->where('is_active', true)
            ->first();
    }

    protected function isDryRun(bool $isDryRunOverride = false): bool
    {
        if ($isDryRunOverride) {
            return true;
        }

        $globalDevMode = Setting::get('developer_mode', true);
        $credential = $this->getCredential();

        // If developer mode is explicitly ON or credentials aren't configured yet, fallback to Dry Run
        return $globalDevMode || !$credential || empty($credential->client_id);
    }

    public function supportsScheduling(): bool
    {
        return true;
    }

    public function supportsDirectPublish(): bool
    {
        return true;
    }

    public function supportsDraftUpload(): bool
    {
        return false;
    }

    protected function mockPublishSuccess(SocialAccount $account, PostContent $content, MediaAsset $media): array
    {
        $mockId = 'mock_' . Str::lower($this->getPlatformKey()) . '_' . Str::random(10);
        return [
            'success' => true,
            'platform_post_id' => $mockId,
            'platform_post_url' => $this->getPostUrl($mockId),
            'status' => 'published',
            'is_mock' => true,
            'message' => '[' . strtoupper($this->getPlatformKey()) . ' DRY RUN] Post published successfully to account @' . $account->account_name,
        ];
    }
}

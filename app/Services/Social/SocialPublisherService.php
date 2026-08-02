<?php

namespace App\Services\Social;

use App\Jobs\PublishFacebookJob;
use App\Jobs\PublishInstagramJob;
use App\Jobs\PublishLinkedInJob;
use App\Jobs\PublishTikTokJob;
use App\Jobs\PublishYouTubeJob;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\PostContent;
use App\Models\PostPlatform;
use App\Models\PublishAttempt;
use App\Models\ScheduledPost;
use App\Models\SocialAccount;
use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SocialPublisherService
{
    public function __construct(
        protected SocialAccountService $accountService
    ) {}

    public function createPost(array $data, ?int $userId = null): Post
    {
        return DB::transaction(function () use ($data, $userId) {
            $scheduledAt = !empty($data['scheduled_at'])
                ? Carbon::parse($data['scheduled_at'], $data['timezone'] ?? 'Asia/Karachi')->setTimezone('UTC')
                : null;

            $post = Post::create([
                'user_id' => $userId,
                'media_asset_id' => $data['media_asset_id'] ?? null,
                'title' => $data['title'] ?? 'Untitled Social Post',
                'base_content' => $data['base_content'] ?? '',
                'status' => $scheduledAt ? 'scheduled' : 'draft',
                'scheduled_at' => $scheduledAt,
                'timezone' => $data['timezone'] ?? 'Asia/Karachi',
            ]);

            $platformPayloads = $data['platforms'] ?? [];

            foreach ($platformPayloads as $platformKey => $payload) {
                if (empty($payload['enabled'])) {
                    continue;
                }

                $socialAccount = SocialAccount::where('id', $payload['social_account_id'] ?? null)
                    ->orWhere(function ($q) use ($platformKey) {
                        $q->where('platform', $platformKey)->where('is_default', true);
                    })->first();

                if (!$socialAccount) {
                    $socialAccount = SocialAccount::where('platform', $platformKey)->first();
                }

                if (!$socialAccount) {
                    // Create temporary mock account for dry-run testing
                    $socialAccount = SocialAccount::create([
                        'user_id' => $userId,
                        'platform' => $platformKey,
                        'account_name' => ucfirst($platformKey) . ' Demo Account',
                        'access_token' => 'mock_token_' . $platformKey,
                        'status' => 'active',
                        'is_default' => true,
                    ]);
                }

                $postPlatform = PostPlatform::create([
                    'post_id' => $post->id,
                    'social_account_id' => $socialAccount->id,
                    'platform_key' => $platformKey,
                    'status' => $scheduledAt ? 'scheduled' : 'draft',
                ]);

                PostContent::create([
                    'post_platform_id' => $postPlatform->id,
                    'caption' => $payload['caption'] ?? $data['base_content'] ?? '',
                    'hook' => $payload['hook'] ?? null,
                    'youtube_title' => $payload['youtube_title'] ?? null,
                    'youtube_description' => $payload['youtube_description'] ?? null,
                    'hashtags' => $payload['hashtags'] ?? [],
                    'privacy_level' => $payload['privacy_level'] ?? 'public',
                    'made_for_kids' => $payload['made_for_kids'] ?? false,
                    'synthetic_content_disclosure' => $payload['synthetic_content_disclosure'] ?? false,
                    'commercial_content_disclosure' => $payload['commercial_content_disclosure'] ?? false,
                ]);

                if ($scheduledAt) {
                    ScheduledPost::create([
                        'post_id' => $post->id,
                        'post_platform_id' => $postPlatform->id,
                        'scheduled_time_utc' => $scheduledAt,
                        'timezone' => $data['timezone'] ?? 'Asia/Karachi',
                        'status' => 'pending',
                    ]);
                }
            }

            return $post;
        });
    }

    public function publishPostNow(Post $post): void
    {
        $post->update(['status' => 'publishing']);

        foreach ($post->postPlatforms as $postPlatform) {
            $this->dispatchPlatformJob($postPlatform);
        }
    }

    public function dispatchPlatformJob(PostPlatform $postPlatform): void
    {
        $postPlatform->update(['status' => 'publishing']);

        match ($postPlatform->platform_key) {
            'instagram' => PublishInstagramJob::dispatch($postPlatform->id),
            'facebook' => PublishFacebookJob::dispatch($postPlatform->id),
            'tiktok' => PublishTikTokJob::dispatch($postPlatform->id),
            'youtube' => PublishYouTubeJob::dispatch($postPlatform->id),
            'linkedin' => PublishLinkedInJob::dispatch($postPlatform->id),
            default => null,
        };
    }

    public function executePlatformPublish(PostPlatform $postPlatform): void
    {
        $account = $postPlatform->socialAccount;
        $content = $postPlatform->postContent;
        $media = $postPlatform->post->mediaAsset;

        if (!$account || !$content || !$media) {
            $postPlatform->update([
                'status' => 'failed',
                'error_code' => 'MISSING_DATA',
                'error_message' => 'Post media or account data is missing.',
            ]);
            $this->updateMasterPostStatus($postPlatform->post_id);
            return;
        }

        try {
            $provider = $this->accountService->getProvider($postPlatform->platform_key);
            $result = $provider->publish($account, $content, $media);

            PublishAttempt::create([
                'post_platform_id' => $postPlatform->id,
                'attempt_type' => ($result['is_mock'] ?? false) ? 'dry_run' : 'publish',
                'status_code' => $result['success'] ? 200 : 400,
                'response_payload' => $result,
                'error_message' => $result['error_message'] ?? null,
                'attempted_at' => now(),
            ]);

            if ($result['success']) {
                $postPlatform->update([
                    'status' => 'published',
                    'platform_post_id' => $result['platform_post_id'] ?? null,
                    'platform_post_url' => $result['platform_post_url'] ?? null,
                    'error_code' => null,
                    'error_message' => null,
                    'published_at' => now(),
                ]);
                SystemLog::log('PUBLISH_SUCCESS', "Successfully published to {$postPlatform->platform_key} for post #{$postPlatform->post_id}");
            } else {
                $postPlatform->update([
                    'status' => 'failed',
                    'error_code' => $result['error_code'] ?? 'PUBLISH_FAILED',
                    'error_message' => $result['error_message'] ?? 'Publishing failed.',
                ]);
                SystemLog::log('PUBLISH_FAILED', "Failed to publish to {$postPlatform->platform_key}: " . ($result['error_message'] ?? ''), 'error');
            }
        } catch (\Throwable $e) {
            $postPlatform->update([
                'status' => 'failed',
                'error_code' => 'SYSTEM_EXCEPTION',
                'error_message' => $e->getMessage(),
            ]);
            PublishAttempt::create([
                'post_platform_id' => $postPlatform->id,
                'attempt_type' => 'publish',
                'status_code' => 500,
                'response_payload' => ['exception' => $e->getMessage()],
                'error_message' => $e->getMessage(),
                'attempted_at' => now(),
            ]);
            SystemLog::log('PUBLISH_EXCEPTION', "Exception during {$postPlatform->platform_key} publish: " . $e->getMessage(), 'error');
        }

        $this->updateMasterPostStatus($postPlatform->post_id);
    }

    public function updateMasterPostStatus(int $postId): void
    {
        $post = Post::with('postPlatforms')->find($postId);
        if (!$post) return;

        $statuses = $post->postPlatforms->pluck('status')->toArray();

        if (empty($statuses)) return;

        if (collect($statuses)->every(fn($s) => $s === 'published')) {
            $post->update(['status' => 'published', 'published_at' => now()]);
        } elseif (collect($statuses)->every(fn($s) => $s === 'failed')) {
            $post->update(['status' => 'failed']);
        } elseif (collect($statuses)->contains('published') && collect($statuses)->contains('failed')) {
            $post->update(['status' => 'partial_success']);
        }
    }
}

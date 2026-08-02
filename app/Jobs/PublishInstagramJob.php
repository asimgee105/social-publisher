<?php

namespace App\Jobs;

use App\Models\PostPlatform;
use App\Services\Social\SocialPublisherService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishInstagramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public int $postPlatformId) {}

    public function handle(SocialPublisherService $publisherService): void
    {
        $postPlatform = PostPlatform::find($this->postPlatformId);
        if ($postPlatform) {
            $publisherService->executePlatformPublish($postPlatform);
        }
    }
}

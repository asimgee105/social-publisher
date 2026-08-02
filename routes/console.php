<?php

use App\Models\ScheduledPost;
use App\Services\Social\SocialPublisherService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    $dueScheduled = ScheduledPost::where('status', 'pending')
        ->where('scheduled_time_utc', '<=', now())
        ->get();

    $publisherService = app(SocialPublisherService::class);

    foreach ($dueScheduled as $scheduledItem) {
        $scheduledItem->update(['status' => 'processing']);
        if ($scheduledItem->postPlatform) {
            $publisherService->dispatchPlatformJob($scheduledItem->postPlatform);
            $scheduledItem->update(['status' => 'completed']);
        }
    }
})->everyMinute()->name('process-scheduled-social-posts');

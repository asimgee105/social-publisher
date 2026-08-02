<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ScheduledPost;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(): Response
    {
        $scheduledEvents = ScheduledPost::with(['post.mediaAsset', 'postPlatform.socialAccount'])
            ->orderBy('scheduled_time_utc', 'asc')
            ->get();

        return Inertia::render('Calendar', [
            'events' => $scheduledEvents,
        ]);
    }
}

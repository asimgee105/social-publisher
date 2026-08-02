<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\PostPlatform;
use App\Models\ScheduledPost;
use App\Models\Setting;
use App\Models\SocialAccount;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'uploaded_videos' => MediaAsset::count(),
            'scheduled_posts' => Post::where('status', 'scheduled')->count(),
            'published_posts' => Post::where('status', 'published')->count(),
            'failed_posts' => PostPlatform::where('status', 'failed')->count(),
            'connected_platforms' => SocialAccount::where('status', 'active')->count(),
            'expired_connections' => SocialAccount::where('status', 'needs_reauth')->count(),
            'pending_jobs' => ScheduledPost::where('status', 'pending')->count(),
            'developer_mode' => Setting::get('developer_mode', true),
        ];

        $accounts = SocialAccount::all(['id', 'platform', 'account_name', 'status', 'last_synced_at', 'avatar_url']);

        $recentPosts = Post::with(['mediaAsset', 'postPlatforms.postContent'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $todayScheduled = ScheduledPost::with(['post.mediaAsset', 'postPlatform'])
            ->where('scheduled_time_utc', '>=', now()->startOfDay())
            ->where('scheduled_time_utc', '<=', now()->endOfDay())
            ->orderBy('scheduled_time_utc', 'asc')
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'accounts' => $accounts,
            'recentPosts' => $recentPosts,
            'todayScheduled' => $todayScheduled,
        ]);
    }
}

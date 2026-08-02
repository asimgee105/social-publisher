<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\PostPlatform;
use App\Models\SocialAccount;
use App\Services\Social\SocialPublisherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        protected SocialPublisherService $publisherService
    ) {}

    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Post::with(['mediaAsset', 'postPlatforms.postContent', 'postPlatforms.socialAccount'])
            ->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('base_content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(12)->withQueryString();

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'filters' => [
                'status' => $status ?? 'all',
                'search' => $search ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        $accounts = SocialAccount::all();
        $recentMedia = MediaAsset::orderBy('created_at', 'desc')->take(10)->get();

        return Inertia::render('Posts/CreateWizard', [
            'accounts' => $accounts,
            'recentMedia' => $recentMedia,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'media_asset_id' => 'required|exists:media_assets,id',
            'title' => 'required|string|max:255',
            'base_content' => 'required|string',
            'scheduled_at' => 'nullable|string',
            'timezone' => 'nullable|string',
            'platforms' => 'required|array',
        ]);

        $post = $this->publisherService->createPost($validated, $request->user()?->id);

        if (empty($validated['scheduled_at'])) {
            $this->publisherService->publishPostNow($post);
            return redirect()->route('posts.show', $post->id)->with('success', 'Publishing jobs queued across selected platforms!');
        }

        return redirect()->route('posts.index')->with('success', 'Post scheduled successfully!');
    }

    public function show(Post $post): Response
    {
        $post->load(['mediaAsset', 'postPlatforms.postContent', 'postPlatforms.socialAccount', 'postPlatforms.publishAttempts']);

        return Inertia::render('Posts/Show', [
            'post' => $post,
        ]);
    }

    public function retryPlatform(PostPlatform $postPlatform): RedirectResponse
    {
        $this->publisherService->dispatchPlatformJob($postPlatform);
        return back()->with('success', "Retry queued for {$postPlatform->platform_key}!");
    }

    public function duplicate(Post $post): RedirectResponse
    {
        $post->load(['mediaAsset', 'postPlatforms.postContent']);

        $newPost = Post::create([
            'media_asset_id' => $post->media_asset_id,
            'title' => $post->title . ' (Copy)',
            'base_content' => $post->base_content,
            'status' => 'draft',
            'timezone' => $post->timezone,
        ]);

        foreach ($post->postPlatforms as $pf) {
            $newPf = PostPlatform::create([
                'post_id' => $newPost->id,
                'social_account_id' => $pf->social_account_id,
                'platform_key' => $pf->platform_key,
                'status' => 'draft',
            ]);

            if ($pf->postContent) {
                $newPf->postContent()->create([
                    'caption' => $pf->postContent->caption,
                    'hook' => $pf->postContent->hook,
                    'youtube_title' => $pf->postContent->youtube_title,
                    'youtube_description' => $pf->postContent->youtube_description,
                    'hashtags' => $pf->postContent->hashtags,
                    'privacy_level' => $pf->postContent->privacy_level,
                ]);
            }
        }

        return redirect()->route('posts.show', $newPost->id)->with('success', 'Post duplicated successfully!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted.');
    }
}

<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\PostPlatform;
use App\Models\SocialAccount;

use App\Services\Social\SocialPublisherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialPublisherTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_publish_multi_platform_post(): void
    {
        $media = MediaAsset::create([
            'filename' => 'test-video.mp4',
            'original_name' => 'test-video.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 1024000,
            'duration' => 15.0,
            'width' => 1080,
            'height' => 1920,
            'aspect_ratio' => '9:16',
            'path' => 'media/originals/test-video.mp4',
        ]);

        $service = app(SocialPublisherService::class);

        $post = $service->createPost([
            'title' => 'Test AI Campaign',
            'base_content' => 'Test topic for multi-platform publishing',
            'media_asset_id' => $media->id,
            'timezone' => 'Asia/Karachi',
            'platforms' => [
                'instagram' => ['enabled' => true, 'caption' => 'IG Caption #reel', 'hashtags' => ['#reel']],
                'tiktok' => ['enabled' => true, 'caption' => 'TikTok Copy #fyp', 'hashtags' => ['#fyp']],
                'youtube' => ['enabled' => true, 'youtube_title' => 'YT Shorts Title', 'youtube_description' => 'YT Shorts Desc', 'hashtags' => ['#shorts']],
            ]
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Test AI Campaign']);
        $this->assertCount(3, $post->postPlatforms);

        // Execute publication
        $service->publishPostNow($post);

        $post->refresh();
        $this->assertDatabaseHas('post_platforms', [
            'post_id' => $post->id,
            'platform_key' => 'instagram',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('post_platforms', [
            'post_id' => $post->id,
            'platform_key' => 'tiktok',
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('post_platforms', [
            'post_id' => $post->id,
            'platform_key' => 'youtube',
            'status' => 'published',
        ]);
    }
}

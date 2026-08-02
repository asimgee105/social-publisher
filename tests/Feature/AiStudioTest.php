<?php

namespace Tests\Feature;

use App\Services\Ai\AiContentStudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_studio_generates_tailored_platform_copy(): void
    {
        $service = app(AiContentStudioService::class);

        $results = $service->generateForPlatforms(
            'Productivity tips for remote engineers in 2026',
            ['instagram', 'youtube', 'linkedin']
        );

        $this->assertArrayHasKey('instagram', $results);
        $this->assertArrayHasKey('youtube', $results);
        $this->assertArrayHasKey('linkedin', $results);

        $this->assertNotEmpty($results['instagram']['caption']);
        $this->assertNotEmpty($results['youtube']['youtube_title']);
        $this->assertNotEmpty($results['linkedin']['caption']);
    }
}

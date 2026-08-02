<?php

namespace App\Services\Ai;

use App\Models\AiProvider;
use App\Services\Ai\Contracts\AiProviderInterface;
use App\Services\Ai\Providers\GeminiProvider;
use App\Services\Ai\Providers\OpenAiProvider;

class AiContentStudioService
{
    public function getActiveProvider(): AiProviderInterface
    {
        $active = AiProvider::where('is_active', true)->first();
        if ($active && $active->provider_key === 'openai') {
            return app(OpenAiProvider::class);
        }

        return app(GeminiProvider::class);
    }

    public function generateForPlatforms(string $topic, array $platforms, array $options = []): array
    {
        $provider = $this->getActiveProvider();
        $results = [];

        foreach ($platforms as $platform) {
            $results[$platform] = $provider->generatePlatformContent($topic, $platform, $options);
        }

        return $results;
    }
}

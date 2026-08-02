<?php

namespace App\Services\Ai\Providers;

use App\Models\AiProvider;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;

class OpenAiProvider implements AiProviderInterface
{
    public function generatePlatformContent(string $topic, string $platform, array $options = []): array
    {
        $dbProvider = AiProvider::where('provider_key', 'openai')->first();
        $apiKey = $dbProvider?->api_key ?? config('services.openai.key');

        if (!$apiKey) {
            return (new GeminiProvider())->generatePlatformContent($topic, $platform, $options);
        }

        try {
            $response = Http::withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $dbProvider?->model_name ?? 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a social media copywriter. Output JSON with keys: hook, caption, youtube_title, youtube_description, hashtags.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Generate {$platform} content for topic: {$topic}"
                    ]
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $parsed = json_decode($content, true);
                if (is_array($parsed)) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return (new GeminiProvider())->generatePlatformContent($topic, $platform, $options);
    }
}

<?php

namespace App\Services\Ai\Providers;

use App\Models\AiProvider;
use App\Services\Ai\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements AiProviderInterface
{
    public function generatePlatformContent(string $topic, string $platform, array $options = []): array
    {
        $dbProvider = AiProvider::where('provider_key', 'gemini')->first();
        $apiKey = $dbProvider?->api_key ?? config('services.gemini.key');
        $model = $dbProvider?->model_name ?? 'gemini-2.0-flash';

        if (!$apiKey) {
            return $this->fallbackContent($topic, $platform);
        }

        $prompt = $this->buildPrompt($topic, $platform, $options);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $dbProvider?->temperature ?? 0.7,
                    'responseMimeType' => 'application/json',
                ]
            ]);

            if ($response->successful()) {
                $text = $response->json('candidates.0.content.parts.0.text');
                $parsed = json_decode($text, true);
                if (is_array($parsed)) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            // Fallback on exception
        }

        return $this->fallbackContent($topic, $platform);
    }

    protected function buildPrompt(string $topic, string $platform, array $options = []): string
    {
        $tone = $options['tone'] ?? 'engaging and viral';

        return "You are an expert social media strategist. Generate platform-tailored content for '{$platform}' based on the topic: '{$topic}'. Tone: {$tone}.

Return ONLY valid JSON matching this exact structure:
{
    \"hook\": \"Short catchy hook\",
    \"caption\": \"Engaging main caption with line breaks\",
    \"youtube_title\": \"YouTube title (if youtube, else null)\",
    \"youtube_description\": \"YouTube description (if youtube, else null)\",
    \"hashtags\": [\"#tag1\", \"#tag2\", \"#tag3\", \"#tag4\", \"#tag5\"]
}";
    }

    protected function fallbackContent(string $topic, string $platform): array
    {
        $cleanTopic = ucfirst(trim($topic));
        return match (strtolower($platform)) {
            'instagram' => [
                'hook' => "🔥 Stop scrolling! You need to know this about {$cleanTopic}",
                'caption' => "Here is everything you need to know about {$cleanTopic}!\n\nSave this reel for later and share with a friend who needs to see this! 👇",
                'youtube_title' => null,
                'youtube_description' => null,
                'hashtags' => ['#' . str_replace(' ', '', $cleanTopic), '#Reels', '#Trending', '#ContentCreator', '#ViralShorts'],
            ],
            'tiktok' => [
                'hook' => "Wait till the end for the secret to {$cleanTopic} 🤫",
                'caption' => "Why nobody talks about {$cleanTopic}... Comment your thoughts below! 💬",
                'youtube_title' => null,
                'youtube_description' => null,
                'hashtags' => ['#' . str_replace(' ', '', $cleanTopic), '#FYP', '#TikTokTech', '#Viral', '#LearnOnTikTok'],
            ],
            'youtube' => [
                'hook' => "The Ultimate Guide to {$cleanTopic}",
                'caption' => "Discover the truth about {$cleanTopic} in this short breakdown!",
                'youtube_title' => "How to Master {$cleanTopic} (Full Breakdown)",
                'youtube_description' => "In this video, we cover the essentials of {$cleanTopic}.\n\nSubscribe for daily updates & tutorials!\n\n#Shorts #{$cleanTopic}",
                'hashtags' => ['#' . str_replace(' ', '', $cleanTopic), '#Shorts', '#YouTubeShorts', '#Tutorial', '#Tips'],
            ],
            'facebook' => [
                'hook' => "Did you know this about {$cleanTopic}?",
                'caption' => "{$cleanTopic} is taking over right now. Here is what it means for you!\n\nLike and follow our page for more updates.",
                'youtube_title' => null,
                'youtube_description' => null,
                'hashtags' => ['#' . str_replace(' ', '', $cleanTopic), '#FacebookReels', '#Community', '#Trends'],
            ],
            'linkedin' => [
                'hook' => "3 Key Lessons I Learned About {$cleanTopic}",
                'caption' => "{$cleanTopic} is transforming the industry standard.\n\nHere are 3 actionable takeaways:\n1. Focus on core value\n2. Automate repetitive workflows\n3. Measure real-world outcomes\n\nWhat is your strategy for {$cleanTopic} this quarter?",
                'youtube_title' => null,
                'youtube_description' => null,
                'hashtags' => ['#' . str_replace(' ', '', $cleanTopic), '#Leadership', '#Innovation', '#Productivity', '#Strategy'],
            ],
            default => [
                'hook' => $cleanTopic,
                'caption' => "Check out our latest update on {$cleanTopic}.",
                'youtube_title' => null,
                'youtube_description' => null,
                'hashtags' => ['#' . str_replace(' ', '', $cleanTopic)],
            ]
        };
    }
}

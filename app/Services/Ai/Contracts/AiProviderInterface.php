<?php

namespace App\Services\Ai\Contracts;

interface AiProviderInterface
{
    public function generatePlatformContent(string $topic, string $platform, array $options = []): array;
}

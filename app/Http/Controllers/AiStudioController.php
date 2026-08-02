<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiContentStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiStudioController extends Controller
{
    public function __construct(
        protected AiContentStudioService $aiStudio
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'platforms' => 'required|array',
            'tone' => 'nullable|string',
        ]);

        $generated = $this->aiStudio->generateForPlatforms(
            $validated['topic'],
            $validated['platforms'],
            ['tone' => $validated['tone'] ?? 'engaging']
        );

        return response()->json([
            'success' => true,
            'data' => $generated,
        ]);
    }
}

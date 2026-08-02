<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Services\Media\MediaProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(
        protected MediaProcessingService $mediaService
    ) {}

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,webm,qt|max:512000', // max 500MB
        ]);

        $mediaAsset = $this->mediaService->processUpload(
            $request->file('video'),
            $request->user()?->id
        );

        return response()->json([
            'success' => true,
            'media' => $mediaAsset->load('variants'),
        ]);
    }
}

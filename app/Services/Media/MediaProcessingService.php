<?php

namespace App\Services\Media;

use App\Models\MediaAsset;
use App\Models\MediaVariant;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaProcessingService
{
    public function processUpload(UploadedFile $file, ?int $userId = null): MediaAsset
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('media/originals', $filename, 'public');

        $fullPath = storage_path('app/public/' . $path);

        $metadata = $this->extractMetadata($fullPath);
        $thumbnailPath = $this->generateThumbnail($fullPath, $filename);

        $asset = MediaAsset::create([
            'user_id' => $userId,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: 'video/mp4',
            'file_size' => $file->getSize(),
            'duration' => $metadata['duration'],
            'width' => $metadata['width'],
            'height' => $metadata['height'],
            'aspect_ratio' => $metadata['aspect_ratio'],
            'codec' => $metadata['codec'],
            'frame_rate' => $metadata['frame_rate'],
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
        ]);

        // Generate default variant reference
        MediaVariant::create([
            'media_asset_id' => $asset->id,
            'aspect_ratio' => $asset->aspect_ratio,
            'path' => $path,
            'status' => 'ready',
        ]);

        return $asset;
    }

    protected function extractMetadata(string $fullPath): array
    {
        $ffprobeBinary = Setting::get('ffprobe_path', 'ffprobe');
        $default = [
            'duration' => 15.0,
            'width' => 1080,
            'height' => 1920,
            'aspect_ratio' => '9:16',
            'codec' => 'h264',
            'frame_rate' => 30.0,
        ];

        if (!file_exists($fullPath)) {
            return $default;
        }

        try {
            $cmd = "{$ffprobeBinary} -v error -select_streams v:0 -show_entries stream=width,height,codec_name,r_frame_rate:format=duration -of json \"{$fullPath}\"";
            $output = @shell_exec($cmd);
            if ($output) {
                $json = json_decode($output, true);
                $stream = $json['streams'][0] ?? [];
                $format = $json['format'] ?? [];

                $width = (int) ($stream['width'] ?? 1080);
                $height = (int) ($stream['height'] ?? 1920);
                $duration = (float) ($format['duration'] ?? 15.0);
                $codec = $stream['codec_name'] ?? 'h264';

                $aspectRatio = '9:16';
                if ($width > 0 && $height > 0) {
                    $ratio = $width / $height;
                    if ($ratio >= 1.5) {
                        $aspectRatio = '16:9';
                    } elseif ($ratio >= 0.9 && $ratio <= 1.1) {
                        $aspectRatio = '1:1';
                    }
                }

                return [
                    'duration' => $duration,
                    'width' => $width,
                    'height' => $height,
                    'aspect_ratio' => $aspectRatio,
                    'codec' => $codec,
                    'frame_rate' => 30.0,
                ];
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return $default;
    }

    protected function generateThumbnail(string $videoPath, string $filename): ?string
    {
        $ffmpegBinary = Setting::get('ffmpeg_path', 'ffmpeg');
        $thumbFilename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
        $thumbRelPath = 'media/thumbnails/' . $thumbFilename;
        $thumbFullPath = storage_path('app/public/' . $thumbRelPath);

        if (!file_exists(dirname($thumbFullPath))) {
            @mkdir(dirname($thumbFullPath), 0755, true);
        }

        try {
            $cmd = "{$ffmpegBinary} -y -ss 00:00:01 -i \"{$videoPath}\" -vframes 1 -q:v 2 \"{$thumbFullPath}\"";
            @shell_exec($cmd);
            if (file_exists($thumbFullPath)) {
                return $thumbRelPath;
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return null;
    }
}

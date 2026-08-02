<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use App\Models\Setting;
use App\Models\SocialPlatform;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@socialpublisher.local'],
            [
                'name' => 'Asim Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Social Platforms
        $platforms = [
            [
                'key' => 'instagram',
                'name' => 'Instagram',
                'icon' => 'instagram',
                'supports_scheduling' => true,
                'supports_direct_publish' => true,
                'supports_draft_upload' => false,
                'supports_video' => true,
                'supports_image' => true,
            ],
            [
                'key' => 'facebook',
                'name' => 'Facebook',
                'icon' => 'facebook',
                'supports_scheduling' => true,
                'supports_direct_publish' => true,
                'supports_draft_upload' => false,
                'supports_video' => true,
                'supports_image' => true,
            ],
            [
                'key' => 'tiktok',
                'name' => 'TikTok',
                'icon' => 'video',
                'supports_scheduling' => true,
                'supports_direct_publish' => true,
                'supports_draft_upload' => true,
                'supports_video' => true,
                'supports_image' => false,
            ],
            [
                'key' => 'youtube',
                'name' => 'YouTube',
                'icon' => 'youtube',
                'supports_scheduling' => true,
                'supports_direct_publish' => true,
                'supports_draft_upload' => false,
                'supports_video' => true,
                'supports_image' => false,
            ],
            [
                'key' => 'linkedin',
                'name' => 'LinkedIn',
                'icon' => 'linkedin',
                'supports_scheduling' => true,
                'supports_direct_publish' => true,
                'supports_draft_upload' => false,
                'supports_video' => true,
                'supports_image' => true,
            ],
        ];

        foreach ($platforms as $platform) {
            SocialPlatform::updateOrCreate(['key' => $platform['key']], $platform);
        }

        // AI Providers
        AiProvider::updateOrCreate(
            ['provider_key' => 'gemini'],
            [
                'name' => 'Google Gemini',
                'model_name' => 'gemini-2.0-flash',
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'is_active' => true,
            ]
        );

        AiProvider::updateOrCreate(
            ['provider_key' => 'openai'],
            [
                'name' => 'OpenAI',
                'model_name' => 'gpt-4o-mini',
                'temperature' => 0.7,
                'max_tokens' => 1000,
                'is_active' => false,
            ]
        );

        // System Settings
        Setting::set('app_name', 'Asim Social Publisher', 'string');
        Setting::set('timezone', 'Asia/Karachi', 'string');
        Setting::set('developer_mode', true, 'boolean');
        Setting::set('auto_approve_ai', false, 'boolean');
        Setting::set('ffmpeg_path', 'ffmpeg', 'string');
        Setting::set('ffprobe_path', 'ffprobe', 'string');
    }
}

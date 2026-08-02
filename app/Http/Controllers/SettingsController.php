<?php

namespace App\Http\Controllers;

use App\Models\AiProvider;
use App\Models\ApiCredential;
use App\Models\PublishAttempt;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\SystemLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $settings = [
            'app_name' => Setting::get('app_name', 'Asim Social Publisher'),
            'timezone' => Setting::get('timezone', 'Asia/Karachi'),
            'developer_mode' => Setting::get('developer_mode', true),
            'auto_approve_ai' => Setting::get('auto_approve_ai', false),
            'ffmpeg_path' => Setting::get('ffmpeg_path', 'ffmpeg'),
            'ffprobe_path' => Setting::get('ffprobe_path', 'ffprobe'),
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string',
            'developer_mode' => 'required|boolean',
            'auto_approve_ai' => 'required|boolean',
            'ffmpeg_path' => 'required|string',
            'ffprobe_path' => 'required|string',
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, $val, is_bool($val) ? 'boolean' : 'string');
        }

        return back()->with('success', 'System Settings saved.');
    }

    public function redirectUrls(): Response
    {
        $baseUrl = url('/');

        $redirectUrls = [
            'instagram' => "{$baseUrl}/oauth/instagram/callback",
            'facebook' => "{$baseUrl}/oauth/facebook/callback",
            'tiktok' => "{$baseUrl}/oauth/tiktok/callback",
            'youtube' => "{$baseUrl}/oauth/youtube/callback",
            'linkedin' => "{$baseUrl}/oauth/linkedin/callback",
        ];

        return Inertia::render('Settings/OAuthRedirects', [
            'redirectUrls' => $redirectUrls,
            'baseUrl' => $baseUrl,
        ]);
    }

    public function apiHealth(): Response
    {
        $platforms = ['instagram', 'facebook', 'tiktok', 'youtube', 'linkedin'];
        $healthMatrix = [];

        foreach ($platforms as $p) {
            $credential = ApiCredential::where('platform', $p)->first();
            $account = SocialAccount::where('platform', $p)->first();
            $lastAttempt = PublishAttempt::whereHas('postPlatform', function ($q) use ($p) {
                $q->where('platform_key', $p);
            })->latest('attempted_at')->first();

            $healthMatrix[] = [
                'platform' => ucfirst($p),
                'key' => $p,
                'has_credentials' => (bool) ($credential && $credential->client_id),
                'has_connected_account' => (bool) ($account && $account->status === 'active'),
                'account_name' => $account?->account_name,
                'token_status' => $account?->status ?? 'not_connected',
                'last_success_at' => $account?->last_synced_at,
                'last_error' => $lastAttempt?->error_message,
            ];
        }

        $gemini = AiProvider::where('provider_key', 'gemini')->first();

        return Inertia::render('Settings/ApiHealth', [
            'healthMatrix' => $healthMatrix,
            'geminiStatus' => [
                'has_key' => !empty($gemini?->api_key),
                'is_active' => (bool) $gemini?->is_active,
                'model' => $gemini?->model_name ?? 'gemini-2.0-flash',
            ],
            'systemLogs' => SystemLog::latest()->take(20)->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AiProvider;
use App\Models\ApiCredential;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiConfigurationController extends Controller
{
    public function index(): Response
    {
        $credentials = ApiCredential::all()->map(function ($cred) {
            return [
                'id' => $cred->id,
                'platform' => $cred->platform,
                'client_id' => $cred->client_id,
                'client_secret_masked' => $cred->client_secret ? '••••••••' . substr($cred->client_secret, -4) : '',
                'redirect_uri' => $cred->redirect_uri,
                'scopes' => $cred->scopes,
                'extra_config' => $cred->extra_config,
                'is_active' => $cred->is_active,
            ];
        });

        $aiProviders = AiProvider::all()->map(function ($ai) {
            return [
                'id' => $ai->id,
                'provider_key' => $ai->provider_key,
                'name' => $ai->name,
                'api_key_masked' => $ai->api_key ? '••••••••' . substr($ai->api_key, -4) : '',
                'model_name' => $ai->model_name,
                'temperature' => $ai->temperature,
                'max_tokens' => $ai->max_tokens,
                'is_active' => $ai->is_active,
            ];
        });

        return Inertia::render('Settings/ApiConfiguration', [
            'credentials' => $credentials,
            'aiProviders' => $aiProviders,
        ]);
    }

    public function updateCredential(Request $request, string $platform): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'nullable|string',
            'redirect_uri' => 'nullable|string',
            'scopes' => 'nullable|array',
            'extra_config' => 'nullable|array',
        ]);

        $cred = ApiCredential::firstOrNew(['platform' => $platform]);
        $cred->client_id = $validated['client_id'];

        if (!empty($validated['client_secret'])) {
            $cred->client_secret = $validated['client_secret'];
        }

        $cred->redirect_uri = $validated['redirect_uri'] ?? route('oauth.callback', ['platform' => $platform]);
        $cred->scopes = $validated['scopes'] ?? [];
        $cred->extra_config = $validated['extra_config'] ?? [];
        $cred->is_active = true;
        $cred->save();

        return back()->with('success', ucfirst($platform) . ' API Credentials saved successfully!');
    }

    public function updateAiProvider(Request $request, string $providerKey): RedirectResponse
    {
        $validated = $request->validate([
            'api_key' => 'nullable|string',
            'model_name' => 'required|string',
            'temperature' => 'required|numeric',
            'is_active' => 'required|boolean',
        ]);

        $ai = AiProvider::firstOrNew(['provider_key' => $providerKey]);
        if (!empty($validated['api_key'])) {
            $ai->api_key = $validated['api_key'];
        }

        $ai->model_name = $validated['model_name'];
        $ai->temperature = $validated['temperature'];

        if ($validated['is_active']) {
            AiProvider::where('id', '!=', $ai->id)->update(['is_active' => false]);
            $ai->is_active = true;
        } else {
            $ai->is_active = false;
        }

        $ai->save();

        return back()->with('success', 'AI Provider settings updated.');
    }
}

<?php

use App\Http\Controllers\AiStudioController;
use App\Http\Controllers\ApiConfigurationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SetupWizardController;
use App\Http\Controllers\SocialAccountController;
use Illuminate\Support\Facades\Route;

// Root URL displays nothing (404 Not Found) for privacy/security
Route::get('/', function () {
    abort(404);
});

// Social Admin Group
Route::prefix('social/admin')->group(function () {

    // Authentication Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // OAuth Callbacks
    Route::get('/oauth/{platform}/connect', [SocialAccountController::class, 'connect'])->name('oauth.connect');
    Route::get('/oauth/{platform}/callback', [OAuthController::class, 'callback'])->name('oauth.callback');

    // Protected Admin Panel Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Post Management & Wizard
        Route::resource('posts', PostController::class);
        Route::post('/posts/{post}/duplicate', [PostController::class, 'duplicate'])->name('posts.duplicate');
        Route::post('/post-platforms/{postPlatform}/retry', [PostController::class, 'retryPlatform'])->name('post-platforms.retry');

        // Calendar View
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

        // Media Upload API
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

        // AI Content Studio API
        Route::post('/ai/generate', [AiStudioController::class, 'generate'])->name('ai.generate');

        // Social Accounts Management
        Route::get('/settings/social-accounts', [SocialAccountController::class, 'index'])->name('social-accounts.index');
        Route::post('/social-accounts/{account}/test', [SocialAccountController::class, 'testConnection'])->name('social-accounts.test');
        Route::delete('/social-accounts/{account}', [SocialAccountController::class, 'disconnect'])->name('social-accounts.disconnect');

        // API & AI Configuration
        Route::get('/settings/api-config', [ApiConfigurationController::class, 'index'])->name('api-config.index');
        Route::post('/settings/api-config/{platform}', [ApiConfigurationController::class, 'updateCredential'])->name('api-config.update');
        Route::post('/settings/ai-config/{providerKey}', [ApiConfigurationController::class, 'updateAiProvider'])->name('ai-config.update');

        // System Settings & Health Matrix
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/redirect-urls', [SettingsController::class, 'redirectUrls'])->name('settings.redirect-urls');
        Route::get('/settings/api-health', [SettingsController::class, 'apiHealth'])->name('settings.api-health');

        // Setup Wizard
        Route::get('/wizard', [SetupWizardController::class, 'index'])->name('wizard.index');
    });
});

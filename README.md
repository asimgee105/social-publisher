# Asim Social Publisher (SocialFlow)

> **UPLOAD ONCE → CUSTOMIZE → APPROVE → SCHEDULE/PUBLISH EVERYWHERE**

A production-ready, secure, modular, local-first social media content publishing and scheduling platform for Instagram, Facebook, TikTok, YouTube, and LinkedIn.

---

## Key Features

- **Multi-Platform Provider Architecture**: Common `SocialPlatformProvider` interface with isolated adapters for Instagram (Meta Graph API), Facebook (Pages API), TikTok (Content Posting API), YouTube (Data API v3), and LinkedIn (UGC Posts API).
- **Secure Token Storage**: Application-level encryption (`encrypted` casts) for all access tokens, refresh tokens, and developer secrets. No raw credentials are exposed to frontend Vue assets, network payloads, or system logs.
- **AI Content Studio**: Abstraction supporting Google Gemini (`gemini-2.0-flash`) and OpenAI with fallback intelligent templates.
- **FFmpeg & Media Pipeline**: Automatic video metadata extraction (resolution, duration, aspect ratio) and thumbnail generation with graceful fallback.
- **Platform Visual Previews**: Authentic UI mock previews for Instagram Reels, TikTok, YouTube Shorts, Facebook Page Video, and LinkedIn Member Posts.
- **Queue & Scheduler Engine**: Platform-isolated Laravel Queues with exponential backoff retries for rate limits (429) & network timeouts.
- **Developer / Dry-Run Mode**: Test the complete application workflow locally without live social API keys.

---

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.4+), Laravel Queues, Scheduler, HTTP Client, Encrypted Casts, SQLite / MySQL.
- **Frontend**: Vue 3, Inertia.js, Tailwind CSS, Lucide Icons.
- **Media**: FFmpeg / FFprobe.
- **AI**: Google Gemini API, OpenAI API.

---

## Quick Start (Local Development)

```bash
# 1. Clone repository & install backend dependencies
composer install

# 2. Setup environment variables
cp .env.example .env
php artisan key:generate

# 3. Migrate and seed database
php artisan migrate --seed

# 4. Install frontend dependencies and build assets
npm install --legacy-peer-deps
npm run build

# 5. Start local server
php artisan serve
```

Access the dashboard at `http://127.0.0.1:8000`.

---

## Running Queue & Scheduler

```bash
# Run queue worker
php artisan queue:work

# Run scheduler (processes scheduled social posts every minute)
php artisan schedule:work
```

---

## Developer / Dry-Run Mode

Developer mode is enabled by default in **Settings → System Settings**. When active, post publications generate realistic mock platform responses so you can test uploading, AI generation, previewing, scheduling, queueing, and status monitoring locally.

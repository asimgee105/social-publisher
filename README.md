# Asim Social Publisher (SocialFlow)

> **Work in Progress — Independent Personal Project**

Asim Social Publisher is my personal project for exploring a secure, AI-assisted social media publishing and scheduling platform for Instagram, Facebook, TikTok, YouTube, and LinkedIn.

The project is **not yet complete** and should be considered an active work in progress rather than a finished production SaaS product.

## Planned / In-Progress Features

- Multi-platform provider architecture
- Social account integrations
- AI-assisted content generation with Google Gemini and OpenAI
- Media processing with FFmpeg / FFprobe
- Platform-specific post previews
- Queue-based publishing and scheduling
- Retry handling for rate limits and network failures
- Secure encrypted storage for credentials and tokens
- Developer / dry-run mode for local testing

## Tech Stack

- **Backend:** Laravel 12, PHP 8.4+, Laravel Queues, Scheduler, HTTP Client
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Lucide Icons
- **Media:** FFmpeg / FFprobe
- **AI:** Google Gemini API, OpenAI API
- **Database:** SQLite / MySQL

## Development Status

- 🚧 Work in progress
- 🧪 Features are still being implemented and tested
- 🔄 Architecture and integrations may change during development
- 📌 Not presented as a completed production-ready application yet

## Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install --legacy-peer-deps
npm run build
php artisan serve
```

For background jobs and scheduling during local development:

```bash
php artisan queue:work
php artisan schedule:work
```

## Author

**Asim Ali**  
Laravel / PHP Backend Developer • Full-Stack Developer • AI Integrations

# Official API Setup Guide

This guide details how to configure developer app credentials and OAuth callbacks for each supported platform.

---

## 1. Meta (Instagram & Facebook)

- **Portal**: [Meta for Developers](https://developers.facebook.com/)
- **App Type**: Business App
- **Required Products**: Facebook Login for Business, Instagram Graph API
- **Permissions**: `pages_show_list`, `pages_read_engagement`, `pages_manage_posts`, `publish_video`, `instagram_basic`, `instagram_content_publish`
- **OAuth Callback URL**: `http://127.0.0.1:8000/oauth/meta/callback`
- **Admin Configuration**: Navigate to **Settings → API Configuration → Meta**. Enter App ID and App Secret.

---

## 2. TikTok

- **Portal**: [TikTok Developer Portal](https://developers.tiktok.com/)
- **App Type**: Content Posting API
- **Permissions / Scopes**: `user.info.basic`, `video.upload`, `video.publish`
- **OAuth Callback URL**: `http://127.0.0.1:8000/oauth/tiktok/callback`
- **Admin Configuration**: Navigate to **Settings → API Configuration → TikTok**. Enter Client Key and Client Secret.

---

## 3. YouTube (Google Cloud)

- **Portal**: [Google Cloud Console](https://console.cloud.google.com/)
- **API Enabled**: YouTube Data API v3
- **OAuth Credentials**: OAuth 2.0 Client ID (Web Application)
- **Scopes**: `https://www.googleapis.com/auth/youtube.upload`, `https://www.googleapis.com/auth/youtube.readonly`
- **OAuth Callback URL**: `http://127.0.0.1:8000/oauth/youtube/callback`
- **Admin Configuration**: Navigate to **Settings → API Configuration → Google**. Enter Client ID and Client Secret.

---

## 4. LinkedIn

- **Portal**: [LinkedIn Developer Portal](https://www.linkedin.com/developers/)
- **Products**: Share on LinkedIn, Sign In with LinkedIn using OpenID Connect
- **Permissions**: `openid`, `profile`, `w_member_social`
- **OAuth Callback URL**: `http://127.0.0.1:8000/oauth/linkedin/callback`
- **Admin Configuration**: Navigate to **Settings → API Configuration → LinkedIn**. Enter Client ID and Client Secret.

---

## 5. Google Gemini AI

- **Portal**: [Google AI Studio](https://aistudio.google.com/)
- **Admin Configuration**: Navigate to **Settings → API Configuration → AI Studio**. Enter Gemini API Key and select `gemini-2.0-flash`.

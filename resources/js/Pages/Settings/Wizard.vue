<template>
  <AdminLayout>
    <div class="max-w-3xl mx-auto space-y-8 pb-16">
      <div class="text-center space-y-2">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-pink-500 mx-auto flex items-center justify-center font-extrabold text-xl text-white shadow-xl shadow-indigo-500/30">
          A
        </div>
        <h2 class="text-3xl font-extrabold text-slate-100 tracking-tight">Welcome to Asim Social Publisher</h2>
        <p class="text-xs text-slate-400">Step-by-step checklist to get your local-first multi-platform publishing platform fully operational.</p>
      </div>

      <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-8 space-y-6">
        <div v-for="(wizStep, idx) in wizardSteps" :key="wizStep.title" class="flex items-start gap-4 p-4 rounded-2xl bg-slate-950 border border-slate-800/80">
          <div class="w-7 h-7 rounded-full bg-indigo-600/20 border border-indigo-500/40 text-indigo-400 font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
            {{ idx + 1 }}
          </div>

          <div class="space-y-1 flex-1">
            <h4 class="font-bold text-sm text-slate-100">{{ wizStep.title }}</h4>
            <p class="text-xs text-slate-400 leading-relaxed">{{ wizStep.description }}</p>
            <div class="pt-2">
              <Link :href="wizStep.link" class="text-xs font-semibold text-indigo-400 hover:underline">Configure {{ wizStep.title }} →</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
  appUrl: String,
  developerMode: Boolean,
})

const wizardSteps = [
  { title: 'Database & Key Setup', description: 'SQLite database is configured at database/database.sqlite with encrypted token keys.', link: '/social/admin/settings' },
  { title: 'FFmpeg & Media Pipeline', description: 'Ensure FFmpeg & FFprobe are accessible or set their custom binary paths.', link: '/social/admin/settings' },
  { title: 'AI Content Studio', description: 'Enter your Google Gemini API key or use fallback intelligent templates.', link: '/social/admin/settings/api-config' },
  { title: 'Developer Credentials', description: 'Configure Meta, TikTok, Google, and LinkedIn App IDs and Client Secrets.', link: '/social/admin/settings/api-config' },
  { title: 'OAuth Redirect URIs', description: 'Copy callback URLs into your social developer app portals.', link: '/social/admin/settings/redirect-urls' },
  { title: 'Connect Accounts', description: 'Authorize your Instagram, Facebook, TikTok, YouTube, and LinkedIn accounts.', link: '/social/admin/settings/social-accounts' },
  { title: 'Upload & Test Run', description: 'Upload a video, generate AI captions, preview platforms, and run dry-run tests.', link: '/social/admin/posts/create' },
]
</script>

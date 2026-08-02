<template>
  <AdminLayout>
    <div class="space-y-6 max-w-4xl mx-auto">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">OAuth Redirect URLs</h2>
        <p class="text-xs text-slate-400">Copy these exact callback URLs into your developer portal app settings (Meta, TikTok, Google Console, LinkedIn).</p>
      </div>

      <div class="space-y-4">
        <div v-for="(url, pf) in redirectUrls" :key="pf" class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 flex items-center justify-between gap-4">
          <div class="space-y-1">
            <PlatformBadge :platform="pf" />
            <p class="text-xs font-mono text-slate-200 select-all">{{ url }}</p>
          </div>

          <button @click="copyToClipboard(url)" class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-indigo-300 transition flex items-center gap-1.5">
            <CopyIcon class="w-3.5 h-3.5" />
            Copy URL
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PlatformBadge from '@/Components/PlatformBadge.vue'
import { CopyIcon } from 'lucide-vue-next'

defineProps({
  redirectUrls: Object,
  baseUrl: String,
})

function copyToClipboard(url) {
  navigator.clipboard.writeText(url)
  alert('OAuth Callback URL copied to clipboard!')
}
</script>

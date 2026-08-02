<template>
  <AdminLayout>
    <div class="space-y-8 max-w-4xl mx-auto">
      <div class="flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <h2 class="text-2xl font-bold text-slate-100">{{ post.title }}</h2>
            <StatusBadge :status="post.status" />
          </div>
          <p class="text-xs text-slate-400">Created: {{ new Date(post.created_at).toLocaleString() }} • Timezone: {{ post.timezone }}</p>
        </div>

        <div class="flex items-center gap-3">
          <button @click="duplicatePost" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200">
            Duplicate Post
          </button>
        </div>
      </div>

      <!-- Media & Core Details -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-slate-900/60 border border-slate-800 p-6 rounded-3xl">
        <div class="aspect-[9/16] bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center">
          <video v-if="post.media_asset?.path" :src="'/storage/' + post.media_asset.path" class="w-full h-full object-cover" controls></video>
          <span v-else class="text-xs text-slate-600">No Video</span>
        </div>

        <div class="md:col-span-2 space-y-4">
          <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Base Content Topic</h4>
          <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800/80 text-xs text-slate-200 leading-relaxed whitespace-pre-line">
            {{ post.base_content }}
          </div>
        </div>
      </div>

      <!-- Per-Platform Status Breakdown -->
      <div class="space-y-4">
        <h3 class="font-bold text-lg text-slate-200">Platform Publishing Status</h3>

        <div class="space-y-4">
          <div v-for="pf in post.post_platforms" :key="pf.id" class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <PlatformBadge :platform="pf.platform_key" />
                <span class="text-xs text-slate-400">Account: @{{ pf.social_account?.account_name || 'Account' }}</span>
              </div>

              <div class="flex items-center gap-3">
                <StatusBadge :status="pf.status" />
                <button v-if="pf.status === 'failed'" @click="retryPlatform(pf.id)"
                        class="px-3 py-1 rounded-lg bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold hover:bg-rose-500/30 transition">
                  Retry Publishing
                </button>
              </div>
            </div>

            <!-- Error message if failed -->
            <div v-if="pf.error_message" class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
              <p class="font-bold">Error Code: {{ pf.error_code || '400' }}</p>
              <p>{{ pf.error_message }}</p>
            </div>

            <!-- Published URL if available -->
            <div v-if="pf.platform_post_url" class="text-xs text-emerald-400">
              Published URL: <a :href="pf.platform_post_url" target="_blank" class="underline font-mono">{{ pf.platform_post_url }}</a>
            </div>

            <!-- Platform content summary -->
            <div v-if="pf.post_content" class="p-3 bg-slate-950 rounded-xl border border-slate-800/60 text-xs text-slate-300">
              <p class="font-bold text-slate-400 mb-1" v-if="pf.post_content.hook">Hook: {{ pf.post_content.hook }}</p>
              <p class="line-clamp-2">{{ pf.post_content.caption }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PlatformBadge from '@/Components/PlatformBadge.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  post: Object
})

function retryPlatform(id) {
  router.post(`/social/admin/post-platforms/${id}/retry`)
}

function duplicatePost() {
  router.post(`/social/admin/posts/${props.post.id}/duplicate`)
}
</script>

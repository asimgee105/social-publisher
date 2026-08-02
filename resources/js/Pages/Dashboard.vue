<template>
  <AdminLayout>
    <div class="space-y-8">
      <!-- Title & CTA -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-100 tracking-tight">Publisher Control Center</h2>
          <p class="text-sm text-slate-400">Upload once, customize with AI, and publish to all 5 platforms simultaneously.</p>
        </div>

        <Link href="/social/admin/posts/create" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white font-semibold shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 transition transform hover:-translate-y-0.5">
          <PlusCircleIcon class="w-5 h-5" />
          Create New Multi-Platform Post
        </Link>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
          <span class="text-xs text-slate-400 font-medium">Uploaded Videos</span>
          <p class="text-2xl font-extrabold text-slate-100">{{ stats.uploaded_videos }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
          <span class="text-xs text-slate-400 font-medium">Scheduled Posts</span>
          <p class="text-2xl font-extrabold text-amber-400">{{ stats.scheduled_posts }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
          <span class="text-xs text-slate-400 font-medium">Published Success</span>
          <p class="text-2xl font-extrabold text-emerald-400">{{ stats.published_posts }}</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 space-y-1">
          <span class="text-xs text-slate-400 font-medium">Failed / Errors</span>
          <p class="text-2xl font-extrabold text-rose-400">{{ stats.failed_posts }}</p>
        </div>
      </div>

      <!-- Connected Accounts Overview Cards -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-bold text-lg text-slate-200">Connected Accounts</h3>
          <Link href="/social/admin/settings/social-accounts" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">Manage Accounts →</Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
          <div v-for="acc in accounts" :key="acc.id" class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/80 flex items-center justify-between">
            <div>
              <PlatformBadge :platform="acc.platform" class="mb-1" />
              <p class="text-xs font-bold text-slate-200 truncate max-w-[110px]">{{ acc.account_name }}</p>
            </div>
            <StatusBadge :status="acc.status" />
          </div>
          <div v-if="accounts.length === 0" class="col-span-full p-6 rounded-2xl bg-slate-900/20 border border-dashed border-slate-800 text-center text-slate-500 text-xs">
            No accounts connected yet. Go to <Link href="/social/admin/settings/social-accounts" class="text-indigo-400 underline">Social Accounts</Link> to connect or test accounts.
          </div>
        </div>
      </div>

      <!-- Recent Publications List -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <h3 class="font-bold text-lg text-slate-200">Recent Content</h3>
          <Link href="/social/admin/posts" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View Content Library →</Link>
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden divide-y divide-slate-800/60">
          <div v-for="post in recentPosts" :key="post.id" class="p-4 flex items-center justify-between hover:bg-slate-800/30 transition">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center font-bold text-indigo-400 text-xs overflow-hidden">
                <img v-if="post.media_asset?.thumbnail_path" :src="'/storage/' + post.media_asset.thumbnail_path" class="w-full h-full object-cover" />
                <span v-else>VID</span>
              </div>
              <div>
                <h4 class="font-bold text-sm text-slate-100">{{ post.title }}</h4>
                <p class="text-xs text-slate-400 line-clamp-1 max-w-md">{{ post.base_content }}</p>
                <div class="flex items-center gap-2 mt-1.5">
                  <PlatformBadge v-for="pf in post.post_platforms" :key="pf.id" :platform="pf.platform_key" />
                </div>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <StatusBadge :status="post.status" />
              <Link :href="'/social/admin/posts/' + post.id" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-medium text-slate-300 transition">
                Details
              </Link>
            </div>
          </div>

          <div v-if="recentPosts.length === 0" class="p-8 text-center text-slate-500 text-xs">
            No posts created yet. Click "Create New Multi-Platform Post" to start.
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
import { Link } from '@inertiajs/vue3'
import { PlusCircleIcon } from 'lucide-vue-next'

defineProps({
  stats: Object,
  accounts: Array,
  recentPosts: Array,
  todayScheduled: Array,
})
</script>

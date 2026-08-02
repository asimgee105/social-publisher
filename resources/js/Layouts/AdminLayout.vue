<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans">
    <!-- Top Nav / Header -->
    <header class="bg-slate-900/80 backdrop-blur border-b border-slate-800 sticky top-0 z-40 px-4 lg:px-8 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <Link href="/social/admin" class="flex items-center gap-2 group">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 font-extrabold text-lg">
            A
          </div>
          <div>
            <h1 class="font-bold text-slate-100 tracking-tight leading-none group-hover:text-indigo-400 transition">Asim Social Publisher</h1>
            <span class="text-xs text-slate-400 font-mono">SocialFlow v2.0 • Local-First</span>
          </div>
        </Link>
      </div>

      <div class="flex items-center gap-4">
        <!-- Dev Mode Indicator -->
        <div v-if="$page.props.stats?.developer_mode" class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold flex items-center gap-1.5 animate-pulse">
          <span class="w-2 h-2 rounded-full bg-amber-400"></span>
          Developer / Dry-Run Mode Active
        </div>

        <Link href="/social/admin/posts/create" class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-medium text-sm shadow-md shadow-indigo-500/20 flex items-center gap-2 transition transform active:scale-95">
          <PlusCircleIcon class="w-4 h-4" />
          Create New Post
        </Link>

        <!-- User / Logout -->
        <div class="flex items-center gap-3 border-l border-slate-800 pl-4">
          <span class="text-xs font-semibold text-slate-300">{{ $page.props.auth?.user?.name || 'Admin' }}</span>
          <Link href="/social/admin/logout" method="post" as="button" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-rose-400 transition" title="Log Out">
            <LogOutIcon class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </header>

    <div class="flex-1 flex">
      <!-- Sidebar -->
      <aside class="w-64 bg-slate-900/50 border-r border-slate-800 p-4 hidden md:flex flex-col gap-6">
        <nav class="space-y-1">
          <Link v-for="item in navItems" :key="item.name" :href="item.href"
                :class="[
                  $page.url === item.href || ($page.url.startsWith(item.href) && item.href !== '/social/admin')
                    ? 'bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-semibold'
                    : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50',
                  'px-3.5 py-2.5 rounded-lg text-sm flex items-center gap-3 transition'
                ]">
            <component :is="item.icon" class="w-4 h-4" />
            {{ item.name }}
          </Link>
        </nav>

        <div class="mt-auto pt-4 border-t border-slate-800 text-xs text-slate-500 space-y-2">
          <div class="flex items-center justify-between">
            <span>System Status</span>
            <span class="text-emerald-400 flex items-center gap-1">● Online</span>
          </div>
          <p>api.superdollarsahiwal.com</p>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full overflow-x-hidden">
        <!-- Flash notifications -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between">
          <span>{{ $page.props.flash.success }}</span>
        </div>
        <div v-if="$page.props.flash?.error" class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between">
          <span>{{ $page.props.flash.error }}</span>
        </div>

        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
  LayoutDashboardIcon,
  VideoIcon,
  CalendarIcon,
  Share2Icon,
  KeyIcon,
  ActivityIcon,
  SettingsIcon,
  PlusCircleIcon,
  SlidersIcon,
  HelpCircleIcon,
  LogOutIcon
} from 'lucide-vue-next'

const navItems = [
  { name: 'Overview', href: '/social/admin', icon: LayoutDashboardIcon },
  { name: 'Create Post', href: '/social/admin/posts/create', icon: PlusCircleIcon },
  { name: 'Content Library', href: '/social/admin/posts', icon: VideoIcon },
  { name: 'Calendar', href: '/social/admin/calendar', icon: CalendarIcon },
  { name: 'Social Accounts', href: '/social/admin/settings/social-accounts', icon: Share2Icon },
  { name: 'API Configuration', href: '/social/admin/settings/api-config', icon: KeyIcon },
  { name: 'OAuth Redirects', href: '/social/admin/settings/redirect-urls', icon: SlidersIcon },
  { name: 'API Health', href: '/social/admin/settings/api-health', icon: ActivityIcon },
  { name: 'System Settings', href: '/social/admin/settings', icon: SettingsIcon },
  { name: 'First-Run Wizard', href: '/social/admin/wizard', icon: HelpCircleIcon },
]
</script>

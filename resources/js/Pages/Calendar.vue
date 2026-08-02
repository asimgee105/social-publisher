<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">Schedule Calendar</h2>
        <p class="text-xs text-slate-400">View upcoming scheduled multi-platform posts in your local timezone.</p>
      </div>

      <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="ev in events" :key="ev.id" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2">
            <div class="flex items-center justify-between">
              <PlatformBadge :platform="ev.post_platform?.platform_key || 'social'" />
              <StatusBadge :status="ev.status" />
            </div>

            <h4 class="font-bold text-xs text-slate-100 line-clamp-1">{{ ev.post?.title || 'Scheduled Post' }}</h4>
            <p class="text-[11px] text-indigo-400 font-mono">
              📅 {{ new Date(ev.scheduled_time_utc).toLocaleString() }}
            </p>
          </div>

          <div v-if="events.length === 0" class="col-span-full p-8 text-center text-slate-500 text-xs">
            No posts currently scheduled in calendar.
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

defineProps({
  events: Array
})
</script>

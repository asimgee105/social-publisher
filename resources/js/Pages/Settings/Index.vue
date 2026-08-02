<template>
  <AdminLayout>
    <div class="space-y-6 max-w-3xl mx-auto">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">System Settings</h2>
        <p class="text-xs text-slate-400">Configure global timezone, developer dry-run mode, and FFmpeg binaries.</p>
      </div>

      <div class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800 space-y-6">
        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Application Name</label>
          <input v-model="form.app_name" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1">Default Timezone</label>
          <select v-model="form.timezone" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100">
            <option value="Asia/Karachi">Asia/Karachi (PKT +05:00)</option>
            <option value="UTC">UTC (+00:00)</option>
            <option value="America/New_York">America/New_York (EST)</option>
            <option value="Europe/London">Europe/London (GMT)</option>
          </select>
        </div>

        <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-3">
          <label class="flex items-center justify-between text-xs font-semibold text-slate-200 cursor-pointer">
            <div>
              <span class="block">Developer / Dry-Run Mode</span>
              <span class="text-[11px] text-slate-400 font-normal">When active, publishing actions simulate success locally without calling live APIs.</span>
            </div>
            <input type="checkbox" v-model="form.developer_mode" class="rounded text-indigo-600 w-4 h-4" />
          </label>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">FFmpeg Binary Path</label>
            <input v-model="form.ffmpeg_path" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100 font-mono" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">FFprobe Binary Path</label>
            <input v-model="form.ffprobe_path" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100 font-mono" />
          </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-slate-800">
          <button @click="saveSettings" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs">
            Save System Settings
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  settings: Object,
})

const form = ref({
  app_name: props.settings.app_name,
  timezone: props.settings.timezone,
  developer_mode: props.settings.developer_mode,
  auto_approve_ai: props.settings.auto_approve_ai,
  ffmpeg_path: props.settings.ffmpeg_path,
  ffprobe_path: props.settings.ffprobe_path,
})

function saveSettings() {
  router.post('/social/admin/settings', form.value)
}
</script>

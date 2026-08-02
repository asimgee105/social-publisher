<template>
  <AdminLayout>
    <div class="space-y-8 max-w-5xl mx-auto">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">API Health & System Status</h2>
        <p class="text-xs text-slate-400">Diagnostic matrix showing credentials status, token state, and recent publication errors.</p>
      </div>

      <!-- Health Matrix Table -->
      <div class="bg-slate-900/60 border border-slate-800 rounded-3xl overflow-hidden">
        <table class="w-full text-left text-xs text-slate-300">
          <thead class="bg-slate-950/80 border-b border-slate-800 text-slate-400 uppercase font-semibold">
            <tr>
              <th class="p-4">Platform</th>
              <th class="p-4">API Credentials</th>
              <th class="p-4">Account Connected</th>
              <th class="p-4">Token Status</th>
              <th class="p-4">Last Error</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800/60">
            <tr v-for="item in healthMatrix" :key="item.key" class="hover:bg-slate-800/30">
              <td class="p-4 font-bold flex items-center gap-2">
                <PlatformBadge :platform="item.key" />
              </td>
              <td class="p-4">
                <span v-if="item.has_credentials" class="text-emerald-400 font-semibold">Configured ✅</span>
                <span v-else class="text-amber-400 font-semibold">Not Configured ⚠️</span>
              </td>
              <td class="p-4">
                <span v-if="item.has_connected_account" class="text-slate-200">@{{ item.account_name }}</span>
                <span v-else class="text-slate-500">None</span>
              </td>
              <td class="p-4">
                <StatusBadge :status="item.token_status" />
              </td>
              <td class="p-4 max-w-xs truncate text-rose-300">
                {{ item.last_error || 'None' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- System Logs Audit Stream -->
      <div class="space-y-3">
        <h3 class="font-bold text-sm text-slate-200">Recent System Activity Logs</h3>

        <div class="bg-slate-950 border border-slate-800 rounded-2xl p-4 font-mono text-xs text-slate-400 space-y-2 max-h-64 overflow-y-auto">
          <div v-for="log in systemLogs" :key="log.id" class="flex items-start gap-3 border-b border-slate-900 pb-1.5">
            <span class="text-slate-600">{{ new Date(log.created_at).toLocaleTimeString() }}</span>
            <span :class="[log.level === 'error' ? 'text-rose-400 font-bold' : 'text-indigo-400']">[{{ log.action }}]</span>
            <span class="text-slate-300">{{ log.message }}</span>
          </div>
          <div v-if="systemLogs.length === 0" class="text-slate-600">No system logs recorded yet.</div>
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
  healthMatrix: Array,
  geminiStatus: Object,
  systemLogs: Array,
})
</script>

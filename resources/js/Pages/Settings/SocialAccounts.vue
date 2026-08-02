<template>
  <AdminLayout>
    <div class="space-y-6 max-w-5xl mx-auto">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">Social Accounts</h2>
        <p class="text-xs text-slate-400">Connect and manage official OAuth platform authorizations. Tokens are securely encrypted.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="pf in availablePlatforms" :key="pf.key" class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800 space-y-4 flex flex-col justify-between">
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <PlatformBadge :platform="pf.key" />
              <StatusBadge :status="getAccount(pf.key) ? getAccount(pf.key).status : 'disconnected'" />
            </div>

            <div>
              <h3 class="font-bold text-sm text-slate-100">{{ pf.name }}</h3>
              <p class="text-xs text-slate-400" v-if="getAccount(pf.key)">
                Connected Account: <strong class="text-indigo-300">@{{ getAccount(pf.key).account_name }}</strong>
              </p>
              <p class="text-xs text-slate-500" v-else>No account connected for this platform yet.</p>
            </div>
          </div>

          <div class="pt-4 border-t border-slate-800 flex flex-wrap gap-2">
            <a v-if="!getAccount(pf.key) || getAccount(pf.key).status === 'needs_reauth'"
               :href="'/social/admin/oauth/' + pf.key + '/connect'"
               class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition inline-flex items-center gap-1.5">
              Connect {{ pf.name }}
            </a>

            <button v-if="getAccount(pf.key)" @click="testConnection(getAccount(pf.key).id)"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium text-xs transition">
              Test Connection
            </button>

            <button v-if="getAccount(pf.key)" @click="disconnect(getAccount(pf.key).id)"
                    class="px-3.5 py-2 rounded-xl bg-rose-500/20 text-rose-300 hover:bg-rose-500/30 font-medium text-xs transition">
              Disconnect
            </button>
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
  platforms: Array,
  accounts: Array,
})

const availablePlatforms = [
  { key: 'instagram', name: 'Instagram' },
  { key: 'facebook', name: 'Facebook' },
  { key: 'tiktok', name: 'TikTok' },
  { key: 'youtube', name: 'YouTube' },
  { key: 'linkedin', name: 'LinkedIn' },
]

function getAccount(platformKey) {
  return props.accounts?.find(a => a.platform === platformKey)
}

function testConnection(id) {
  router.post(`/social/admin/social-accounts/${id}/test`)
}

function disconnect(id) {
  if (confirm('Disconnect this account?')) {
    router.delete(`/social/admin/social-accounts/${id}`)
  }
}
</script>

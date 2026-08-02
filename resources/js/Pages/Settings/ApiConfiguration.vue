<template>
  <AdminLayout>
    <div class="space-y-8 max-w-4xl mx-auto">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">API & Developer App Configuration</h2>
        <p class="text-xs text-slate-400">Configure official OAuth Client IDs and App Secrets for each social platform without editing PHP code.</p>
      </div>

      <!-- AI Provider Configuration -->
      <div class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800 space-y-4">
        <h3 class="font-bold text-sm text-slate-100 flex items-center gap-2">
          <SparklesIcon class="w-4 h-4 text-indigo-400" />
          AI Content Studio Provider
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div v-for="ai in aiProviders" :key="ai.id" class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
              <span class="font-bold text-xs text-slate-200">{{ ai.name }}</span>
              <span v-if="ai.is_active" class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">Active Provider</span>
            </div>

            <div>
              <label class="block text-[11px] text-slate-400 mb-1">API Key</label>
              <input v-model="aiForm[ai.provider_key].api_key" type="password" :placeholder="ai.api_key_masked || 'Enter API Key'"
                     class="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-xs text-slate-100" />
            </div>

            <div>
              <label class="block text-[11px] text-slate-400 mb-1">Model Name</label>
              <input v-model="aiForm[ai.provider_key].model_name" type="text" class="w-full bg-slate-900 border border-slate-800 rounded-lg p-2 text-xs text-slate-100" />
            </div>

            <button @click="saveAiProvider(ai.provider_key)" class="w-full py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs">
              Save {{ ai.name }} Settings
            </button>
          </div>
        </div>
      </div>

      <!-- Developer Credentials Form per Platform -->
      <div class="space-y-6">
        <div v-for="pfKey in ['meta', 'tiktok', 'google', 'linkedin']" :key="pfKey" class="p-6 rounded-3xl bg-slate-900/60 border border-slate-800 space-y-4">
          <div class="flex items-center justify-between">
            <h3 class="font-bold text-sm text-slate-100 uppercase tracking-wider">{{ pfKey }} Developer App Credentials</h3>
            <PlatformBadge :platform="pfKey === 'meta' ? 'instagram' : (pfKey === 'google' ? 'youtube' : pfKey)" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">App ID / Client ID / Client Key</label>
              <input v-model="credForm[pfKey].client_id" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100" />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Client Secret</label>
              <input v-model="credForm[pfKey].client_secret" type="password" :placeholder="getCredMasked(pfKey) || 'Enter Client Secret'"
                     class="w-full bg-slate-950 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100" />
            </div>
          </div>

          <div class="flex justify-end">
            <button @click="saveCredential(pfKey)" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
              Save {{ pfKey.toUpperCase() }} Credentials
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
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { SparklesIcon } from 'lucide-vue-next'

const props = defineProps({
  credentials: Array,
  aiProviders: Array,
})

const aiForm = ref({
  gemini: {
    api_key: '',
    model_name: props.aiProviders?.find(a => a.provider_key === 'gemini')?.model_name || 'gemini-2.0-flash',
    temperature: 0.7,
    is_active: true,
  },
  openai: {
    api_key: '',
    model_name: props.aiProviders?.find(a => a.provider_key === 'openai')?.model_name || 'gpt-4o-mini',
    temperature: 0.7,
    is_active: false,
  }
})

const credForm = ref({
  meta: { client_id: getCred('meta')?.client_id || '', client_secret: '' },
  tiktok: { client_id: getCred('tiktok')?.client_id || '', client_secret: '' },
  google: { client_id: getCred('google')?.client_id || '', client_secret: '' },
  linkedin: { client_id: getCred('linkedin')?.client_id || '', client_secret: '' },
})

function getCred(pf) {
  return props.credentials?.find(c => c.platform === pf)
}

function getCredMasked(pf) {
  return getCred(pf)?.client_secret_masked
}

function saveCredential(pfKey) {
  router.post(`/social/admin/settings/api-config/${pfKey}`, credForm.value[pfKey])
}

function saveAiProvider(key) {
  router.post(`/social/admin/settings/ai-config/${key}`, aiForm.value[key])
}
</script>

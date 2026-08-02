<template>
  <span :class="[badgeStyle, 'px-2.5 py-1 rounded-md text-xs font-semibold inline-flex items-center gap-1.5']">
    <span>{{ platformName }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  platform: { type: String, required: true }
})

const platformName = computed(() => {
  return props.platform ? props.platform.charAt(0).toUpperCase() + props.platform.slice(1) : ''
})

const badgeStyle = computed(() => {
  switch (props.platform?.toLowerCase()) {
    case 'instagram':
      return 'bg-gradient-to-r from-purple-500/20 to-pink-500/20 text-pink-300 border border-pink-500/30'
    case 'facebook':
      return 'bg-blue-600/20 text-blue-300 border border-blue-500/30'
    case 'tiktok':
      return 'bg-slate-800 text-cyan-300 border border-cyan-500/30'
    case 'youtube':
      return 'bg-red-600/20 text-red-300 border border-red-500/30'
    case 'linkedin':
      return 'bg-sky-600/20 text-sky-300 border border-sky-500/30'
    default:
      return 'bg-slate-800 text-slate-300 border border-slate-700'
  }
})
</script>

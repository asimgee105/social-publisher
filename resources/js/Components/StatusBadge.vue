<template>
  <span :class="[badgeStyle, 'px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider inline-flex items-center gap-1.5']">
    <span class="w-1.5 h-1.5 rounded-full" :class="dotStyle"></span>
    {{ formattedStatus }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, default: 'draft' }
})

const formattedStatus = computed(() => {
  return (props.status || 'draft').replace('_', ' ')
})

const badgeStyle = computed(() => {
  switch (props.status?.toLowerCase()) {
    case 'published':
      return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30'
    case 'scheduled':
      return 'bg-amber-500/10 text-amber-400 border border-amber-500/30'
    case 'publishing':
      return 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 animate-pulse'
    case 'failed':
      return 'bg-rose-500/10 text-rose-400 border border-rose-500/30'
    case 'needs_reauth':
      return 'bg-orange-500/10 text-orange-400 border border-orange-500/30'
    case 'partial_success':
      return 'bg-purple-500/10 text-purple-400 border border-purple-500/30'
    default:
      return 'bg-slate-800 text-slate-400 border border-slate-700'
  }
})

const dotStyle = computed(() => {
  switch (props.status?.toLowerCase()) {
    case 'published': return 'bg-emerald-400'
    case 'scheduled': return 'bg-amber-400'
    case 'publishing': return 'bg-indigo-400'
    case 'failed': return 'bg-rose-400'
    case 'needs_reauth': return 'bg-orange-400'
    default: return 'bg-slate-400'
  }
})
</script>

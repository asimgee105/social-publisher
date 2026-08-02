<template>
  <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden max-w-sm mx-auto shadow-2xl font-sans text-white p-4 space-y-3">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-sky-600 flex items-center justify-center font-bold text-white">in</div>
      <div>
        <h4 class="font-bold text-xs text-slate-100">{{ accountName || 'LinkedIn Member' }}</h4>
        <span class="text-[10px] text-slate-400">Content Creator & Leader • 1m</span>
      </div>
    </div>

    <p class="text-xs text-slate-200 leading-relaxed whitespace-pre-line">
      <span class="font-bold block mb-1 text-sky-400" v-if="hook">{{ hook }}</span>
      {{ caption }}
    </p>

    <div class="flex flex-wrap gap-1">
      <span v-for="tag in hashtags" :key="tag" class="text-xs text-sky-400 font-medium">{{ tag }}</span>
    </div>

    <div class="aspect-video bg-slate-950 rounded-xl overflow-hidden border border-slate-800 flex items-center justify-center">
      <video v-if="videoUrl" :src="videoUrl" class="w-full h-full object-cover" controls preload="metadata"></video>
      <p v-else class="text-xs text-slate-600">No Video Selected</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  accountName: String,
  hook: String,
  caption: String,
  hashtags: Array,
  videoUrl: String,
})
</script>

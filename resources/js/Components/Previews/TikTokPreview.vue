<template>
  <div class="bg-black border border-slate-800 rounded-3xl overflow-hidden max-w-xs mx-auto shadow-2xl relative font-sans text-white">
    <div class="aspect-[9/16] bg-slate-950 relative flex items-center justify-center overflow-hidden">
      <video v-if="videoUrl" :src="videoUrl" class="w-full h-full object-cover" controls preload="metadata"></video>
      <div v-else class="text-center p-6 text-slate-600">
        <p class="text-xs">No Video Selected</p>
      </div>

      <!-- TikTok Overlay -->
      <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-4">
        <p class="text-xs font-bold text-white mb-1">@{{ accountName || 'tiktok_creator' }}</p>
        <p class="text-xs text-slate-200 line-clamp-3 leading-relaxed mb-2">
          <span class="font-bold text-cyan-300 mr-1">{{ hook }}</span>
          {{ caption }}
        </p>
        <div class="flex flex-wrap gap-1">
          <span v-for="tag in hashtags" :key="tag" class="text-[10px] text-cyan-400 font-semibold">{{ tag }}</span>
        </div>
      </div>
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

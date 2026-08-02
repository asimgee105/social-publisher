<template>
  <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden max-w-sm mx-auto shadow-2xl font-sans text-white">
    <!-- Video Player -->
    <div class="aspect-video bg-black relative flex items-center justify-center">
      <video v-if="videoUrl" :src="videoUrl" class="w-full h-full object-cover" controls preload="metadata"></video>
      <div v-else class="text-center p-6 text-slate-600">
        <p class="text-xs">No Video Selected</p>
      </div>
      <span class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded">YouTube Short</span>
    </div>

    <!-- Metadata -->
    <div class="p-4 space-y-2">
      <h3 class="font-bold text-sm text-slate-100 line-clamp-2">{{ title || hook || 'YouTube Video Title' }}</h3>
      <div class="flex items-center gap-2 text-xs text-slate-400">
        <div class="w-6 h-6 rounded-full bg-red-600 flex items-center justify-center font-bold text-white text-[10px]">YT</div>
        <span>{{ accountName || 'Channel Name' }}</span>
        <span>• 0 views</span>
      </div>
      <div class="p-3 bg-slate-950/80 rounded-xl text-xs text-slate-300 leading-relaxed border border-slate-800">
        <p class="line-clamp-3 mb-2">{{ description || caption }}</p>
        <div class="flex flex-wrap gap-1">
          <span v-for="tag in hashtags" :key="tag" class="text-blue-400 font-mono">{{ tag }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  accountName: String,
  title: String,
  hook: String,
  description: String,
  caption: String,
  hashtags: Array,
  videoUrl: String,
})
</script>

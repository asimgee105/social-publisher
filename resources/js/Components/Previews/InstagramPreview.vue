<template>
  <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden max-w-xs mx-auto shadow-2xl relative font-sans text-white">
    <!-- Header -->
    <div class="p-3 border-b border-slate-800/60 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-yellow-400 via-rose-500 to-purple-600 p-[2px]">
          <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center text-xs font-bold">
            IG
          </div>
        </div>
        <span class="text-xs font-semibold text-slate-100">@{{ accountName || 'instagram_account' }}</span>
      </div>
      <span class="text-xs font-semibold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-md">Reel</span>
    </div>

    <!-- Media Area -->
    <div class="aspect-[9/16] bg-slate-950 relative flex items-center justify-center overflow-hidden group">
      <video v-if="videoUrl" :src="videoUrl" class="w-full h-full object-cover" controls preload="metadata"></video>
      <div v-else class="text-center p-6 text-slate-600">
        <p class="text-xs">No Video Selected</p>
      </div>

      <!-- Overlay Caption Mock -->
      <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-4">
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xs font-bold text-white">@{{ accountName || 'instagram_account' }}</span>
          <button class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-semibold">Follow</button>
        </div>
        <p class="text-xs text-slate-200 line-clamp-3 leading-relaxed mb-2">
          <span class="font-bold mr-1">{{ hook }}</span>
          {{ caption }}
        </p>
        <div class="flex flex-wrap gap-1">
          <span v-for="tag in hashtags" :key="tag" class="text-[10px] text-pink-400 font-medium">{{ tag }}</span>
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

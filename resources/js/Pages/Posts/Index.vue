<template>
  <AdminLayout>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="text-2xl font-bold text-slate-100">Content Library</h2>
          <p class="text-xs text-slate-400">Search, filter, monitor, and duplicate multi-platform content items.</p>
        </div>

        <Link href="/social/admin/posts/create" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm shadow-md flex items-center gap-2">
          <PlusIcon class="w-4 h-4" />
          Create Post
        </Link>
      </div>

      <!-- Filters & Search -->
      <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-900/60 p-3 rounded-2xl border border-slate-800">
        <input v-model="search" @input="debouncedSearch" type="text" placeholder="Search by title or topic..."
               class="w-full sm:w-72 bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-100 focus:outline-none focus:border-indigo-500" />

        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto">
          <button v-for="st in statuses" :key="st.key" @click="filterStatus(st.key)"
                  :class="[
                    currentStatus === st.key ? 'bg-indigo-600 text-white font-semibold' : 'bg-slate-950 text-slate-400 hover:bg-slate-800',
                    'px-3 py-1.5 rounded-lg text-xs transition border border-slate-800 capitalize whitespace-nowrap'
                  ]">
            {{ st.label }}
          </button>
        </div>
      </div>

      <!-- Content Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="post in posts.data" :key="post.id" class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden flex flex-col group hover:border-slate-700 transition">
          <!-- Thumbnail Header -->
          <div class="aspect-video bg-slate-950 relative flex items-center justify-center border-b border-slate-800/80">
            <img v-if="post.media_asset?.thumbnail_path" :src="'/storage/' + post.media_asset.thumbnail_path" class="w-full h-full object-cover" />
            <div v-else class="text-xs text-slate-600 font-bold">NO THUMBNAIL</div>
            <div class="absolute top-3 right-3">
              <StatusBadge :status="post.status" />
            </div>
          </div>

          <!-- Card Content -->
          <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
            <div class="space-y-2">
              <h3 class="font-bold text-sm text-slate-100 line-clamp-1 group-hover:text-indigo-400 transition">{{ post.title }}</h3>
              <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">{{ post.base_content }}</p>
            </div>

            <div class="space-y-3 pt-3 border-t border-slate-800/60">
              <div class="flex flex-wrap gap-1.5">
                <PlatformBadge v-for="pf in post.post_platforms" :key="pf.id" :platform="pf.platform_key" />
              </div>

              <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                <span>{{ new Date(post.created_at).toLocaleDateString() }}</span>
                <div class="flex items-center gap-2">
                  <Link :href="'/social/admin/posts/' + post.id" class="px-2.5 py-1 rounded-lg bg-indigo-600/20 text-indigo-400 font-semibold hover:bg-indigo-600/30 transition">
                    View
                  </Link>
                  <button @click="duplicatePost(post.id)" class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 transition">
                    Copy
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="posts.data.length === 0" class="p-12 text-center bg-slate-900/30 rounded-2xl border border-dashed border-slate-800 text-slate-500 text-xs">
        No content items found. Create a new post to get started!
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PlatformBadge from '@/Components/PlatformBadge.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { PlusIcon } from 'lucide-vue-next'

const props = defineProps({
  posts: Object,
  filters: Object,
})

const search = ref(props.filters.search)
const currentStatus = ref(props.filters.status)

const statuses = [
  { key: 'all', label: 'All Content' },
  { key: 'draft', label: 'Draft' },
  { key: 'scheduled', label: 'Scheduled' },
  { key: 'publishing', label: 'Publishing' },
  { key: 'published', label: 'Published' },
  { key: 'failed', label: 'Failed' },
]

function filterStatus(st) {
  currentStatus.value = st
  router.get('/social/admin/posts', { status: st, search: search.value }, { preserveState: true })
}

function debouncedSearch() {
  router.get('/social/admin/posts', { status: currentStatus.value, search: search.value }, { preserveState: true })
}

function duplicatePost(id) {
  router.post(`/social/admin/posts/${id}/duplicate`)
}
</script>

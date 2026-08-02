<template>
  <AdminLayout>
    <div class="max-w-4xl mx-auto space-y-8 pb-16">
      <div>
        <h2 class="text-2xl font-bold text-slate-100">Create Multi-Platform Post</h2>
        <p class="text-xs text-slate-400">Upload video once → AI generate captions & hashtags → Preview every platform → Schedule or Publish everywhere.</p>
      </div>

      <!-- Step Indicator Bar -->
      <div class="grid grid-cols-5 gap-2 bg-slate-900/60 p-2 rounded-2xl border border-slate-800 text-center text-xs font-semibold">
        <div v-for="(st, idx) in steps" :key="st"
             :class="[
               step === idx + 1 ? 'bg-indigo-600 text-white shadow-md' : (step > idx + 1 ? 'bg-emerald-500/20 text-emerald-300' : 'text-slate-500'),
               'py-2.5 rounded-xl transition cursor-pointer'
             ]" @click="goToStep(idx + 1)">
          Step {{ idx + 1 }}: {{ st }}
        </div>
      </div>

      <!-- STEP 1: VIDEO UPLOAD -->
      <div v-if="step === 1" class="space-y-6 bg-slate-900/60 border border-slate-800 p-6 rounded-3xl">
        <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
          <UploadIcon class="w-5 h-5 text-indigo-400" />
          1. Upload Vertical / Short-Form Video
        </h3>

        <!-- Drag & Drop Zone -->
        <div class="border-2 border-dashed border-slate-700 hover:border-indigo-500 rounded-3xl p-8 text-center transition cursor-pointer bg-slate-950/40 relative"
             @dragover.prevent @drop.prevent="handleDrop" @click="triggerFileInput">
          <input ref="fileInput" type="file" accept="video/mp4,video/mov,video/webm" class="hidden" @change="handleFileSelect" />

          <div v-if="uploading" class="space-y-3 py-6">
            <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="text-xs text-slate-300 font-semibold">Analyzing Video Metadata & Generating Thumbnails...</p>
          </div>

          <div v-else-if="uploadedMedia" class="space-y-3">
            <div class="w-24 h-32 bg-slate-900 rounded-xl mx-auto overflow-hidden border border-slate-700 flex items-center justify-center">
              <img v-if="uploadedMedia.thumbnail_path" :src="'/storage/' + uploadedMedia.thumbnail_path" class="w-full h-full object-cover" />
              <video v-else :src="'/storage/' + uploadedMedia.path" class="w-full h-full object-cover"></video>
            </div>
            <p class="font-bold text-sm text-slate-100">{{ uploadedMedia.original_name }}</p>
            <div class="flex items-center justify-center gap-4 text-xs text-slate-400 font-mono">
              <span>{{ uploadedMedia.width }}x{{ uploadedMedia.height }}</span>
              <span>{{ uploadedMedia.duration }}s</span>
              <span>{{ uploadedMedia.aspect_ratio }}</span>
            </div>
            <span class="text-xs text-indigo-400 hover:underline inline-block mt-2">Click to replace video</span>
          </div>

          <div v-else class="space-y-2 py-6">
            <UploadCloudIcon class="w-12 h-12 text-indigo-400 mx-auto" />
            <p class="font-bold text-sm text-slate-200">Drag & Drop Video Here or Click to Browse</p>
            <p class="text-xs text-slate-500">Supports MP4, MOV, WebM (Max 500MB)</p>
          </div>
        </div>

        <div class="flex justify-end">
          <button :disabled="!uploadedMedia" @click="step = 2" class="px-6 py-2.5 rounded-xl bg-indigo-600 disabled:opacity-50 hover:bg-indigo-500 text-white font-semibold text-xs transition">
            Next: Select Target Platforms →
          </button>
        </div>
      </div>

      <!-- STEP 2: SELECT TARGET PLATFORMS -->
      <div v-if="step === 2" class="space-y-6 bg-slate-900/60 border border-slate-800 p-6 rounded-3xl">
        <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
          <Share2Icon class="w-5 h-5 text-indigo-400" />
          2. Select Target Social Platforms
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <div v-for="pf in availablePlatforms" :key="pf.key"
               @click="form.platforms[pf.key].enabled = !form.platforms[pf.key].enabled"
               :class="[
                 form.platforms[pf.key].enabled ? 'bg-indigo-600/20 border-indigo-500' : 'bg-slate-950 border-slate-800 opacity-60',
                 'p-4 rounded-2xl border-2 transition cursor-pointer flex items-center justify-between'
               ]">
            <div>
              <PlatformBadge :platform="pf.key" class="mb-1" />
              <p class="text-xs font-semibold text-slate-200">{{ pf.name }}</p>
            </div>
            <input type="checkbox" :checked="form.platforms[pf.key].enabled" class="rounded text-indigo-600" />
          </div>
        </div>

        <div class="flex justify-between pt-4">
          <button @click="step = 1" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-medium">← Back</button>
          <button @click="step = 3" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs">
            Next: AI Content Studio →
          </button>
        </div>
      </div>

      <!-- STEP 3: AI CONTENT STUDIO -->
      <div v-if="step === 3" class="space-y-6 bg-slate-900/60 border border-slate-800 p-6 rounded-3xl">
        <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
          <SparklesIcon class="w-5 h-5 text-indigo-400" />
          3. AI Content Studio
        </h3>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Core Content Concept / Topic</label>
            <textarea v-model="form.base_content" rows="3" placeholder="E.g. 3 secret tips to boost productivity using AI automation in 2026..."
                      class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-slate-100 focus:outline-none focus:border-indigo-500"></textarea>
          </div>

          <button :disabled="generatingAi || !form.base_content" @click="runAiGeneration"
                  class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20">
            <SparklesIcon class="w-4 h-4" />
            {{ generatingAi ? 'AI Generating Platform Copy & Hashtags...' : 'Generate Platform-Specific Captions with AI' }}
          </button>
        </div>

        <div class="flex justify-between pt-4">
          <button @click="step = 2" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">← Back</button>
          <button @click="step = 4" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs">
            Next: Platform Previews & Edit →
          </button>
        </div>
      </div>

      <!-- STEP 4: PREVIEW & CUSTOMIZE -->
      <div v-if="step === 4" class="space-y-6">
        <div class="flex items-center justify-between">
          <h3 class="font-bold text-lg text-slate-100">4. Platform Visual Previews & Edits</h3>
          <span class="text-xs text-slate-400">Review layout & adjust platform copy</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div v-for="pfKey in selectedPlatformKeys" :key="pfKey" class="space-y-4 bg-slate-900/60 border border-slate-800 p-6 rounded-3xl">
            <div class="flex items-center justify-between">
              <PlatformBadge :platform="pfKey" />
              <span class="text-xs font-semibold text-slate-400">Custom Edit</span>
            </div>

            <!-- Preview Card -->
            <component :is="getPreviewComponent(pfKey)"
                       :accountName="form.platforms[pfKey].account_name"
                       :hook="form.platforms[pfKey].hook"
                       :caption="form.platforms[pfKey].caption"
                       :title="form.platforms[pfKey].youtube_title"
                       :description="form.platforms[pfKey].youtube_description"
                       :hashtags="form.platforms[pfKey].hashtags"
                       :videoUrl="uploadedMedia ? '/storage/' + uploadedMedia.path : null" />

            <!-- Editable fields -->
            <div class="space-y-3 pt-4 border-t border-slate-800">
              <div v-if="pfKey === 'youtube'">
                <label class="block text-[11px] font-semibold text-slate-400 mb-1">YouTube Video Title</label>
                <input v-model="form.platforms[pfKey].youtube_title" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-100" />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Caption / Body Copy</label>
                <textarea v-model="form.platforms[pfKey].caption" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-100"></textarea>
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-slate-400 mb-1">Hashtags (Comma Separated)</label>
                <input :value="form.platforms[pfKey].hashtags?.join(', ')"
                       @input="form.platforms[pfKey].hashtags = $event.target.value.split(',').map(s => s.trim())"
                       type="text" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-100" />
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between pt-4">
          <button @click="step = 3" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">← Back</button>
          <button @click="step = 5" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs">
            Next: Schedule or Publish →
          </button>
        </div>
      </div>

      <!-- STEP 5: SCHEDULE & PUBLISH CONFIRMATION -->
      <div v-if="step === 5" class="space-y-6 bg-slate-900/60 border border-slate-800 p-6 rounded-3xl max-w-xl mx-auto">
        <h3 class="font-bold text-lg text-slate-100 flex items-center gap-2">
          <CalendarIcon class="w-5 h-5 text-indigo-400" />
          5. Schedule or Publish Now
        </h3>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Post Title / Internal Reference</label>
            <input v-model="form.title" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-slate-100" />
          </div>

          <div class="p-4 bg-slate-950 rounded-2xl border border-slate-800 space-y-3">
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-200 cursor-pointer">
              <input type="radio" value="now" v-model="publishTiming" class="text-indigo-600" />
              Publish Immediately Across Selected Platforms
            </label>

            <label class="flex items-center gap-2 text-xs font-semibold text-slate-200 cursor-pointer">
              <input type="radio" value="schedule" v-model="publishTiming" class="text-indigo-600" />
              Schedule for Later Date & Time
            </label>

            <div v-if="publishTiming === 'schedule'" class="pt-3 border-t border-slate-800 space-y-3">
              <div>
                <label class="block text-[11px] text-slate-400 mb-1">Date & Time (Local Timezone)</label>
                <input v-model="form.scheduled_at" type="datetime-local" class="w-full bg-slate-900 border border-slate-800 rounded-xl p-2.5 text-xs text-slate-100" />
              </div>
              <p class="text-[11px] text-slate-500">Configured Local Timezone: <strong class="text-slate-300">Asia/Karachi (PKT)</strong></p>
            </div>
          </div>
        </div>

        <div class="flex justify-between pt-4">
          <button @click="step = 4" class="px-5 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">← Back</button>
          <button @click="submitPost" class="px-8 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs shadow-lg shadow-emerald-500/20">
            Confirm & {{ publishTiming === 'now' ? 'Publish Now' : 'Schedule Queue' }}
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import PlatformBadge from '@/Components/PlatformBadge.vue'
import InstagramPreview from '@/Components/Previews/InstagramPreview.vue'
import TikTokPreview from '@/Components/Previews/TikTokPreview.vue'
import YouTubePreview from '@/Components/Previews/YouTubePreview.vue'
import FacebookPreview from '@/Components/Previews/FacebookPreview.vue'
import LinkedInPreview from '@/Components/Previews/LinkedInPreview.vue'
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import axios from 'axios'
import {
  UploadIcon,
  UploadCloudIcon,
  Share2Icon,
  SparklesIcon,
  CalendarIcon
} from 'lucide-vue-next'

const props = defineProps({
  accounts: Array,
  recentMedia: Array,
})

const steps = ['Upload', 'Platforms', 'AI Studio', 'Previews', 'Confirm']
const step = ref(1)

const fileInput = ref(null)
const uploading = ref(false)
const uploadedMedia = ref(null)
const generatingAi = ref(false)
const publishTiming = ref('now')

const availablePlatforms = [
  { key: 'instagram', name: 'Instagram Reel' },
  { key: 'facebook', name: 'Facebook Reel/Video' },
  { key: 'tiktok', name: 'TikTok Video' },
  { key: 'youtube', name: 'YouTube Short' },
  { key: 'linkedin', name: 'LinkedIn Video' },
]

const form = ref({
  title: 'AI Multi-Platform Campaign',
  base_content: '3 essential secrets to scale content creation automatically in 2026 using AI.',
  media_asset_id: null,
  scheduled_at: null,
  timezone: 'Asia/Karachi',
  platforms: {
    instagram: { enabled: true, caption: '', hook: '', hashtags: [], social_account_id: null },
    facebook: { enabled: true, caption: '', hook: '', hashtags: [], social_account_id: null },
    tiktok: { enabled: true, caption: '', hook: '', hashtags: [], social_account_id: null },
    youtube: { enabled: true, caption: '', hook: '', youtube_title: '', youtube_description: '', hashtags: [], social_account_id: null },
    linkedin: { enabled: true, caption: '', hook: '', hashtags: [], social_account_id: null },
  }
})

const selectedPlatformKeys = computed(() => {
  return Object.keys(form.value.platforms).filter(k => form.value.platforms[k].enabled)
})

function triggerFileInput() {
  fileInput.value.click()
}

function handleFileSelect(e) {
  const file = e.target.files[0]
  if (file) uploadFile(file)
}

function handleDrop(e) {
  const file = e.dataTransfer.files[0]
  if (file) uploadFile(file)
}

async function uploadFile(file) {
  uploading.value = true
  const formData = new FormData()
  formData.append('video', file)

  try {
    const res = await axios.post('/social/admin/media/upload', formData)
    if (res.data.success) {
      uploadedMedia.value = res.data.media
      form.value.media_asset_id = res.data.media.id
      form.value.title = file.name.replace(/\.[^/.]+$/, "")
    }
  } catch (err) {
    alert('Upload failed: ' + (err.response?.data?.message || err.message))
  } finally {
    uploading.value = false
  }
}

async function runAiGeneration() {
  generatingAi.value = true
  try {
    const res = await axios.post('/social/admin/ai/generate', {
      topic: form.value.base_content,
      platforms: selectedPlatformKeys.value,
    })

    if (res.data.success) {
      const data = res.data.data
      Object.keys(data).forEach(pk => {
        if (form.value.platforms[pk]) {
          form.value.platforms[pk].hook = data[pk].hook || ''
          form.value.platforms[pk].caption = data[pk].caption || ''
          form.value.platforms[pk].youtube_title = data[pk].youtube_title || ''
          form.value.platforms[pk].youtube_description = data[pk].youtube_description || ''
          form.value.platforms[pk].hashtags = data[pk].hashtags || []
        }
      })
      step.value = 4
    }
  } catch (err) {
    alert('AI Generation error: ' + err.message)
  } finally {
    generatingAi.value = false
  }
}

function getPreviewComponent(key) {
  switch (key) {
    case 'instagram': return InstagramPreview
    case 'tiktok': return TikTokPreview
    case 'youtube': return YouTubePreview
    case 'facebook': return FacebookPreview
    case 'linkedin': return LinkedInPreview
    default: return InstagramPreview
  }
}

function goToStep(s) {
  if (s > 1 && !uploadedMedia.value) return
  step.value = s
}

function submitPost() {
  if (publishTiming.value === 'now') {
    form.value.scheduled_at = null
  }
  router.post('/social/admin/posts', form.value)
}
</script>

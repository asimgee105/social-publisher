<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 font-sans selection:bg-indigo-500 selection:text-white">
    <div class="w-full max-w-md space-y-8 bg-slate-900/80 border border-slate-800 p-8 rounded-3xl backdrop-blur shadow-2xl">
      <div class="text-center space-y-3">
        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-pink-500 mx-auto flex items-center justify-center text-white shadow-xl shadow-indigo-500/30 text-2xl font-extrabold">
          A
        </div>
        <div>
          <h1 class="text-2xl font-extrabold text-slate-100 tracking-tight">Asim Social Publisher</h1>
          <p class="text-xs text-slate-400 mt-1">Admin Panel Access • SocialFlow</p>
        </div>
      </div>

      <form @submit.prevent="submit" autocomplete="off" class="space-y-5">
        <div v-if="form.errors.email" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold">
          {{ form.errors.email }}
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Email</label>
          <input v-model="form.email" type="email" required autofocus autocomplete="off" placeholder="Enter admin email"
                 class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
          <input v-model="form.password" type="password" required autocomplete="new-password" placeholder="••••••••"
                 class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-xs text-slate-100 focus:outline-none focus:border-indigo-500 transition" />
        </div>

        <div class="flex items-center justify-between text-xs">
          <label class="flex items-center gap-2 text-slate-400 cursor-pointer">
            <input type="checkbox" v-model="form.remember" class="rounded text-indigo-600 bg-slate-950 border-slate-800" />
            Remember Me
          </label>
        </div>

        <button :disabled="form.processing" type="submit"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/25 transition transform active:scale-95 disabled:opacity-50">
          {{ form.processing ? 'Signing In...' : 'Sign In to Social Admin' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/social/admin/login')
}
</script>

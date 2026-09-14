<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Store, Lock, User, AlertCircle, Loader2 } from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const errorMessage = ref('')

const handleLogin = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'Username dan password wajib diisi'
    return
  }

  try {
    errorMessage.value = ''
    await authStore.login({
      username: username.value,
      password: password.value,
    })
    router.push('/')
  } catch (err: any) {
    errorMessage.value = authStore.error || 'Terjadi kesalahan saat login'
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-950 p-4 text-slate-100">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500/10 text-emerald-400 mb-4 border border-emerald-500/20">
          <Store class="w-7 h-7" />
        </div>
        <h1 class="text-2xl font-bold text-white tracking-tight">KasirKel Login</h1>
        <p class="text-sm text-slate-400 mt-1">Masuk untuk mengelola transaksi & inventaris</p>
      </div>

      <!-- Error Alert -->
      <div
        v-if="errorMessage"
        class="mb-6 p-4 rounded-xl bg-rose-950/40 border border-rose-800/60 text-rose-300 text-sm flex items-start gap-3"
      >
        <AlertCircle class="w-5 h-5 flex-shrink-0 mt-0.5 text-rose-400" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
            Username
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <User class="w-5 h-5" />
            </div>
            <input
              v-model="username"
              type="text"
              required
              autocomplete="username"
              placeholder="Masukkan username"
              class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm transition"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
            Password
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
              <Lock class="w-5 h-5" />
            </div>
            <input
              v-model="password"
              type="password"
              required
              autocomplete="current-password"
              placeholder="Masukkan password"
              class="w-full pl-11 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 text-sm transition"
            />
          </div>
        </div>

        <button
          type="submit"
          :disabled="authStore.isLoading"
          class="w-full py-3 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-slate-950 font-semibold text-sm transition duration-150 flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/10 cursor-pointer"
        >
          <Loader2 v-if="authStore.isLoading" class="w-4 h-4 animate-spin" />
          <span>{{ authStore.isLoading ? 'Memproses...' : 'Masuk ke Sistem' }}</span>
        </button>
      </form>

      <!-- Footer Info -->
      <div class="mt-8 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-500">
        Backend API: <code class="text-emerald-400">http://127.0.0.1:8000/api</code>
      </div>
    </div>
  </div>
</template>

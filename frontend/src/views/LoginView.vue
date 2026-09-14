<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Store,
  User,
  Lock,
  Eye,
  EyeOff,
  ArrowRight,
  Loader2,
  AlertCircle
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
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
    errorMessage.value = authStore.error || 'Username atau password salah'
  }
}

const quickFill = (user: string, pass: string) => {
  username.value = user
  password.value = pass
  errorMessage.value = ''
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex flex-col items-center justify-center p-4 selection:bg-slate-800 selection:text-white font-sans">
    <div class="w-full max-w-md bg-white border border-slate-200/90 rounded-2xl p-8 sm:p-10 shadow-sm space-y-6 text-center">
      <!-- Logo & Header -->
      <div class="flex flex-col items-center space-y-2">
        <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-md">
          <Store class="w-8 h-8" />
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">POS SEKOLAH</h1>
        <p class="text-xs text-slate-500 font-medium">Sistem Kasir Multi Tenant</p>
      </div>

      <!-- Quick Demo Pills -->
      <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-center gap-2 text-xs">
        <span class="text-slate-400 text-[11px]">Akun Cepat:</span>
        <button
          type="button"
          @click="quickFill('admin', 'password123')"
          class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 text-slate-700 font-semibold transition cursor-pointer"
        >
          admin
        </button>
        <button
          type="button"
          @click="quickFill('kasir1', 'password123')"
          class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:border-slate-400 text-slate-700 font-semibold transition cursor-pointer"
        >
          kasir1
        </button>
      </div>

      <!-- Error Alert -->
      <div
        v-if="errorMessage"
        class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2 text-left"
      >
        <AlertCircle class="w-4 h-4 shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Form (No Pilih Sekolah per user revision) -->
      <form @submit.prevent="handleLogin" class="space-y-4 text-left">
        <!-- Username -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <User class="w-4 h-4" />
          </div>
          <input
            v-model="username"
            type="text"
            required
            autocomplete="username"
            placeholder="Username"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
          />
        </div>

        <!-- Password -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Lock class="w-4 h-4" />
          </div>
          <input
            v-model="password"
            :type="showPassword ? 'text' : 'password'"
            required
            autocomplete="current-password"
            placeholder="Password"
            class="w-full pl-10 pr-10 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
          />
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
          >
            <EyeOff v-if="showPassword" class="w-4 h-4" />
            <Eye v-else class="w-4 h-4" />
          </button>
        </div>

        <!-- Ingat saya & Lupa password -->
        <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input
              v-model="rememberMe"
              type="checkbox"
              class="w-3.5 h-3.5 rounded border-slate-300 text-slate-900 focus:ring-slate-800 cursor-pointer"
            />
            <span>Ingat saya</span>
          </label>
          <a href="#" class="hover:underline text-slate-600">Lupa password?</a>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="authStore.isLoading"
          class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-xs disabled:opacity-50"
        >
          <Loader2 v-if="authStore.isLoading" class="w-4 h-4 animate-spin" />
          <span v-else class="flex items-center gap-2">
            <span>Login</span>
            <ArrowRight class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Register Link (User Revision) -->
      <div class="pt-2 border-t border-slate-100 text-xs text-slate-500">
        Belum memiliki akun kasir?
        <router-link to="/register" class="font-bold text-slate-900 hover:underline ml-1">
          Daftar sekarang
        </router-link>
      </div>

      <!-- Footer -->
      <div class="text-[11px] text-slate-400">
        &copy; 2024 POS Sekolah. All rights reserved.
      </div>
    </div>
  </div>
</template>

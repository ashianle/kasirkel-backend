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
  AlertCircle,
  Loader2
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const rememberMe = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'Silakan isi username dan password'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.login({
      username: username.value,
      password: password.value
    })
    router.push('/')
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Username atau password salah'
  } finally {
    isLoading.value = false
  }
}

const fillDemo = (u: string, p: string) => {
  username.value = u
  password.value = p
}
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col items-center justify-center p-4 select-none">
    <div class="max-w-sm w-full space-y-6">
      <!-- Brand Logo & Header -->
      <div class="text-center space-y-1.5">
        <div class="w-12 h-12 mx-auto flex items-center justify-center text-gray-900 mb-1">
          <Store class="w-10 h-10" />
        </div>
        <h1 class="text-xl font-extrabold text-gray-900 tracking-wide uppercase">POS SEKOLAH</h1>
        <p class="text-xs text-gray-500 font-medium">Sistem Kasir Multi Tenant</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-4">
        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2"
        >
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <!-- Username -->
          <div class="relative">
            <User class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="username"
              type="text"
              required
              placeholder="Username"
              class="w-full pl-10 pr-3.5 py-2.5 rounded-lg border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-hidden focus:border-gray-400 transition"
            />
          </div>

          <!-- Password -->
          <div class="relative">
            <Lock class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Password"
              class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-hidden focus:border-gray-400 transition"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer"
            >
              <EyeOff v-if="showPassword" class="w-4 h-4" />
              <Eye v-else class="w-4 h-4" />
            </button>
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between text-xs text-gray-600 pt-0.5">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                v-model="rememberMe"
                type="checkbox"
                class="rounded border-gray-300 text-black focus:ring-0"
              />
              <span>Ingat saya</span>
            </label>
            <a href="#" class="hover:text-black font-medium transition">Lupa password?</a>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-2.5 bg-[#23272f] hover:bg-black text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 mt-2"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
            <span v-else>Login</span>
            <ArrowRight v-if="!isLoading" class="w-4 h-4" />
          </button>
        </form>

        <!-- Demo Accounts Quick Fill -->
        <div class="pt-3 border-t border-gray-100 space-y-1.5">
          <div class="text-[10px] text-gray-400 text-center font-medium">Akun Demo Cepat:</div>
          <div class="flex gap-1.5 justify-center">
            <button
              type="button"
              @click="fillDemo('superadmin', 'password')"
              class="px-2 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-[10px] font-semibold rounded cursor-pointer"
            >
              Super Admin
            </button>
            <button
              type="button"
              @click="fillDemo('kasir_smkn2', 'password')"
              class="px-2 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-[10px] font-semibold rounded cursor-pointer"
            >
              Kasir SMKN 2
            </button>
            <button
              type="button"
              @click="fillDemo('kasir_sman1', 'password')"
              class="px-2 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-700 text-[10px] font-semibold rounded cursor-pointer"
            >
              Kasir SMAN 1
            </button>
          </div>
        </div>

        <!-- Link ke Register -->
        <div class="text-center pt-2">
          <router-link
            to="/register"
            class="text-xs text-gray-600 hover:text-black font-semibold transition"
          >
            Belum punya akun? <span class="text-black underline">Daftar sekarang</span>
          </router-link>
        </div>
      </div>

      <!-- Copyright Footer -->
      <div class="text-center text-[11px] text-gray-400">
        © 2024 POS Sekolah. All rights reserved.
      </div>
    </div>
  </div>
</template>

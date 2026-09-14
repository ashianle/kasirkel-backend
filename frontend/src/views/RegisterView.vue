<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import apiClient from '@/api/client'
import { useAuthStore } from '@/stores/auth'
import {
  Store,
  User,
  Mail,
  Lock,
  ArrowRight,
  Loader2,
  AlertCircle,
  CheckCircle2
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const namaLengkap = ref('')
const username = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleRegister = async () => {
  if (!namaLengkap.value || !username.value || !password.value) {
    errorMessage.value = 'Nama lengkap, username, dan password wajib diisi'
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Konfirmasi password tidak cocok'
    return
  }

  if (password.value.length < 6) {
    errorMessage.value = 'Password minimal 6 karakter'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await apiClient.post('/register', {
      nama_lengkap: namaLengkap.value,
      username: username.value,
      email: email.value || null,
      password: password.value,
    })

    successMessage.value = 'Pendaftaran berhasil! Mengalihkan ke dashboard...'
    authStore.token = res.data.token
    authStore.user = res.data.user
    if (res.data.token) localStorage.setItem('auth_token', res.data.token)
    if (res.data.user) localStorage.setItem('auth_user', JSON.stringify(res.data.user))

    setTimeout(() => {
      router.push('/')
    }, 1200)
  } catch (err: any) {
    errorMessage.value =
      err.response?.data?.errors?.username?.[0] ||
      err.response?.data?.message ||
      'Pendaftaran gagal'
  } finally {
    isLoading.value = false
  }
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
        <p class="text-xs text-slate-500 font-medium">Pendaftaran Akun Baru Kasir / Staf</p>
      </div>

      <!-- Success Alert -->
      <div
        v-if="successMessage"
        class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2 text-left"
      >
        <CheckCircle2 class="w-4 h-4 shrink-0" />
        <span>{{ successMessage }}</span>
      </div>

      <!-- Error Alert -->
      <div
        v-if="errorMessage"
        class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2 text-left"
      >
        <AlertCircle class="w-4 h-4 shrink-0" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleRegister" class="space-y-3.5 text-left">
        <!-- Nama Lengkap -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <User class="w-4 h-4" />
          </div>
          <input
            v-model="namaLengkap"
            type="text"
            required
            placeholder="Nama Lengkap"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
          />
        </div>

        <!-- Username -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <User class="w-4 h-4" />
          </div>
          <input
            v-model="username"
            type="text"
            required
            placeholder="Username (untuk login)"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
          />
        </div>

        <!-- Email (Optional) -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Mail class="w-4 h-4" />
          </div>
          <input
            v-model="email"
            type="email"
            placeholder="Email (Opsional)"
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
            type="password"
            required
            placeholder="Password (min. 6 karakter)"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
          />
        </div>

        <!-- Konfirmasi Password -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
            <Lock class="w-4 h-4" />
          </div>
          <input
            v-model="passwordConfirmation"
            type="password"
            required
            placeholder="Ulangi Password"
            class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-slate-800 focus:ring-1 focus:ring-slate-800 transition"
          />
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-xs disabled:opacity-50 mt-2"
        >
          <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
          <span v-else class="flex items-center gap-2">
            <span>Daftar Sekarang</span>
            <ArrowRight class="w-4 h-4" />
          </span>
        </button>
      </form>

      <!-- Back to Login Link -->
      <div class="pt-2 border-t border-slate-100 text-xs text-slate-500">
        Sudah memiliki akun kasir?
        <router-link to="/login" class="font-bold text-slate-900 hover:underline ml-1">
          Masuk di sini
        </router-link>
      </div>

      <!-- Footer -->
      <div class="text-[11px] text-slate-400">
        &copy; 2024 POS Sekolah. All rights reserved.
      </div>
    </div>
  </div>
</template>

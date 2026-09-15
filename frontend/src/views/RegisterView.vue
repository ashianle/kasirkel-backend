<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  Store,
  User,
  Mail,
  Lock,
  Eye,
  EyeOff,
  ArrowRight,
  AlertCircle,
  Loader2,
  CheckCircle2
} from 'lucide-vue-next'
import apiClient from '@/api/client'

const router = useRouter()

const namaLengkap = ref('')
const username = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const showPassword = ref(false)

const errorMessage = ref('')
const successMessage = ref('')
const isLoading = ref(false)

const handleRegister = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!namaLengkap.value || !username.value || !password.value) {
    errorMessage.value = 'Nama lengkap, username, dan password wajib diisi'
    return
  }

  if (password.value.length < 6) {
    errorMessage.value = 'Password minimal harus 6 karakter'
    return
  }

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = 'Konfirmasi password tidak cocok'
    return
  }

  isLoading.value = true

  try {
    const payload = {
      nama_lengkap: namaLengkap.value,
      username: username.value,
      email: email.value || null,
      password: password.value,
      id_sekolah: 1
    }

    await apiClient.post('/register', payload)

    successMessage.value = 'Pendaftaran berhasil! Mengalihkan ke halaman login...'
    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (err: any) {
    errorMessage.value =
      err.response?.data?.message ||
      err.response?.data?.errors?.username?.[0] ||
      'Pendaftaran gagal. Pastikan username belum digunakan.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col items-center justify-center p-4 select-none">
    <div class="max-w-sm w-full space-y-6">
      <!-- Header -->
      <div class="text-center space-y-1.5">
        <div class="w-12 h-12 mx-auto flex items-center justify-center text-gray-900 mb-1">
          <Store class="w-10 h-10" />
        </div>
        <h1 class="text-xl font-extrabold text-gray-900 tracking-wide uppercase">DAFTAR KASIR BARU</h1>
        <p class="text-xs text-gray-500 font-medium">Sistem Kasir Multi Tenant</p>
      </div>

      <!-- Register Card -->
      <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-xs space-y-4">
        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2"
        >
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Success Alert -->
        <div
          v-if="successMessage"
          class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs flex items-center gap-2"
        >
          <CheckCircle2 class="w-4 h-4 shrink-0" />
          <span>{{ successMessage }}</span>
        </div>

        <form @submit.prevent="handleRegister" class="space-y-3">
          <!-- Nama Lengkap -->
          <div class="relative">
            <User class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="namaLengkap"
              type="text"
              required
              placeholder="Nama Lengkap"
              class="w-full pl-10 pr-3.5 py-2.5 rounded-lg border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-hidden focus:border-gray-400 transition"
            />
          </div>

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

          <!-- Email (Opsional) -->
          <div class="relative">
            <Mail class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="email"
              type="email"
              placeholder="Email (Opsional)"
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
              placeholder="Password (min. 6 karakter)"
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

          <!-- Konfirmasi Password -->
          <div class="relative">
            <Lock class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="passwordConfirmation"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="Konfirmasi Password"
              class="w-full pl-10 pr-3.5 py-2.5 rounded-lg border border-gray-200 text-xs text-gray-900 placeholder:text-gray-400 focus:outline-hidden focus:border-gray-400 transition"
            />
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full py-2.5 bg-[#23272f] hover:bg-black text-white text-xs font-bold rounded-lg shadow-xs transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 mt-2"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
            <span v-else>Daftar Sekarang</span>
            <ArrowRight v-if="!isLoading" class="w-4 h-4" />
          </button>
        </form>

        <!-- Link ke Login -->
        <div class="text-center pt-2">
          <router-link
            to="/login"
            class="text-xs text-gray-600 hover:text-black font-semibold transition"
          >
            Sudah punya akun? <span class="text-black underline">Masuk di sini</span>
          </router-link>
        </div>
      </div>

      <!-- Copyright -->
      <div class="text-center text-[11px] text-gray-400">
        © 2024 POS Sekolah. All rights reserved.
      </div>
    </div>
  </div>
</template>

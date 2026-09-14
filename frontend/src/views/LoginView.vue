<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { 
  Store, 
  Lock, 
  User, 
  AlertCircle, 
  Loader2, 
  ShieldCheck, 
  Zap, 
  Receipt 
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const errorMessage = ref('')
const showPassword = ref(false)

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
    router.push('/pos')
  } catch (err: any) {
    errorMessage.value = authStore.error || 'Username atau password tidak sesuai'
  }
}

const fillDemo = (user: string, pass: string) => {
  username.value = user
  password.value = pass
  errorMessage.value = ''
}
</script>

<template>
  <div class="min-h-screen flex flex-col lg:flex-row bg-slate-900 text-slate-100 font-sans selection:bg-indigo-500 selection:text-white">
    <!-- Left Hero Column -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 p-12 flex-col justify-between relative overflow-hidden border-r border-slate-800">
      <!-- Decorative Background Glow -->
      <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <!-- Header Brand -->
      <div class="relative z-10 flex items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/30">
          <Store class="w-6 h-6" />
        </div>
        <div>
          <span class="text-xl font-bold tracking-tight text-white">KasirKel</span>
          <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">POS Pro</span>
        </div>
      </div>

      <!-- Hero Message -->
      <div class="relative z-10 space-y-6 max-w-lg">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Database MySQL pos_sekolah Terhubung
        </div>
        <h1 class="text-4xl font-extrabold tracking-tight text-white leading-tight">
          Sistem Kasir Modern, Cepat, dan Terintegrasi.
        </h1>
        <p class="text-slate-400 text-base leading-relaxed">
          Kelola transaksi point of sale kasir, inventaris barang, dan laporan penjualan secara efisien dengan arsitektur REST API & Vue SPA.
        </p>

        <!-- Feature Points -->
        <div class="space-y-3.5 pt-4">
          <div class="flex items-center gap-3 text-slate-300 text-sm">
            <div class="w-7 h-7 rounded-lg bg-indigo-900/60 border border-indigo-700/50 flex items-center justify-center text-indigo-400">
              <Zap class="w-4 h-4" />
            </div>
            <span>Kasir POS Interaktif & Perhitungan Kembalian Otomatis</span>
          </div>
          <div class="flex items-center gap-3 text-slate-300 text-sm">
            <div class="w-7 h-7 rounded-lg bg-indigo-900/60 border border-indigo-700/50 flex items-center justify-center text-indigo-400">
              <Receipt class="w-4 h-4" />
            </div>
            <span>Dukungan Struk Belanja & Cetak Transaksi Instan</span>
          </div>
          <div class="flex items-center gap-3 text-slate-300 text-sm">
            <div class="w-7 h-7 rounded-lg bg-indigo-900/60 border border-indigo-700/50 flex items-center justify-center text-indigo-400">
              <ShieldCheck class="w-4 h-4" />
            </div>
            <span>Keamanan Token Sanctum & Kontrol Stok Real-time</span>
          </div>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="relative z-10 text-xs text-slate-500">
        KasirKel POS v2.0 • Koperasi & Retail Solution
      </div>
    </div>

    <!-- Right Form Column -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-slate-950">
      <div class="w-full max-w-md space-y-8">
        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center gap-3 justify-center mb-6">
          <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white">
            <Store class="w-5 h-5" />
          </div>
          <span class="text-xl font-bold text-white">KasirKel POS</span>
        </div>

        <div>
          <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
            Masuk ke KasirKel
          </h2>
          <p class="mt-2 text-sm text-slate-400">
            Gunakan kredensial akun kasir atau administrator Anda
          </p>
        </div>

        <!-- Quick Demo Switcher -->
        <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
          <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 flex items-center justify-between">
            <span>Pilih Akun Cepat (Database Ready)</span>
            <span class="text-[10px] text-emerald-400">Klik untuk isi</span>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="fillDemo('admin', 'password123')"
              class="px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-indigo-950/60 border border-slate-700 hover:border-indigo-500/50 text-left transition cursor-pointer"
            >
              <div class="text-xs font-bold text-white">admin</div>
              <div class="text-[10px] text-slate-400">Administrator Utama</div>
            </button>
            <button
              type="button"
              @click="fillDemo('kasir1', 'password123')"
              class="px-3 py-2 rounded-lg bg-slate-800/80 hover:bg-emerald-950/60 border border-slate-700 hover:border-emerald-500/50 text-left transition cursor-pointer"
            >
              <div class="text-xs font-bold text-white">kasir1</div>
              <div class="text-[10px] text-slate-400">Siti Aminah (Kasir)</div>
            </button>
          </div>
        </div>

        <!-- Error Alert -->
        <div
          v-if="errorMessage"
          class="p-4 rounded-xl bg-rose-950/40 border border-rose-800/60 text-rose-300 text-sm flex items-start gap-3"
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
                placeholder="Contoh: admin atau kasir1"
                class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-sm transition"
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
                :type="showPassword ? 'text' : 'password'"
                required
                autocomplete="current-password"
                placeholder="Masukkan password akun"
                class="w-full pl-11 pr-12 py-3 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-sm transition"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-500 hover:text-slate-300 cursor-pointer"
              >
                {{ showPassword ? 'Sembunyi' : 'Lihat' }}
              </button>
            </div>
          </div>

          <button
            type="submit"
            :disabled="authStore.isLoading"
            class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 disabled:opacity-50 text-white font-semibold text-sm transition duration-150 flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/25 cursor-pointer"
          >
            <Loader2 v-if="authStore.isLoading" class="w-4 h-4 animate-spin" />
            <span>{{ authStore.isLoading ? 'Memverifikasi Akses...' : 'Masuk Sekarang' }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

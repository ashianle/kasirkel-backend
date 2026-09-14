<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'
import { 
  Package, 
  ShoppingCart, 
  TrendingUp, 
  ArrowRight,
  Plus
} from 'lucide-vue-next'

const authStore = useAuthStore()

const totalBarang = ref<number | null>(null)
const totalPenjualan = ref<number | null>(null)
const isLoading = ref(true)

const fetchSummary = async () => {
  try {
    isLoading.value = true
    const [resBarang, resPenjualan] = await Promise.allSettled([
      apiClient.get('/barang'),
      apiClient.get('/penjualan'),
    ])

    if (resBarang.status === 'fulfilled') {
      totalBarang.value = Array.isArray(resBarang.value.data) ? resBarang.value.data.length : 0
    }
    if (resPenjualan.status === 'fulfilled') {
      totalPenjualan.value = Array.isArray(resPenjualan.value.data) ? resPenjualan.value.data.length : 0
    }
  } catch (err) {
    console.error('Error fetching dashboard summary:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchSummary()
})
</script>

<template>
  <div class="space-y-8">
    <!-- Header Greeting -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 border border-slate-800 rounded-2xl p-6 sm:p-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
            Selamat Datang, {{ authStore.user?.nama || authStore.user?.username }}! 👋
          </h1>
          <p class="text-slate-400 mt-1 text-sm">
            Sistem KasirKel (Frontend Vue 3 + Backend Laravel API) siap digunakan.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <router-link
            to="/penjualan"
            class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold text-sm transition flex items-center gap-2"
          >
            <Plus class="w-4 h-4" />
            Transaksi Baru
          </router-link>
        </div>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-slate-400">Total Item Barang</span>
          <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
            <Package class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-bold text-white">
            {{ isLoading ? '...' : (totalBarang ?? 0) }}
          </span>
          <span class="text-xs text-slate-500">item terdaftar</span>
        </div>
        <router-link
          to="/barang"
          class="mt-4 text-xs font-semibold text-blue-400 hover:text-blue-300 flex items-center gap-1"
        >
          Lihat Data Barang <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-slate-400">Total Transaksi Penjualan</span>
          <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
            <ShoppingCart class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-3xl font-bold text-white">
            {{ isLoading ? '...' : (totalPenjualan ?? 0) }}
          </span>
          <span class="text-xs text-slate-500">transaksi</span>
        </div>
        <router-link
          to="/penjualan"
          class="mt-4 text-xs font-semibold text-emerald-400 hover:text-emerald-300 flex items-center gap-1"
        >
          Buka Kasir <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-slate-400">Status Server</span>
          <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
            <TrendingUp class="w-5 h-5" />
          </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-sm font-semibold text-white">API Laravel Terhubung</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Port 8000 (Backend) & 5173 (Frontend)</p>
      </div>
    </div>
  </div>
</template>

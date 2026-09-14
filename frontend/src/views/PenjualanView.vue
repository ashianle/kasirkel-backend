<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import { ShoppingCart, RefreshCw, AlertCircle } from 'lucide-vue-next'

interface Penjualan {
  id_penjualan: number
  no_faktur?: string
  tanggal?: string
  total?: number
  keterangan?: string
}

const listPenjualan = ref<Penjualan[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

const fetchPenjualan = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await apiClient.get('/penjualan')
    listPenjualan.value = Array.isArray(res.data) ? res.data : []
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat transaksi penjualan'
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (val?: number) => {
  if (!val) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

onMounted(() => {
  fetchPenjualan()
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
          <ShoppingCart class="w-6 h-6 text-emerald-400" />
          Transaksi Penjualan (Kasir)
        </h1>
        <p class="text-sm text-slate-400 mt-1">Daftar riwayat transaksi kasir</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="fetchPenjualan"
          :disabled="isLoading"
          class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition"
          title="Refresh"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
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

    <!-- Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-300">
          <thead class="bg-slate-950/60 text-slate-400 text-xs uppercase font-semibold border-b border-slate-800">
            <tr>
              <th class="px-6 py-4">ID Penjualan</th>
              <th class="px-6 py-4">No Faktur</th>
              <th class="px-6 py-4">Tanggal</th>
              <th class="px-6 py-4 text-right">Total Transaksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-if="isLoading" class="text-center">
              <td colspan="4" class="px-6 py-12 text-slate-500">Memuat transaksi dari backend...</td>
            </tr>
            <tr v-else-if="listPenjualan.length === 0" class="text-center">
              <td colspan="4" class="px-6 py-12 text-slate-500">Belum ada riwayat transaksi penjualan.</td>
            </tr>
            <tr
              v-else
              v-for="item in listPenjualan"
              :key="item.id_penjualan"
              class="hover:bg-slate-800/40 transition"
            >
              <td class="px-6 py-4 font-mono text-emerald-400">#{{ item.id_penjualan }}</td>
              <td class="px-6 py-4 font-medium text-white">{{ item.no_faktur || '-' }}</td>
              <td class="px-6 py-4 text-slate-400">{{ item.tanggal || '-' }}</td>
              <td class="px-6 py-4 text-right font-semibold text-emerald-400">{{ formatRupiah(item.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

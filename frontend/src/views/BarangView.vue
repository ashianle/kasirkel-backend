<script setup lang="ts">
import { ref, onMounted } from 'vue'
import apiClient from '@/api/client'
import { Package, Search, RefreshCw, AlertCircle } from 'lucide-vue-next'

interface Barang {
  id_barang: number
  barcode: string
  nama: string
  satuan: string
  harga_beli: number
  harga_jual: number
  stok: number
  is_active: boolean
}

const listBarang = ref<Barang[]>([])
const searchQuery = ref('')
const isLoading = ref(false)
const errorMessage = ref('')

const fetchBarang = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await apiClient.get('/barang')
    listBarang.value = Array.isArray(res.data) ? res.data : []
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat data barang'
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

const filteredBarang = () => {
  if (!searchQuery.value) return listBarang.value
  const q = searchQuery.value.toLowerCase()
  return listBarang.value.filter(
    (b) => b.nama.toLowerCase().includes(q) || b.barcode.toLowerCase().includes(q)
  )
}

onMounted(() => {
  fetchBarang()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
          <Package class="w-6 h-6 text-emerald-400" />
          Data Barang
        </h1>
        <p class="text-sm text-slate-400 mt-1">Daftar produk dan inventaris dari API Backend</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="fetchBarang"
          :disabled="isLoading"
          class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition"
          title="Refresh data"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center gap-4">
      <div class="relative flex-1 max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
          <Search class="w-4 h-4" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari berdasarkan nama atau barcode..."
          class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 text-sm"
        />
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
              <th class="px-6 py-4">Barcode</th>
              <th class="px-6 py-4">Nama Barang</th>
              <th class="px-6 py-4">Satuan</th>
              <th class="px-6 py-4 text-right">Harga Beli</th>
              <th class="px-6 py-4 text-right">Harga Jual</th>
              <th class="px-6 py-4 text-center">Stok</th>
              <th class="px-6 py-4 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-if="isLoading" class="text-center">
              <td colspan="7" class="px-6 py-12 text-slate-500">Memuat data dari backend...</td>
            </tr>
            <tr v-else-if="filteredBarang().length === 0" class="text-center">
              <td colspan="7" class="px-6 py-12 text-slate-500">Belum ada data barang ditemukan.</td>
            </tr>
            <tr
              v-else
              v-for="item in filteredBarang()"
              :key="item.id_barang"
              class="hover:bg-slate-800/40 transition"
            >
              <td class="px-6 py-4 font-mono text-xs text-emerald-400">{{ item.barcode }}</td>
              <td class="px-6 py-4 font-medium text-white">{{ item.nama }}</td>
              <td class="px-6 py-4">{{ item.satuan }}</td>
              <td class="px-6 py-4 text-right text-slate-400">{{ formatRupiah(item.harga_beli) }}</td>
              <td class="px-6 py-4 text-right font-medium text-emerald-400">{{ formatRupiah(item.harga_jual) }}</td>
              <td class="px-6 py-4 text-center">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="item.stok > 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'"
                >
                  {{ item.stok }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  class="px-2 py-0.5 rounded text-xs"
                  :class="item.is_active ? 'bg-emerald-950 text-emerald-400 border border-emerald-800' : 'bg-slate-800 text-slate-500'"
                >
                  {{ item.is_active ? 'Aktif' : 'Non-aktif' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

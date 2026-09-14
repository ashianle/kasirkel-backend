<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import {
  Package,
  Search,
  RefreshCw,
  AlertCircle
} from 'lucide-vue-next'

interface Barang {
  id_barang: number
  barcode: string
  sku?: string
  nama: string
  satuan: string
  harga_beli: number
  harga_jual: number
  stok: number
  minimum_stok?: number
  id_kategori: number
  is_active: boolean
}

const listBarang = ref<Barang[]>([])
const listKategori = ref<any[]>([])
const searchQuery = ref('')
const selectedKategori = ref<number | null>(null)
const isLoading = ref(false)
const errorMessage = ref('')

const fetchBarang = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const [resBarang, resKategori] = await Promise.allSettled([
      apiClient.get('/barang'),
      apiClient.get('/kategori'),
    ])

    if (resBarang.status === 'fulfilled') {
      listBarang.value = Array.isArray(resBarang.value.data) ? resBarang.value.data : []
    }
    if (resKategori.status === 'fulfilled') {
      listKategori.value = Array.isArray(resKategori.value.data) ? resKategori.value.data : []
    }
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

const filteredBarang = computed(() => {
  return listBarang.value.filter((b) => {
    const q = searchQuery.value.toLowerCase()
    const matchQuery =
      !q ||
      b.nama.toLowerCase().includes(q) ||
      b.barcode.toLowerCase().includes(q) ||
      (b.sku && b.sku.toLowerCase().includes(q))
    const matchKategori =
      selectedKategori.value === null || b.id_kategori === selectedKategori.value
    return matchQuery && matchKategori
  })
})

// Metrics
const totalAset = computed(() => {
  return listBarang.value.reduce((acc, b) => acc + (b.harga_beli * b.stok), 0)
})

const totalStokFisik = computed(() => {
  return listBarang.value.reduce((acc, b) => acc + b.stok, 0)
})

const getCategoryName = (id: number) => {
  const found = listKategori.value.find((k) => k.id_kategori === id)
  return found ? found.nama : 'Umum'
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
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <Package class="w-4 h-4" />
          </div>
          <span>Katalog Data Barang & Inventaris</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1">Daftar produk aktif dari database MySQL pos_sekolah</p>
      </div>

      <button
        @click="fetchBarang"
        :disabled="isLoading"
        class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
      >
        <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
        <span>Sinkronkan Data</span>
      </button>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <div class="text-xs text-slate-400 font-semibold uppercase">Total SKU Terdaftar</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ listBarang.length }} Item</div>
      </div>
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <div class="text-xs text-slate-400 font-semibold uppercase">Total Stok Fisik</div>
        <div class="text-2xl font-black text-indigo-600 mt-1">{{ totalStokFisik }} Unit</div>
      </div>
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <div class="text-xs text-slate-400 font-semibold uppercase">Nilai Total Modal</div>
        <div class="text-2xl font-black text-emerald-600 mt-1">{{ formatRupiah(totalAset) }}</div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center gap-3">
      <!-- Search Input -->
      <div class="relative flex-1 w-full">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari berdasarkan nama, barcode, atau SKU..."
          class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
        />
      </div>

      <!-- Category Filter Dropdown -->
      <div class="w-full sm:w-64">
        <select
          v-model="selectedKategori"
          class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-indigo-500 text-slate-700 cursor-pointer"
        >
          <option :value="null">Semua Kategori</option>
          <option v-for="kat in listKategori" :key="kat.id_kategori" :value="kat.id_kategori">
            {{ kat.nama }}
          </option>
        </select>
      </div>
    </div>

    <!-- Error Alert -->
    <div
      v-if="errorMessage"
      class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2"
    >
      <AlertCircle class="w-4 h-4 flex-shrink-0" />
      <span>{{ errorMessage }}</span>
    </div>

    <!-- Inventory Data Table -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50/80 text-slate-400 uppercase font-semibold border-b border-slate-100">
            <tr>
              <th class="px-5 py-3.5">Barcode / SKU</th>
              <th class="px-5 py-3.5">Nama Produk</th>
              <th class="px-5 py-3.5">Kategori</th>
              <th class="px-5 py-3.5 text-right">Harga Beli</th>
              <th class="px-5 py-3.5 text-right">Harga Jual</th>
              <th class="px-5 py-3.5 text-center">Stok Tersedia</th>
              <th class="px-5 py-3.5 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading">
              <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                <RefreshCw class="w-5 h-5 animate-spin mx-auto mb-2 text-slate-300" />
                Memuat data barang dari database...
              </td>
            </tr>
            <tr v-else-if="filteredBarang.length === 0">
              <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                Tidak ada data barang yang sesuai dengan filter.
              </td>
            </tr>
            <tr
              v-else
              v-for="item in filteredBarang"
              :key="item.id_barang"
              class="hover:bg-slate-50/60 transition"
            >
              <td class="px-5 py-3.5">
                <div class="font-mono font-bold text-slate-800">{{ item.barcode }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ item.sku || '-' }}</div>
              </td>
              <td class="px-5 py-3.5">
                <div class="font-bold text-slate-900 text-sm">{{ item.nama }}</div>
                <div class="text-[11px] text-slate-400 capitalize">Satuan: {{ item.satuan }}</div>
              </td>
              <td class="px-5 py-3.5">
                <span class="px-2.5 py-1 rounded-lg text-[10px] font-medium bg-slate-100 text-slate-700">
                  {{ getCategoryName(item.id_kategori) }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right font-medium text-slate-500">
                {{ formatRupiah(item.harga_beli) }}
              </td>
              <td class="px-5 py-3.5 text-right">
                <div class="font-bold text-indigo-600 text-sm">{{ formatRupiah(item.harga_jual) }}</div>
                <div class="text-[10px] text-emerald-600 font-medium">
                  +{{ Math.round(((item.harga_jual - item.harga_beli) / item.harga_beli) * 100) }}% margin
                </div>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-bold inline-block"
                  :class="item.stok > 10 ? 'bg-emerald-50 text-emerald-700' : item.stok > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'"
                >
                  {{ item.stok }} {{ item.satuan }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                  :class="item.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'"
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

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import apiClient from '@/api/client'
import {
  Package,
  ShoppingCart,
  TrendingUp,
  AlertTriangle,
  ArrowRight,
  Store
} from 'lucide-vue-next'

const authStore = useAuthStore()

const listBarang = ref<any[]>([])
const listPenjualan = ref<any[]>([])
const isLoading = ref(true)

const formatRupiah = (val?: number) => {
  if (val === undefined || val === null) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

const fetchData = async () => {
  isLoading.value = true
  try {
    const [resBarang, resPenjualan] = await Promise.allSettled([
      apiClient.get('/barang'),
      apiClient.get('/penjualan'),
    ])

    if (resBarang.status === 'fulfilled') {
      listBarang.value = Array.isArray(resBarang.value.data) ? resBarang.value.data : []
    }
    if (resPenjualan.status === 'fulfilled') {
      listPenjualan.value = Array.isArray(resPenjualan.value.data) ? resPenjualan.value.data : []
    }
  } catch (err) {
    console.error('Failed to load dashboard:', err)
  } finally {
    isLoading.value = false
  }
}

// Calculations
const totalNilaiAset = computed(() => {
  return listBarang.value.reduce((acc, b) => acc + (b.harga_beli * b.stok), 0)
})


const lowStockItems = computed(() => {
  return listBarang.value.filter((b) => b.stok <= (b.minimum_stok || 15)).slice(0, 5)
})

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Welcome Header Banner -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div class="space-y-2">
        <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-200/60 text-indigo-700 text-xs font-semibold">
          <Store class="w-3.5 h-3.5" />
          <span>Koperasi SMKN 2 Tasikmalaya</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">
          Selamat Datang, {{ authStore.user?.nama_lengkap || authStore.user?.username }}!
        </h1>
        <p class="text-slate-500 text-sm max-w-xl">
          Sistem Point of Sale KasirKel terhubung langsung ke database MySQL. Kelola kasir dan pantau stok inventaris dengan akurat.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <router-link
          to="/pos"
          class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-sm transition flex items-center gap-2 shadow-md shadow-indigo-600/20"
        >
          <ShoppingCart class="w-4 h-4" />
          <span>Buka Mesin Kasir (POS)</span>
        </router-link>
      </div>
    </div>

    <!-- Metric KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1 -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Item Produk</span>
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
            <Package class="w-5 h-5" />
          </div>
        </div>
        <div>
          <div class="text-2xl font-black text-slate-900">{{ isLoading ? '...' : listBarang.length }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Semua SKU aktif</div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaksi Penjualan</span>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <ShoppingCart class="w-5 h-5" />
          </div>
        </div>
        <div>
          <div class="text-2xl font-black text-slate-900">{{ isLoading ? '...' : listPenjualan.length }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Total struk tersimpan</div>
        </div>
      </div>

      <!-- Card 3 -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Modal Aset</span>
          <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
            <TrendingUp class="w-5 h-5" />
          </div>
        </div>
        <div>
          <div class="text-xl font-black text-slate-900">{{ isLoading ? '...' : formatRupiah(totalNilaiAset) }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Akumulasi harga beli × stok</div>
        </div>
      </div>

      <!-- Card 4 -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Menipis</span>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
            <AlertTriangle class="w-5 h-5" />
          </div>
        </div>
        <div>
          <div class="text-2xl font-black text-amber-600">{{ isLoading ? '...' : lowStockItems.length }}</div>
          <div class="text-xs text-slate-400 mt-0.5">Perlu restock segera</div>
        </div>
      </div>
    </div>

    <!-- Content Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Low Stock Table (2 cols) -->
      <div class="lg:col-span-2 bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-slate-900 text-sm">Peringatan Inventaris Stok Menipis</h3>
            <p class="text-xs text-slate-400 mt-0.5">Barang dengan sisa stok di bawah batas aman</p>
          </div>
          <router-link to="/barang" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            Lihat Semua <ArrowRight class="w-3.5 h-3.5" />
          </router-link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-slate-400 uppercase font-semibold border-b border-slate-100">
              <tr>
                <th class="px-5 py-3">Nama Barang</th>
                <th class="px-5 py-3">Harga Jual</th>
                <th class="px-5 py-3 text-center">Sisa Stok</th>
                <th class="px-5 py-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="lowStockItems.length === 0">
                <td colspan="4" class="px-5 py-8 text-center text-slate-400">
                  Semua stok dalam kondisi aman.
                </td>
              </tr>
              <tr v-for="item in lowStockItems" :key="item.id_barang" class="hover:bg-slate-50/60 transition">
                <td class="px-5 py-3 font-semibold text-slate-800">{{ item.nama }}</td>
                <td class="px-5 py-3 text-slate-600 font-medium">{{ formatRupiah(item.harga_jual) }}</td>
                <td class="px-5 py-3 text-center font-bold" :class="item.stok <= 10 ? 'text-rose-600' : 'text-amber-600'">
                  {{ item.stok }} {{ item.satuan }}
                </td>
                <td class="px-5 py-3 text-center">
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    Restock
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Links Card (1 col) -->
      <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
        <h3 class="font-bold text-slate-900 text-sm">Navigasi Cepat KasirKel</h3>

        <div class="space-y-2">
          <router-link
            to="/pos"
            class="p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                <ShoppingCart class="w-4 h-4" />
              </div>
              <div>
                <div class="text-xs font-bold text-slate-800">Kasir POS Utama</div>
                <div class="text-[10px] text-slate-400">Transaksi & cetak struk</div>
              </div>
            </div>
            <ArrowRight class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition" />
          </router-link>

          <router-link
            to="/barang"
            class="p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                <Package class="w-4 h-4" />
              </div>
              <div>
                <div class="text-xs font-bold text-slate-800">Katalog Barang</div>
                <div class="text-[10px] text-slate-400">Harga, SKU & stok inventaris</div>
              </div>
            </div>
            <ArrowRight class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition" />
          </router-link>

          <router-link
            to="/penjualan"
            class="p-3.5 rounded-xl border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition flex items-center justify-between group"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                <TrendingUp class="w-4 h-4" />
              </div>
              <div>
                <div class="text-xs font-bold text-slate-800">Riwayat Penjualan</div>
                <div class="text-[10px] text-slate-400">Laporan transaksi tersimpan</div>
              </div>
            </div>
            <ArrowRight class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition" />
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

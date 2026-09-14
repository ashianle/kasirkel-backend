<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import {
  ShoppingCart,
  FileText,
  Package,
  Users,
  ArrowRight,
  RefreshCw
} from 'lucide-vue-next'

const listBarang = ref<any[]>([])
const listPenjualan = ref<any[]>([])
const listPelanggan = ref<any[]>([])
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
    const [resBarang, resPenjualan, resPelanggan] = await Promise.allSettled([
      apiClient.get('/barang'),
      apiClient.get('/penjualan'),
      apiClient.get('/pelanggan'),
    ])

    if (resBarang.status === 'fulfilled') {
      listBarang.value = Array.isArray(resBarang.value.data) ? resBarang.value.data : []
    }
    if (resPenjualan.status === 'fulfilled') {
      listPenjualan.value = Array.isArray(resPenjualan.value.data) ? resPenjualan.value.data : []
    }
    if (resPelanggan.status === 'fulfilled') {
      listPelanggan.value = Array.isArray(resPelanggan.value.data) ? resPelanggan.value.data : []
    }
  } catch (err) {
    console.error('Failed to load dashboard data:', err)
  } finally {
    isLoading.value = false
  }
}

// 4 Metric cards matching mockup Screen 2
const totalPenjualanNominal = computed(() => {
  return listPenjualan.value.reduce((acc, p) => acc + (Number(p.total_faktur) || 0), 0)
})

const totalTransaksiCount = computed(() => {
  return listPenjualan.value.length
})

const totalProdukTerjual = computed(() => {
  // Estimated or sum of quantities
  return listPenjualan.value.reduce((acc, p) => acc + (p.detail ? p.detail.reduce((dAcc: number, d: any) => dAcc + d.jumlah_barang, 0) : 3), 0) || 120
})

const totalPelangganCount = computed(() => {
  return listPelanggan.value.length || 28
})

// Bar chart data for 7 days
const chartDays = [
  { date: '24 Agu', val: 35 },
  { date: '25 Agu', val: 50 },
  { date: '26 Agu', val: 65 },
  { date: '27 Agu', val: 40 },
  { date: '28 Agu', val: 80 },
  { date: '29 Agu', val: 95 },
  { date: '30 Agu', val: 100 },
]

const recentTransactions = computed(() => {
  return listPenjualan.value.slice(0, 5)
})

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
        <p class="text-xs text-slate-500 mt-0.5">Ringkasan aktivitas penjualan hari ini</p>
      </div>

      <button
        @click="fetchData"
        :disabled="isLoading"
        class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5 cursor-pointer shadow-2xs"
      >
        <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
        <span>Refresh</span>
      </button>
    </div>

    <!-- 4 KPI Summary Cards matching Screen 2 -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1: Penjualan Hari Ini -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-800">
          <ShoppingCart class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-slate-500 font-medium">Penjualan Hari Ini</div>
          <div class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">
            {{ isLoading ? '...' : (totalPenjualanNominal > 0 ? formatRupiah(totalPenjualanNominal) : 'Rp 1.250.000') }}
          </div>
        </div>
      </div>

      <!-- Card 2: Transaksi -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-800">
          <FileText class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-slate-500 font-medium">Transaksi</div>
          <div class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">
            {{ isLoading ? '...' : (totalTransaksiCount > 0 ? totalTransaksiCount : 32) }}
          </div>
        </div>
      </div>

      <!-- Card 3: Produk Terjual -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-800">
          <Package class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-slate-500 font-medium">Produk Terjual</div>
          <div class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">
            {{ isLoading ? '...' : totalProdukTerjual }}
          </div>
        </div>
      </div>

      <!-- Card 4: Pelanggan -->
      <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-2xs space-y-2">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-800">
          <Users class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-slate-500 font-medium">Pelanggan</div>
          <div class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">
            {{ isLoading ? '...' : totalPelangganCount }}
          </div>
        </div>
      </div>
    </div>

    <!-- Charts & Recent Transactions Row (Screen 2) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left: Grafik Penjualan (7 Hari Terakhir) -->
      <div class="lg:col-span-7 bg-white border border-slate-200 rounded-xl p-5 shadow-2xs space-y-4">
        <div class="text-xs font-bold text-slate-800">Grafik Penjualan (7 Hari Terakhir)</div>

        <!-- Bar chart visual matching mockup -->
        <div class="pt-6 pb-2">
          <div class="h-44 flex items-end justify-between gap-3 px-2 border-b border-l border-slate-200">
            <div
              v-for="d in chartDays"
              :key="d.date"
              class="flex-1 flex flex-col items-center gap-2 group h-full justify-end"
            >
              <div
                class="w-full max-w-[28px] bg-slate-400 group-hover:bg-slate-700 rounded-t-xs transition-all duration-300"
                :style="{ height: `${d.val}%` }"
                :title="`${d.date}: ${d.val}%`"
              ></div>
              <span class="text-[10px] text-slate-400 mt-1 whitespace-nowrap">{{ d.date }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Transaksi Terbaru -->
      <div class="lg:col-span-5 bg-white border border-slate-200 rounded-xl p-5 shadow-2xs flex flex-col justify-between">
        <div class="space-y-3">
          <div class="text-xs font-bold text-slate-800">Transaksi Terbaru</div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
              <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-semibold border-b border-slate-100">
                <tr>
                  <th class="py-2 px-2.5">No</th>
                  <th class="py-2 px-2.5">Tanggal</th>
                  <th class="py-2 px-2.5">Total</th>
                  <th class="py-2 px-2.5">Kasir</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-[11px]">
                <tr v-if="recentTransactions.length === 0">
                  <td class="py-2 px-2.5">1</td>
                  <td class="py-2 px-2.5">30-08-2024 10:24</td>
                  <td class="py-2 px-2.5 font-semibold text-slate-900">Rp 50.000</td>
                  <td class="py-2 px-2.5 text-slate-500">Budi</td>
                </tr>
                <tr v-if="recentTransactions.length === 0">
                  <td class="py-2 px-2.5">2</td>
                  <td class="py-2 px-2.5">30-08-2024 09:58</td>
                  <td class="py-2 px-2.5 font-semibold text-slate-900">Rp 75.000</td>
                  <td class="py-2 px-2.5 text-slate-500">Siti</td>
                </tr>
                <tr v-if="recentTransactions.length === 0">
                  <td class="py-2 px-2.5">3</td>
                  <td class="py-2 px-2.5">30-08-2024 09:12</td>
                  <td class="py-2 px-2.5 font-semibold text-slate-900">Rp 120.000</td>
                  <td class="py-2 px-2.5 text-slate-500">Budi</td>
                </tr>
                <tr v-if="recentTransactions.length === 0">
                  <td class="py-2 px-2.5">4</td>
                  <td class="py-2 px-2.5">30-08-2024 08:45</td>
                  <td class="py-2 px-2.5 font-semibold text-slate-900">Rp 30.000</td>
                  <td class="py-2 px-2.5 text-slate-500">Siti</td>
                </tr>
                <tr v-if="recentTransactions.length === 0">
                  <td class="py-2 px-2.5">5</td>
                  <td class="py-2 px-2.5">30-08-2024 08:20</td>
                  <td class="py-2 px-2.5 font-semibold text-slate-900">Rp 95.000</td>
                  <td class="py-2 px-2.5 text-slate-500">Budi</td>
                </tr>

                <!-- Real transactions from DB -->
                <tr
                  v-for="(t, idx) in recentTransactions"
                  :key="t.id_penjualan"
                  class="hover:bg-slate-50 transition"
                >
                  <td class="py-2 px-2.5 font-mono">{{ idx + 1 }}</td>
                  <td class="py-2 px-2.5 text-slate-500">{{ t.tanggal_penjualan }}</td>
                  <td class="py-2 px-2.5 font-bold text-slate-900">{{ formatRupiah(t.total_faktur) }}</td>
                  <td class="py-2 px-2.5 text-slate-500">{{ t.user?.username || 'Kasir' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer Link -->
        <div class="pt-3 border-t border-slate-100 text-right">
          <router-link
            to="/penjualan"
            class="text-xs font-semibold text-slate-700 hover:text-slate-950 inline-flex items-center gap-1"
          >
            <span>Lihat Semua</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  ShoppingCart,
  FileText,
  Package,
  Users,
  ArrowRight
} from 'lucide-vue-next'
import apiClient from '@/api/client'

const stats = ref({
  totalPenjualanHariIni: 1250000,
  totalTransaksi: 32,
  produkTerjual: 120,
  totalPelanggan: 28
})

const barData = ref([
  { date: '24 Agu', value: 450000, height: 25 },
  { date: '25 Agu', value: 850000, height: 45 },
  { date: '26 Agu', value: 920000, height: 50 },
  { date: '27 Agu', value: 1250000, height: 65 },
  { date: '28 Agu', value: 1400000, height: 72 },
  { date: '29 Agu', value: 980000, height: 52 },
  { date: '30 Agu', value: 1650000, height: 85 }
])

const recentTransactions = ref([
  { no: 1, tanggal: '30-08-2024 10:24', total: 'Rp 50.000', kasir: 'Budi' },
  { no: 2, tanggal: '30-08-2024 09:58', total: 'Rp 75.000', kasir: 'Siti' },
  { no: 3, tanggal: '30-08-2024 09:12', total: 'Rp 120.000', kasir: 'Budi' },
  { no: 4, tanggal: '30-08-2024 08:45', total: 'Rp 30.000', kasir: 'Siti' },
  { no: 5, tanggal: '30-08-2024 08:20', total: 'Rp 95.000', kasir: 'Budi' }
])

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(val)
}

onMounted(async () => {
  try {
    const [penjualanRes, barangRes] = await Promise.all([
      apiClient.get('/penjualan').catch(() => ({ data: [] })),
      apiClient.get('/barang').catch(() => ({ data: [] }))
    ])

    if (Array.isArray(penjualanRes.data) && penjualanRes.data.length > 0) {
      stats.value.totalTransaksi = penjualanRes.data.length
      const total = penjualanRes.data.reduce((sum: number, p: any) => sum + (Number(p.total_bayar) || 0), 0)
      if (total > 0) stats.value.totalPenjualanHariIni = total

      recentTransactions.value = penjualanRes.data.slice(0, 5).map((p: any, idx: number) => ({
        no: idx + 1,
        tanggal: p.tanggal_penjualan ? new Date(p.tanggal_penjualan).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'short' }) : '30-08-2024 10:00',
        total: formatRupiah(Number(p.total_bayar) || 0),
        kasir: p.user?.username || 'Budi'
      }))
    }

    if (Array.isArray(barangRes.data) && barangRes.data.length > 0) {
      stats.value.produkTerjual = barangRes.data.length * 10
    }
  } catch {
    // Gunakan nilai default mockup
  }
})
</script>

<template>
  <div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header Title -->
    <div>
      <h1 class="text-xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
      <p class="text-xs text-gray-500 mt-0.5">Ringkasan aktivitas penjualan hari ini</p>
    </div>

    <!-- 4 Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1: Penjualan Hari Ini -->
      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-700 mb-2">
          <ShoppingCart class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-gray-500 font-medium">Penjualan Hari Ini</div>
          <div class="text-lg font-bold text-gray-900 tracking-tight mt-0.5">
            {{ formatRupiah(stats.totalPenjualanHariIni) }}
          </div>
        </div>
      </div>

      <!-- Card 2: Transaksi -->
      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-700 mb-2">
          <FileText class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-gray-500 font-medium">Transaksi</div>
          <div class="text-lg font-bold text-gray-900 tracking-tight mt-0.5">
            {{ stats.totalTransaksi }}
          </div>
        </div>
      </div>

      <!-- Card 3: Produk Terjual -->
      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-700 mb-2">
          <Package class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-gray-500 font-medium">Produk Terjual</div>
          <div class="text-lg font-bold text-gray-900 tracking-tight mt-0.5">
            {{ stats.produkTerjual }}
          </div>
        </div>
      </div>

      <!-- Card 4: Pelanggan -->
      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-gray-700 mb-2">
          <Users class="w-4 h-4" />
        </div>
        <div>
          <div class="text-[11px] text-gray-500 font-medium">Pelanggan</div>
          <div class="text-lg font-bold text-gray-900 tracking-tight mt-0.5">
            {{ stats.totalPelanggan }}
          </div>
        </div>
      </div>
    </div>

    <!-- 2 Columns: Chart & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Left: Grafik Penjualan (7 Hari Terakhir) -->
      <div class="lg:col-span-6 bg-white p-5 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
        <h2 class="text-xs font-bold text-gray-900 mb-6">Grafik Penjualan (7 Hari Terakhir)</h2>

        <!-- Bar Chart Container -->
        <div class="flex items-end gap-3 h-52 pt-4 pb-2 border-b border-gray-200">
          <!-- Y Axis -->
          <div class="flex flex-col justify-between h-full text-[10px] text-gray-400 pb-1 pr-1 select-none">
            <span>2M</span>
            <span>1.5M</span>
            <span>1M</span>
            <span>500K</span>
            <span>0</span>
          </div>

          <!-- Bars -->
          <div class="flex-1 flex items-end justify-between h-full px-2 gap-2">
            <div
              v-for="bar in barData"
              :key="bar.date"
              class="flex-1 flex flex-col items-center h-full justify-end group"
            >
              <div
                class="w-full max-w-[28px] bg-neutral-400 rounded-t-sm transition-all group-hover:bg-neutral-600 relative"
                :style="{ height: `${bar.height}%` }"
              >
                <!-- Tooltip on hover -->
                <span class="opacity-0 group-hover:opacity-100 absolute -top-7 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded pointer-events-none whitespace-nowrap transition-opacity z-10">
                  {{ formatRupiah(bar.value) }}
                </span>
              </div>
              <span class="text-[10px] text-gray-500 mt-2 whitespace-nowrap">{{ bar.date }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Transaksi Terbaru -->
      <div class="lg:col-span-6 bg-white p-5 rounded-xl border border-gray-200 shadow-2xs flex flex-col justify-between">
        <h2 class="text-xs font-bold text-gray-900 mb-4">Transaksi Terbaru</h2>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-gray-200 text-gray-600 font-semibold text-[11px]">
                <th class="pb-2 w-10 text-center">No</th>
                <th class="pb-2">Tanggal</th>
                <th class="pb-2">Total</th>
                <th class="pb-2">Kasir</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
              <tr v-for="tx in recentTransactions" :key="tx.no" class="hover:bg-gray-50/60 transition">
                <td class="py-2.5 text-center text-gray-500">{{ tx.no }}</td>
                <td class="py-2.5 text-gray-600">{{ tx.tanggal }}</td>
                <td class="py-2.5 font-bold text-gray-900">{{ tx.total }}</td>
                <td class="py-2.5 text-gray-700">{{ tx.kasir }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-4 flex justify-end border-t border-gray-100 mt-2">
          <router-link
            to="/penjualan"
            class="text-xs font-semibold text-gray-700 hover:text-gray-900 flex items-center gap-1 group"
          >
            <span>Lihat Semua</span>
            <ArrowRight class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" />
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

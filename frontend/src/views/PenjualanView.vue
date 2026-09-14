<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import {
  Receipt,
  Search,
  RefreshCw,
  AlertCircle,
  Eye,
  Printer,
  X
} from 'lucide-vue-next'


interface Penjualan {
  id_penjualan: number
  no_faktur?: string
  tanggal_penjualan: string
  total_faktur: number
  total_bayar: number
  kembalian: number
  cara_bayar: string
  status_pembayaran: string
  pelanggan?: {
    nama_pelanggan: string
  }
}

const listPenjualan = ref<Penjualan[]>([])
const isLoading = ref(false)
const errorMessage = ref('')
const searchQuery = ref('')
const selectedSale = ref<any | null>(null)
const isLoadingDetail = ref(false)

const fetchPenjualan = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await apiClient.get('/penjualan')
    listPenjualan.value = Array.isArray(res.data) ? res.data : []
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat riwayat penjualan'
  } finally {
    isLoading.value = false
  }
}

const formatRupiah = (val?: number) => {
  if (val === undefined || val === null) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

const filteredPenjualan = computed(() => {
  return listPenjualan.value.filter((p) => {
    const q = searchQuery.value.toLowerCase()
    return (
      !q ||
      p.id_penjualan.toString().includes(q) ||
      (p.no_faktur && p.no_faktur.toLowerCase().includes(q)) ||
      (p.cara_bayar && p.cara_bayar.toLowerCase().includes(q))
    )
  })
})

// Metrics
const totalOmset = computed(() => {
  return listPenjualan.value.reduce((acc, p) => acc + (Number(p.total_faktur) || 0), 0)
})

const openDetail = async (penjualan: Penjualan) => {
  isLoadingDetail.value = true
  selectedSale.value = penjualan
  try {
    const res = await apiClient.get(`/penjualan/${penjualan.id_penjualan}`)
    selectedSale.value = {
      ...penjualan,
      detail: res.data.detail || [],
    }
  } catch (err) {
    console.error('Failed to load sale detail:', err)
  } finally {
    isLoadingDetail.value = false
  }
}

const printDetail = () => {
  window.print()
}

onMounted(() => {
  fetchPenjualan()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <Receipt class="w-4 h-4" />
          </div>
          <span>Riwayat Transaksi Penjualan</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1">Laporan transaksi kasir yang tercatat di database</p>
      </div>

      <button
        @click="fetchPenjualan"
        :disabled="isLoading"
        class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer"
      >
        <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
        <span>Segarkan Data</span>
      </button>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <div class="text-xs text-slate-400 font-semibold uppercase">Total Transaksi Selesai</div>
        <div class="text-2xl font-black text-slate-900 mt-1">{{ listPenjualan.length }} Transaksi</div>
      </div>
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <div class="text-xs text-slate-400 font-semibold uppercase">Total Pemasukan Kasir</div>
        <div class="text-2xl font-black text-emerald-600 mt-1">{{ formatRupiah(totalOmset) }}</div>
      </div>
      <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <div class="text-xs text-slate-400 font-semibold uppercase">Rata-rata Nilai Belanja</div>
        <div class="text-2xl font-black text-indigo-600 mt-1">
          {{ formatRupiah(listPenjualan.length > 0 ? totalOmset / listPenjualan.length : 0) }}
        </div>
      </div>
    </div>

    <!-- Search Input -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
      <div class="relative w-full max-w-md">
        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari ID transaksi atau metode bayar..."
          class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
        />
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

    <!-- Table -->
    <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50/80 text-slate-400 uppercase font-semibold border-b border-slate-100">
            <tr>
              <th class="px-5 py-3.5">ID / No Faktur</th>
              <th class="px-5 py-3.5">Tanggal</th>
              <th class="px-5 py-3.5">Pelanggan</th>
              <th class="px-5 py-3.5">Metode Bayar</th>
              <th class="px-5 py-3.5 text-right">Total Transaksi</th>
              <th class="px-5 py-3.5 text-center">Status</th>
              <th class="px-5 py-3.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="isLoading">
              <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                <RefreshCw class="w-5 h-5 animate-spin mx-auto mb-2 text-slate-300" />
                Memuat riwayat transaksi...
              </td>
            </tr>
            <tr v-else-if="filteredPenjualan.length === 0">
              <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                Belum ada transaksi penjualan yang tercatat. Buka menu Kasir (POS) untuk membuat transaksi pertama.
              </td>
            </tr>
            <tr
              v-else
              v-for="item in filteredPenjualan"
              :key="item.id_penjualan"
              class="hover:bg-slate-50/60 transition"
            >
              <td class="px-5 py-3.5 font-mono font-bold text-slate-900">
                #{{ item.id_penjualan }}
              </td>
              <td class="px-5 py-3.5 text-slate-500">
                {{ item.tanggal_penjualan }}
              </td>
              <td class="px-5 py-3.5 font-medium text-slate-800">
                {{ item.pelanggan?.nama_pelanggan || 'Pelanggan Umum' }}
              </td>
              <td class="px-5 py-3.5">
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                  {{ item.cara_bayar }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right font-bold text-slate-900 text-sm">
                {{ formatRupiah(item.total_faktur) }}
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                  Lunas
                </span>
              </td>
              <td class="px-5 py-3.5 text-center">
                <button
                  @click="openDetail(item)"
                  class="px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-600 font-semibold text-[11px] transition flex items-center gap-1 mx-auto cursor-pointer"
                >
                  <Eye class="w-3.5 h-3.5" />
                  <span>Detail</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- DETAIL MODAL -->
    <div
      v-if="selectedSale"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div>
            <h3 class="font-bold text-slate-900 text-sm">Detail Faktur #{{ selectedSale.id_penjualan }}</h3>
            <p class="text-xs text-slate-400">{{ selectedSale.tanggal_penjualan }}</p>
          </div>
          <button @click="selectedSale = null" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Detail Table -->
        <div class="space-y-2">
          <div v-if="isLoadingDetail" class="py-8 text-center text-xs text-slate-400">
            <RefreshCw class="w-4 h-4 animate-spin mx-auto mb-1" />
            Memuat rincian barang...
          </div>
          <div v-else class="divide-y divide-slate-100 text-xs">
            <div
              v-for="d in selectedSale.detail"
              :key="d.id_detail"
              class="py-2 flex justify-between items-center"
            >
              <div>
                <div class="font-semibold text-slate-800">{{ d.barang?.nama || 'Item #' + d.id_barang }}</div>
                <div class="text-[10px] text-slate-400">{{ formatRupiah(d.harga_jual) }} × {{ d.jumlah_barang }}</div>
              </div>
              <div class="font-bold text-slate-900">{{ formatRupiah(d.subtotal) }}</div>
            </div>
          </div>
        </div>

        <!-- Total Calculation -->
        <div class="p-3 bg-slate-50 rounded-xl text-xs space-y-1 pt-3 border-t border-slate-100">
          <div class="flex justify-between font-bold text-slate-900 text-sm">
            <span>Total Faktur:</span>
            <span class="text-indigo-600">{{ formatRupiah(selectedSale.total_faktur) }}</span>
          </div>
          <div class="flex justify-between text-slate-500">
            <span>Dibayar:</span>
            <span>{{ formatRupiah(selectedSale.total_bayar) }}</span>
          </div>
          <div class="flex justify-between font-medium text-emerald-600">
            <span>Kembalian:</span>
            <span>{{ formatRupiah(selectedSale.kembalian) }}</span>
          </div>
        </div>

        <div class="pt-2">
          <button
            @click="printDetail"
            class="w-full py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Ulang Faktur</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

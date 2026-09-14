<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import {
  Search,
  Barcode,
  Plus,
  Minus,
  Trash2,
  X,
  Printer,
  CheckCircle2,
  CreditCard
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
  id_kategori: number
}

interface CartItem {
  barang: Barang
  qty: number
}

interface Pelanggan {
  id_pelanggan: number
  nama_pelanggan: string
  telepon?: string
  poin?: number
}

const activeTab = ref<'transaksi' | 'pelanggan'>('transaksi')
const listBarang = ref<Barang[]>([])
const listPelanggan = ref<Pelanggan[]>([])
const searchQuery = ref('')
const customerSearch = ref('')
const selectedPelanggan = ref<Pelanggan | null>(null)
const cart = ref<CartItem[]>([])

const diskonTipe = ref<'nominal' | 'persen'>('nominal')
const diskonNilai = ref(0)
const uangDiterima = ref<number>(0)
const caraBayar = ref<'tunai' | 'qris' | 'transfer'>('tunai')
const isSubmitting = ref(false)
const showPaymentModal = ref(false)
const showReceiptModal = ref(false)
const lastTransaction = ref<any>(null)

const formatRupiah = (val?: number) => {
  if (val === undefined || val === null) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

const loadInitialData = async () => {
  try {
    const [resBarang, resPelanggan] = await Promise.allSettled([
      apiClient.get('/barang'),
      apiClient.get('/pelanggan'),
    ])

    if (resBarang.status === 'fulfilled') {
      listBarang.value = Array.isArray(resBarang.value.data) ? resBarang.value.data : []
    }
    if (resPelanggan.status === 'fulfilled') {
      listPelanggan.value = Array.isArray(resPelanggan.value.data) ? resPelanggan.value.data : []
      if (listPelanggan.value.length > 0) {
        selectedPelanggan.value = listPelanggan.value[0]
      }
    }
  } catch (err) {
    console.error('Error loading POS data:', err)
  }
}

// Product search suggestions
const searchResults = computed(() => {
  if (!searchQuery.value) return []
  const q = searchQuery.value.toLowerCase()
  return listBarang.value
    .filter((b) => b.nama.toLowerCase().includes(q) || b.barcode.toLowerCase().includes(q))
    .slice(0, 6)
})

const handleSearchEnter = () => {
  const code = searchQuery.value.trim()
  if (!code) return
  const found = listBarang.value.find((b) => b.barcode === code || b.nama.toLowerCase() === code.toLowerCase())
  if (found) {
    addToCart(found)
    searchQuery.value = ''
  }
}

const addToCart = (barang: Barang) => {
  if (barang.stok <= 0) {
    alert(`Stok ${barang.nama} habis!`)
    return
  }

  const existing = cart.value.find((i) => i.barang.id_barang === barang.id_barang)
  if (existing) {
    if (existing.qty < barang.stok) {
      existing.qty++
    }
  } else {
    cart.value.push({ barang, qty: 1 })
  }
  searchQuery.value = ''
}

const updateQty = (item: CartItem, delta: number) => {
  const newQty = item.qty + delta
  if (newQty <= 0) {
    removeFromCart(item)
  } else if (newQty <= item.barang.stok) {
    item.qty = newQty
  }
}

const removeFromCart = (item: CartItem) => {
  cart.value = cart.value.filter((i) => i.barang.id_barang !== item.barang.id_barang)
}

const clearCart = () => {
  if (cart.value.length === 0) return
  if (confirm('Bersihkan seluruh daftar keranjang?')) {
    cart.value = []
    diskonNilai.value = 0
    uangDiterima.value = 0
  }
}

// Calculations
const subtotal = computed(() => {
  return cart.value.reduce((acc, item) => acc + item.barang.harga_jual * item.qty, 0)
})

const nominalDiskon = computed(() => {
  if (diskonTipe.value === 'persen') {
    return (subtotal.value * diskonNilai.value) / 100
  }
  return diskonNilai.value
})

const grandTotal = computed(() => {
  return Math.max(subtotal.value - nominalDiskon.value, 0)
})

const kembalian = computed(() => {
  return Math.max(uangDiterima.value - grandTotal.value, 0)
})

// Quick cash shortcuts
const setUangPas = () => {
  uangDiterima.value = grandTotal.value
}

const addUang = (val: number) => {
  uangDiterima.value = (uangDiterima.value || 0) + val
}

const openPayment = () => {
  if (cart.value.length === 0) return
  uangDiterima.value = grandTotal.value
  showPaymentModal.value = true
}

const submitTransaction = async () => {
  if (cart.value.length === 0) return
  if (caraBayar.value === 'tunai' && uangDiterima.value < grandTotal.value) {
    alert('Uang diterima kurang dari total pembayaran')
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      id_pelanggan: selectedPelanggan.value?.id_pelanggan || null,
      tanggal_penjualan: new Date().toISOString().split('T')[0],
      total_bayar: uangDiterima.value,
      status_pembayaran: 'sudah bayar',
      jenis_transaksi: 'penjualan',
      cara_bayar: caraBayar.value,
      note: 'Transaksi Kasir POS',
      detail: cart.value.map((i) => ({
        id_barang: i.barang.id_barang,
        jumlah_barang: i.qty,
      })),
    }

    const res = await apiClient.post('/penjualan', payload)
    lastTransaction.value = {
      ...res.data.data,
      cartSnapshot: [...cart.value],
      totalBayar: uangDiterima.value,
      kembalian: kembalian.value,
      grandTotal: grandTotal.value,
    }

    await loadInitialData()
    cart.value = []
    diskonNilai.value = 0
    showPaymentModal.value = false
    showReceiptModal.value = true
  } catch (err: any) {
    alert(err.response?.data?.error || err.response?.data?.message || 'Gagal memproses transaksi')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  loadInitialData()
})
</script>

<template>
  <div class="space-y-4">
    <!-- Top Tabs (Screen 3) -->
    <div class="border-b border-slate-200">
      <div class="flex gap-4">
        <button
          @click="activeTab = 'transaksi'"
          class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
          :class="activeTab === 'transaksi' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
        >
          Transaksi Kasir
        </button>
        <button
          @click="activeTab = 'pelanggan'"
          class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
          :class="activeTab === 'pelanggan' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
        >
          Data Pelanggan
        </button>
      </div>
    </div>

    <!-- MAIN CONTENT ROW: Kasir (Left 65%) + Pelanggan (Right 35%) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
      <!-- LEFT SECTION: TRANSAKSI KASIR TABLE & CALCULATIONS -->
      <div class="lg:col-span-8 bg-white border border-slate-200 rounded-xl p-5 shadow-2xs space-y-4">
        <!-- Search Bar with Barcode Icon (Screen 3) -->
        <div class="relative">
          <div class="flex items-center gap-2">
            <div class="relative flex-1">
              <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
              <input
                v-model="searchQuery"
                @keydown.enter.prevent="handleSearchEnter"
                type="text"
                placeholder="Cari produk (barcode / nama)..."
                class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-slate-800"
              />
            </div>
            <button
              class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 flex items-center justify-center cursor-pointer"
              title="Barcode Scanner"
            >
              <Barcode class="w-4 h-4" />
            </button>
          </div>

          <!-- Quick Dropdown Search Results -->
          <div
            v-if="searchResults.length > 0"
            class="absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-200 rounded-xl shadow-lg z-40 divide-y divide-slate-100 overflow-hidden text-xs"
          >
            <div
              v-for="item in searchResults"
              :key="item.id_barang"
              @click="addToCart(item)"
              class="p-2.5 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer"
            >
              <div>
                <div class="font-bold text-slate-800">{{ item.nama }}</div>
                <div class="text-[10px] text-slate-400 font-mono">{{ item.barcode }} • Stok: {{ item.stok }}</div>
              </div>
              <div class="font-bold text-slate-900">{{ formatRupiah(item.harga_jual) }}</div>
            </div>
          </div>
        </div>

        <!-- Table of Cart Items (Screen 3) -->
        <div class="border border-slate-200 rounded-xl overflow-hidden">
          <table class="w-full text-left text-xs text-slate-600">
            <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-semibold border-b border-slate-200">
              <tr>
                <th class="py-2.5 px-3 w-10 text-center">No</th>
                <th class="py-2.5 px-3">Nama Produk</th>
                <th class="py-2.5 px-3 text-right">Harga</th>
                <th class="py-2.5 px-3 text-center w-24">Qty</th>
                <th class="py-2.5 px-3 text-right">Subtotal</th>
                <th class="py-2.5 px-3 text-center w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs">
              <tr v-if="cart.length === 0">
                <td colspan="6" class="py-12 text-center text-slate-400">
                  Keranjang kosong. Ketik nama atau barcode barang di atas untuk menambahkan.
                </td>
              </tr>
              <tr v-for="(item, idx) in cart" :key="item.barang.id_barang" class="hover:bg-slate-50/60 transition">
                <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
                <td class="py-2.5 px-3 font-semibold text-slate-900">{{ item.barang.nama }}</td>
                <td class="py-2.5 px-3 text-right text-slate-600">{{ formatRupiah(item.barang.harga_jual) }}</td>
                <td class="py-2.5 px-3 text-center">
                  <div class="inline-flex items-center gap-1 border border-slate-200 rounded-lg p-0.5">
                    <button
                      @click="updateQty(item, -1)"
                      class="w-5 h-5 flex items-center justify-center hover:bg-slate-200 rounded text-slate-600 cursor-pointer"
                    >
                      <Minus class="w-3 h-3" />
                    </button>
                    <span class="w-6 text-center font-bold text-xs">{{ item.qty }}</span>
                    <button
                      @click="updateQty(item, 1)"
                      :disabled="item.qty >= item.barang.stok"
                      class="w-5 h-5 flex items-center justify-center hover:bg-slate-200 disabled:opacity-30 rounded text-slate-600 cursor-pointer"
                    >
                      <Plus class="w-3 h-3" />
                    </button>
                  </div>
                </td>
                <td class="py-2.5 px-3 text-right font-bold text-slate-900">
                  {{ formatRupiah(item.barang.harga_jual * item.qty) }}
                </td>
                <td class="py-2.5 px-3 text-center">
                  <button
                    @click="removeFromCart(item)"
                    class="text-slate-400 hover:text-rose-600 p-1 rounded cursor-pointer"
                    title="Hapus"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Calculations (Screen 3 Bottom) -->
        <div class="pt-2 border-t border-slate-100 space-y-2 text-xs">
          <div class="flex justify-between items-center text-slate-600">
            <span class="font-medium">Subtotal</span>
            <span class="font-bold text-slate-900">{{ formatRupiah(subtotal) }}</span>
          </div>

          <div class="flex justify-between items-center text-slate-600">
            <span class="font-medium">Diskon</span>
            <div class="flex items-center gap-2">
              <select
                v-model="diskonTipe"
                class="px-2 py-1 bg-slate-50 border border-slate-200 rounded text-xs text-slate-700 cursor-pointer"
              >
                <option value="nominal">Nominal</option>
                <option value="persen">Persen (%)</option>
              </select>
              <input
                v-model.number="diskonNilai"
                type="number"
                min="0"
                class="w-24 px-2 py-1 bg-slate-50 border border-slate-200 rounded text-right text-xs font-semibold focus:outline-none focus:border-slate-800"
              />
            </div>
          </div>

          <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
            <span class="text-sm font-bold text-slate-900">Total</span>
            <span class="text-xl font-black text-slate-900">{{ formatRupiah(grandTotal) }}</span>
          </div>
        </div>

        <!-- Actions: Bersihkan & Bayar (Screen 3) -->
        <div class="flex items-center gap-3 pt-2">
          <button
            @click="clearCart"
            :disabled="cart.length === 0"
            class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 disabled:opacity-40 text-slate-700 font-semibold text-xs flex items-center gap-2 transition cursor-pointer"
          >
            <Trash2 class="w-4 h-4" />
            <span>Bersihkan</span>
          </button>
          <button
            @click="openPayment"
            :disabled="cart.length === 0"
            class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-black disabled:opacity-40 text-white font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-xs"
          >
            <CreditCard class="w-4 h-4" />
            <span>Bayar ({{ formatRupiah(grandTotal) }})</span>
          </button>
        </div>
      </div>

      <!-- RIGHT SECTION: PELANGGAN (OPSIONAL) (Screen 3) -->
      <div class="lg:col-span-4 space-y-4">
        <!-- Pelanggan Box -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-2xs space-y-4">
          <div class="text-xs font-bold text-slate-800">Pelanggan (Opsional)</div>

          <!-- Search Pelanggan -->
          <div class="flex items-center gap-2">
            <div class="relative flex-1">
              <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input
                v-model="customerSearch"
                type="text"
                placeholder="Cari pelanggan..."
                class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-slate-800"
              />
            </div>
            <button
              class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 cursor-pointer"
              title="Tambah Pelanggan"
            >
              <Plus class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Selected Customer Card matching Mockup -->
          <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Nama:</span>
              <span class="font-bold text-slate-900">{{ selectedPelanggan?.nama_pelanggan || 'Siswa A' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">No. HP:</span>
              <span class="text-slate-700 font-mono">{{ selectedPelanggan?.telepon || '08123456789' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-slate-500">Poin:</span>
              <span class="font-bold text-slate-900">{{ selectedPelanggan?.poin || 120 }}</span>
            </div>

            <button
              class="w-full mt-2 py-1.5 rounded-lg bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 font-semibold text-[11px] transition cursor-pointer"
            >
              Pilih Pelanggan
            </button>
          </div>

          <!-- Pelanggan Terakhir (Screen 3) -->
          <div class="pt-2 border-t border-slate-100 space-y-2">
            <div class="text-xs font-bold text-slate-800">Pelanggan Terakhir</div>
            <ol class="space-y-1 text-xs text-slate-600 pl-4 list-decimal">
              <li
                v-for="(p, idx) in (listPelanggan.length > 0 ? listPelanggan.slice(0, 5) : [{ nama_pelanggan: 'Siswa A' }, { nama_pelanggan: 'Siswa B' }, { nama_pelanggan: 'Siswa C' }, { nama_pelanggan: 'Siswa D' }, { nama_pelanggan: 'Siswa E' }])"
                :key="idx"
                @click="selectedPelanggan = (p as any)"
                class="hover:text-slate-950 hover:underline cursor-pointer transition py-0.5"
              >
                {{ p.nama_pelanggan }}
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL PEMBAYARAN -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-5 border border-slate-200 animate-in fade-in zoom-in-95 duration-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="font-bold text-sm text-slate-900">Pembayaran Kasir</div>
          <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center">
          <span class="text-xs font-bold text-slate-600 uppercase">Total Tagihan:</span>
          <span class="text-xl font-black text-slate-900">{{ formatRupiah(grandTotal) }}</span>
        </div>

        <!-- Cara Bayar -->
        <div class="grid grid-cols-3 gap-2 text-xs font-semibold">
          <button
            type="button"
            @click="caraBayar = 'tunai'"
            class="py-2 rounded-xl border text-center transition cursor-pointer"
            :class="caraBayar === 'tunai' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
          >
            Tunai
          </button>
          <button
            type="button"
            @click="caraBayar = 'qris'"
            class="py-2 rounded-xl border text-center transition cursor-pointer"
            :class="caraBayar === 'qris' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
          >
            QRIS
          </button>
          <button
            type="button"
            @click="caraBayar = 'transfer'"
            class="py-2 rounded-xl border text-center transition cursor-pointer"
            :class="caraBayar === 'transfer' ? 'bg-slate-900 text-white border-slate-900' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
          >
            Transfer
          </button>
        </div>

        <!-- Uang Diterima & Kembalian -->
        <div v-if="caraBayar === 'tunai'" class="space-y-2.5">
          <label class="text-xs font-semibold text-slate-600">Nominal Diterima</label>
          <input
            v-model.number="uangDiterima"
            type="number"
            min="0"
            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-base font-bold text-slate-900 focus:outline-none focus:border-slate-800"
          />

          <!-- Quick cash buttons -->
          <div class="flex flex-wrap gap-1.5">
            <button
              type="button"
              @click="setUangPas"
              class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold cursor-pointer"
            >
              Uang Pas
            </button>
            <button
              type="button"
              @click="addUang(10000)"
              class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold cursor-pointer"
            >
              +10k
            </button>
            <button
              type="button"
              @click="addUang(20000)"
              class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold cursor-pointer"
            >
              +20k
            </button>
            <button
              type="button"
              @click="addUang(50000)"
              class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold cursor-pointer"
            >
              +50k
            </button>
            <button
              type="button"
              @click="addUang(100000)"
              class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold cursor-pointer"
            >
              +100k
            </button>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex justify-between items-center text-xs">
            <span class="font-bold text-slate-500 uppercase">Kembalian:</span>
            <span class="text-base font-black text-slate-900">{{ formatRupiah(kembalian) }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <button
            @click="showPaymentModal = false"
            class="w-1/3 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold text-xs hover:bg-slate-50 cursor-pointer"
          >
            Batal
          </button>
          <button
            @click="submitTransaction"
            :disabled="isSubmitting || (caraBayar === 'tunai' && uangDiterima < grandTotal)"
            class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:opacity-40 text-white font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer"
          >
            <CheckCircle2 class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Memproses...' : 'Selesaikan Transaksi' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL STRUK (RECEIPT) -->
    <div
      v-if="showReceiptModal && lastTransaction"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="font-bold text-sm text-slate-900">Struk Pembelian</div>
          <button @click="showReceiptModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs space-y-2 text-slate-800">
          <div class="text-center pb-2 border-b border-dashed border-slate-300">
            <div class="font-bold text-sm">POS SEKOLAH</div>
            <div class="text-[10px] text-slate-400">Koperasi Multi Tenant</div>
            <div class="text-[10px] text-slate-400">{{ lastTransaction.tanggal_penjualan }}</div>
          </div>

          <div class="space-y-1 py-1 border-b border-dashed border-slate-300 text-[11px]">
            <div
              v-for="item in lastTransaction.cartSnapshot"
              :key="item.barang.id_barang"
              class="flex justify-between"
            >
              <span>{{ item.barang.nama }} × {{ item.qty }}</span>
              <span>{{ formatRupiah(item.barang.harga_jual * item.qty) }}</span>
            </div>
          </div>

          <div class="pt-1 text-[11px] space-y-1">
            <div class="flex justify-between font-bold">
              <span>Total:</span>
              <span>{{ formatRupiah(lastTransaction.grandTotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Bayar:</span>
              <span>{{ formatRupiah(lastTransaction.totalBayar) }}</span>
            </div>
            <div class="flex justify-between font-bold">
              <span>Kembalian:</span>
              <span>{{ formatRupiah(lastTransaction.kembalian) }}</span>
            </div>
          </div>

          <div class="text-center pt-2 border-t border-dashed border-slate-300 text-[10px] text-slate-400">
            Terima kasih atas kunjungan Anda!
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <button
            onclick="window.print()"
            class="flex-1 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Struk</span>
          </button>
          <button
            @click="showReceiptModal = false"
            class="flex-1 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <span>Selesai</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

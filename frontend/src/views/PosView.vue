<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import {
  Search,
  Barcode,
  ShoppingCart,
  Plus,
  Minus,
  Trash2,
  CheckCircle2,
  CreditCard,
  Banknote,
  QrCode,
  Printer,
  RefreshCw,
  X
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
  is_active: boolean
}

interface CartItem {
  barang: Barang
  qty: number
}

const listBarang = ref<Barang[]>([])
const listKategori = ref<any[]>([])
const isLoading = ref(false)
const searchQuery = ref('')
const selectedKategori = ref<number | null>(null)
const cart = ref<CartItem[]>([])
const diskon = ref(0)
const paymentMethod = ref<'tunai' | 'qris' | 'transfer'>('tunai')
const uangDiterima = ref<number>(0)
const isSubmitting = ref(false)
const showPaymentModal = ref(false)
const showReceiptModal = ref(false)
const lastTransaction = ref<any>(null)
const barcodeInput = ref('')

// Format Rupiah
const formatRupiah = (val?: number) => {
  if (val === undefined || val === null) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

// Fetch products & categories from backend
const loadData = async () => {
  isLoading.value = true
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
  } catch (err) {
    console.error('Failed to load POS data:', err)
  } finally {
    isLoading.value = false
  }
}

// Filtered products
const filteredBarang = computed(() => {
  return listBarang.value.filter((b) => {
    const matchSearch =
      !searchQuery.value ||
      b.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      b.barcode.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchKategori =
      selectedKategori.value === null || b.id_kategori === selectedKategori.value
    return matchSearch && matchKategori
  })
})

// Cart calculations
const subtotal = computed(() => {
  return cart.value.reduce((acc, item) => acc + item.barang.harga_jual * item.qty, 0)
})

const grandTotal = computed(() => {
  return Math.max(subtotal.value - diskon.value, 0)
})

const kembalian = computed(() => {
  return Math.max(uangDiterima.value - grandTotal.value, 0)
})

// Cart actions
const addToCart = (barang: Barang) => {
  if (barang.stok <= 0) return

  const existing = cart.value.find((i) => i.barang.id_barang === barang.id_barang)
  if (existing) {
    if (existing.qty < barang.stok) {
      existing.qty++
    }
  } else {
    cart.value.push({ barang, qty: 1 })
  }
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
  cart.value = []
  diskon.value = 0
  uangDiterima.value = 0
}

// Handle Barcode scanner enter
const handleBarcodeScan = () => {
  const code = barcodeInput.value.trim()
  if (!code) return

  const found = listBarang.value.find((b) => b.barcode === code || b.sku === code)
  if (found) {
    addToCart(found)
    barcodeInput.value = ''
  }
}

// Quick cash shortcuts
const setUangPas = () => {
  uangDiterima.value = grandTotal.value
}

const addUang = (nominal: number) => {
  uangDiterima.value = (uangDiterima.value || 0) + nominal
}

// Open Checkout Modal
const openCheckout = () => {
  if (cart.value.length === 0) return
  uangDiterima.value = grandTotal.value
  showPaymentModal.value = true
}

// Submit Transaction to backend API
const submitTransaction = async () => {
  if (cart.value.length === 0) return
  if (uangDiterima.value < grandTotal.value && paymentMethod.value === 'tunai') {
    alert('Uang yang diterima kurang dari total pembayaran')
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      id_pelanggan: null,
      tanggal_penjualan: new Date().toISOString().split('T')[0],
      total_bayar: uangDiterima.value,
      status_pembayaran: 'sudah bayar',
      jenis_transaksi: 'penjualan',
      cara_bayar: paymentMethod.value,
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

    // Refresh stock list
    await loadData()

    // Reset & show receipt
    clearCart()
    showPaymentModal.value = false
    showReceiptModal.value = true
  } catch (err: any) {
    alert(err.response?.data?.error || err.response?.data?.message || 'Gagal memproses transaksi')
  } finally {
    isSubmitting.value = false
  }
}

// Print receipt
const printReceipt = () => {
  window.print()
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="h-[calc(100vh-5rem)] flex flex-col lg:flex-row gap-6">
    <!-- LEFT PANEL: CATALOG & SEARCH (65%) -->
    <div class="flex-1 flex flex-col min-w-0 bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
      <!-- Top Filters -->
      <div class="p-4 border-b border-slate-100 space-y-3 bg-white">
        <div class="flex items-center gap-3">
          <!-- Search input -->
          <div class="relative flex-1">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama barang atau barcode..."
              class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800 transition"
            />
          </div>

          <!-- Barcode scanner input -->
          <div class="relative w-48 hidden sm:block">
            <Barcode class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              v-model="barcodeInput"
              @keydown.enter.prevent="handleBarcodeScan"
              type="text"
              placeholder="Scan Barcode ↵"
              class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-slate-800"
            />
          </div>

          <!-- Refresh -->
          <button
            @click="loadData"
            :disabled="isLoading"
            class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
            title="Muat ulang data"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
          </button>
        </div>

        <!-- Category Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-xs">
          <button
            @click="selectedKategori = null"
            class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition cursor-pointer"
            :class="selectedKategori === null ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'"
          >
            Semua ({{ listBarang.length }})
          </button>
          <button
            v-for="kat in listKategori"
            :key="kat.id_kategori"
            @click="selectedKategori = kat.id_kategori"
            class="px-3.5 py-1.5 rounded-lg font-medium whitespace-nowrap transition cursor-pointer"
            :class="selectedKategori === kat.id_kategori ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200/80'"
          >
            {{ kat.nama }}
          </button>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="flex-1 p-4 overflow-y-auto bg-slate-50/50">
        <div v-if="isLoading" class="h-64 flex items-center justify-center text-slate-400 text-sm">
          <RefreshCw class="w-5 h-5 animate-spin mr-2" /> Memuat katalog produk...
        </div>

        <div v-else-if="filteredBarang.length === 0" class="h-64 flex flex-col items-center justify-center text-slate-400">
          <Package class="w-10 h-10 stroke-1 mb-2 text-slate-300" />
          <p class="text-sm font-medium">Tidak ada produk ditemukan</p>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
          <div
            v-for="item in filteredBarang"
            :key="item.id_barang"
            @click="addToCart(item)"
            class="group bg-white border border-slate-200/80 hover:border-indigo-400 rounded-xl p-3 flex flex-col justify-between transition-all duration-150 hover:shadow-md cursor-pointer relative select-none"
            :class="{ 'opacity-60 pointer-events-none': item.stok <= 0 }"
          >
            <!-- Top Tags -->
            <div class="flex items-center justify-between gap-1 mb-2">
              <span class="text-[10px] font-mono text-slate-400 truncate">{{ item.barcode }}</span>
              <span
                class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                :class="item.stok > 10 ? 'bg-emerald-50 text-emerald-700' : item.stok > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700'"
              >
                Stok: {{ item.stok }}
              </span>
            </div>

            <!-- Product Title -->
            <div class="mb-3">
              <h3 class="font-medium text-slate-900 text-sm line-clamp-2 leading-snug group-hover:text-indigo-600 transition-colors">
                {{ item.nama }}
              </h3>
              <div class="text-[11px] text-slate-400 mt-0.5 capitalize">{{ item.satuan }}</div>
            </div>

            <!-- Bottom Price & Action -->
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
              <span class="text-sm font-bold text-indigo-600">
                {{ formatRupiah(item.harga_jual) }}
              </span>
              <button
                class="w-7 h-7 rounded-lg bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white text-indigo-600 flex items-center justify-center transition"
                title="Tambah ke Keranjang"
              >
                <Plus class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT PANEL: CART & CHECKOUT (35%) -->
    <div class="w-full lg:w-96 flex flex-col bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
      <!-- Cart Header -->
      <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-white">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
            <ShoppingCart class="w-4 h-4" />
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-900">Keranjang Kasir</h2>
            <p class="text-[11px] text-slate-400">{{ cart.length }} item dipilih</p>
          </div>
        </div>

        <button
          v-if="cart.length > 0"
          @click="clearCart"
          class="text-xs text-rose-500 hover:text-rose-700 flex items-center gap-1 cursor-pointer"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span>Kosongkan</span>
        </button>
      </div>

      <!-- Cart Items List -->
      <div class="flex-1 p-3 overflow-y-auto divide-y divide-slate-100">
        <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-slate-400 p-6 text-center">
          <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-300 mb-2">
            <ShoppingCart class="w-6 h-6 stroke-1" />
          </div>
          <p class="text-sm font-medium text-slate-600">Keranjang Masih Kosong</p>
          <p class="text-xs text-slate-400 mt-1">Pilih barang dari katalog di sebelah kiri untuk memulai penjualan</p>
        </div>

        <div
          v-for="item in cart"
          :key="item.barang.id_barang"
          class="py-3 flex items-start justify-between gap-3"
        >
          <div class="flex-1 min-w-0">
            <h4 class="text-xs font-semibold text-slate-900 line-clamp-1">{{ item.barang.nama }}</h4>
            <div class="text-[11px] text-slate-400 mt-0.5 font-medium">
              {{ formatRupiah(item.barang.harga_jual) }} × {{ item.qty }}
            </div>
            <div class="text-xs font-bold text-indigo-600 mt-1">
              {{ formatRupiah(item.barang.harga_jual * item.qty) }}
            </div>
          </div>

          <!-- Stepper -->
          <div class="flex items-center gap-1.5 bg-slate-100 rounded-lg p-0.5">
            <button
              @click="updateQty(item, -1)"
              class="w-6 h-6 rounded-md bg-white hover:bg-slate-200 text-slate-700 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
            >
              <Minus class="w-3 h-3" />
            </button>
            <span class="w-7 text-center font-bold text-xs text-slate-800">{{ item.qty }}</span>
            <button
              @click="updateQty(item, 1)"
              :disabled="item.qty >= item.barang.stok"
              class="w-6 h-6 rounded-md bg-white hover:bg-slate-200 disabled:opacity-40 text-slate-700 flex items-center justify-center text-xs shadow-2xs transition cursor-pointer"
            >
              <Plus class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>

      <!-- Financial Calculations & Checkout -->
      <div class="p-4 border-t border-slate-100 bg-slate-50 space-y-3">
        <div class="space-y-1.5 text-xs text-slate-600">
          <div class="flex justify-between">
            <span>Subtotal:</span>
            <span class="font-medium text-slate-800">{{ formatRupiah(subtotal) }}</span>
          </div>

          <div class="flex justify-between items-center">
            <span>Potongan / Diskon:</span>
            <div class="flex items-center gap-1">
              <span class="text-slate-400">Rp</span>
              <input
                v-model.number="diskon"
                type="number"
                min="0"
                class="w-20 px-2 py-1 text-right bg-white border border-slate-200 rounded text-xs font-medium focus:outline-none focus:border-indigo-500"
              />
            </div>
          </div>

          <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
            <span class="text-sm font-bold text-slate-900">Total Tagihan:</span>
            <span class="text-lg font-black text-indigo-600">{{ formatRupiah(grandTotal) }}</span>
          </div>
        </div>

        <button
          @click="openCheckout"
          :disabled="cart.length === 0"
          class="w-full py-3.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 disabled:opacity-50 text-white font-bold text-sm transition duration-150 flex items-center justify-center gap-2 shadow-md shadow-indigo-600/20 cursor-pointer"
        >
          <Banknote class="w-5 h-5" />
          <span>Bayar Transaksi ({{ formatRupiah(grandTotal) }})</span>
        </button>
      </div>
    </div>

    <!-- MODAL PEMBAYARAN -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-6 border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-bold text-slate-900">Proses Pembayaran</h3>
            <p class="text-xs text-slate-500">Pilih metode bayar & masukkan jumlah uang</p>
          </div>
          <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Grand Total Banner -->
        <div class="p-4 rounded-xl bg-indigo-50/80 border border-indigo-100 flex items-center justify-between">
          <span class="text-sm font-semibold text-indigo-900">Total Tagihan:</span>
          <span class="text-2xl font-black text-indigo-700">{{ formatRupiah(grandTotal) }}</span>
        </div>

        <!-- Payment Method Tabs -->
        <div class="space-y-2">
          <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Metode Bayar</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              type="button"
              @click="paymentMethod = 'tunai'"
              class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex flex-col items-center gap-1.5 transition cursor-pointer"
              :class="paymentMethod === 'tunai' ? 'border-indigo-600 bg-indigo-50/40 text-indigo-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
            >
              <Banknote class="w-5 h-5" />
              <span>Tunai</span>
            </button>
            <button
              type="button"
              @click="paymentMethod = 'qris'"
              class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex flex-col items-center gap-1.5 transition cursor-pointer"
              :class="paymentMethod === 'qris' ? 'border-indigo-600 bg-indigo-50/40 text-indigo-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
            >
              <QrCode class="w-5 h-5" />
              <span>QRIS</span>
            </button>
            <button
              type="button"
              @click="paymentMethod = 'transfer'"
              class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex flex-col items-center gap-1.5 transition cursor-pointer"
              :class="paymentMethod === 'transfer' ? 'border-indigo-600 bg-indigo-50/40 text-indigo-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'"
            >
              <CreditCard class="w-5 h-5" />
              <span>Transfer</span>
            </button>
          </div>
        </div>

        <!-- Uang Diterima & Quick Cash -->
        <div v-if="paymentMethod === 'tunai'" class="space-y-3">
          <div>
            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">
              Nominal Uang Diterima
            </label>
            <div class="relative">
              <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400 text-sm">Rp</span>
              <input
                v-model.number="uangDiterima"
                type="number"
                min="0"
                class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
              />
            </div>
          </div>

          <!-- Quick Cash Buttons -->
          <div class="flex flex-wrap gap-2">
            <button
              type="button"
              @click="setUangPas"
              class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer"
            >
              Uang Pas
            </button>
            <button
              type="button"
              @click="addUang(10000)"
              class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer"
            >
              +10.000
            </button>
            <button
              type="button"
              @click="addUang(20000)"
              class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer"
            >
              +20.000
            </button>
            <button
              type="button"
              @click="addUang(50000)"
              class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer"
            >
              +50.000
            </button>
            <button
              type="button"
              @click="addUang(100000)"
              class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold cursor-pointer"
            >
              +100.000
            </button>
          </div>

          <!-- Kembalian Banner -->
          <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Kembalian:</span>
            <span class="text-xl font-black text-emerald-600">{{ formatRupiah(kembalian) }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 pt-2">
          <button
            type="button"
            @click="showPaymentModal = false"
            class="w-1/3 py-3 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold text-sm cursor-pointer"
          >
            Batal
          </button>
          <button
            type="button"
            @click="submitTransaction"
            :disabled="isSubmitting || (paymentMethod === 'tunai' && uangDiterima < grandTotal)"
            class="flex-1 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold text-sm shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2 cursor-pointer transition"
          >
            <CheckCircle2 class="w-5 h-5" />
            <span>{{ isSubmitting ? 'Menyimpan...' : 'Selesaikan Transaksi' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL STRUK (RECEIPT MODAL) -->
    <div
      v-if="showReceiptModal && lastTransaction"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4 border border-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2 text-emerald-600 font-bold text-sm">
            <CheckCircle2 class="w-5 h-5" />
            <span>Transaksi Sukses</span>
          </div>
          <button @click="showReceiptModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <!-- Printable Receipt Thermal Area -->
        <div id="receipt-print-area" class="p-4 bg-slate-50 rounded-xl border border-slate-200 font-mono text-xs text-slate-800 space-y-2">
          <div class="text-center pb-2 border-b border-dashed border-slate-300">
            <div class="font-bold text-sm">KASIRKEL POS</div>
            <div class="text-[10px] text-slate-500">Koperasi SMKN 2 Tasikmalaya</div>
            <div class="text-[10px] text-slate-400 mt-1">{{ lastTransaction.tanggal_penjualan || new Date().toLocaleString('id-ID') }}</div>
          </div>

          <div class="text-[11px] py-1 border-b border-dashed border-slate-300 space-y-1">
            <div
              v-for="item in lastTransaction.cartSnapshot"
              :key="item.barang.id_barang"
              class="flex justify-between"
            >
              <span>{{ item.barang.nama }} × {{ item.qty }}</span>
              <span>{{ formatRupiah(item.barang.harga_jual * item.qty) }}</span>
            </div>
          </div>

          <div class="text-[11px] pt-1 space-y-1">
            <div class="flex justify-between font-bold">
              <span>Total:</span>
              <span>{{ formatRupiah(lastTransaction.grandTotal) }}</span>
            </div>
            <div class="flex justify-between">
              <span>Bayar ({{ lastTransaction.cara_bayar }}):</span>
              <span>{{ formatRupiah(lastTransaction.totalBayar) }}</span>
            </div>
            <div class="flex justify-between font-bold text-emerald-600">
              <span>Kembalian:</span>
              <span>{{ formatRupiah(lastTransaction.kembalian) }}</span>
            </div>
          </div>

          <div class="text-center pt-2 border-t border-dashed border-slate-300 text-[10px] text-slate-400">
            Terima kasih atas kunjungan Anda!
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex items-center gap-2 pt-2">
          <button
            @click="printReceipt"
            class="flex-1 py-2.5 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer"
          >
            <Printer class="w-4 h-4" />
            <span>Cetak Struk</span>
          </button>
          <button
            @click="showReceiptModal = false"
            class="flex-1 py-2.5 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer shadow-xs"
          >
            <Plus class="w-4 h-4" />
            <span>Transaksi Baru</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

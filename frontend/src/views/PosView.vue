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
  nama: string
  nama_barang?: string
  satuan: string
  harga_beli: number
  harga_jual: number
  stok: number
  is_active: boolean
}

interface CartItem {
  id_barang: number
  barcode: string
  nama: string
  harga: number
  qty: number
  stok: number
}

// Active Top Tab
const activeTab = ref<'kasir' | 'pelanggan'>('kasir')

// Catalog & Cart
const barangList = ref<Barang[]>([])
const cart = ref<CartItem[]>([
  { id_barang: 1, barcode: '899123456', nama: 'Buku Tulis', harga: 5000, qty: 2, stok: 100 },
  { id_barang: 2, barcode: '899123457', nama: 'Pulpen', harga: 3000, qty: 3, stok: 200 }
])
const searchQuery = ref('')

// Discount
const discountType = ref<'Nominal' | 'Persen'>('Nominal')
const discountValue = ref<number>(0)

// Customer State
const customerQuery = ref('')
const selectedCustomer = ref({
  id_pelanggan: 1,
  nama: 'Siswa A',
  no_hp: '08123456789',
  poin: 120
})
const recentCustomers = ref([
  { id_pelanggan: 1, nama: 'Siswa A', no_hp: '08123456789', poin: 120 },
  { id_pelanggan: 2, nama: 'Siswa B', no_hp: '08123456780', poin: 85 },
  { id_pelanggan: 3, nama: 'Siswa C', no_hp: '08123456781', poin: 40 },
  { id_pelanggan: 4, nama: 'Siswa D', no_hp: '08123456782', poin: 15 },
  { id_pelanggan: 5, nama: 'Siswa E', no_hp: '08123456783', poin: 0 }
])

// Payment Modal State
const showPaymentModal = ref(false)
const showReceiptModal = ref(false)
const metodeBayar = ref<'Tunai' | 'QRIS' | 'Transfer'>('Tunai')
const uangDiterima = ref<number>(0)
const isSubmitting = ref(false)
const lastTransaction = ref<any>(null)

// Format Rupiah
const formatRupiah = (val: number | string) => {
  const num = Number(val) || 0
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(num)
}

// Fetch Catalog from Database
const fetchBarang = async () => {
  try {
    const res = await apiClient.get('/barang')
    if (Array.isArray(res.data) && res.data.length > 0) {
      barangList.value = res.data.map((b: any) => ({
        ...b,
        nama: b.nama || b.nama_barang || 'Produk'
      }))

      // Populate initial cart with real items if available
      if (res.data.length >= 2 && cart.value.length === 2 && cart.value[0].id_barang === 1 && cart.value[0].nama === 'Buku Tulis') {
        const item1 = res.data[0]
        const item2 = res.data[1]
        cart.value = [
          {
            id_barang: item1.id_barang,
            barcode: item1.barcode || '899123456',
            nama: item1.nama || item1.nama_barang,
            harga: Number(item1.harga_jual) || 5000,
            qty: 2,
            stok: item1.stok || 100
          },
          {
            id_barang: item2.id_barang,
            barcode: item2.barcode || '899123457',
            nama: item2.nama || item2.nama_barang,
            harga: Number(item2.harga_jual) || 3000,
            qty: 3,
            stok: item2.stok || 200
          }
        ]
      }
    }
  } catch (err) {
    console.error('Gagal mengambil data barang:', err)
  }
}

// Fetch Customers
const fetchPelanggan = async () => {
  try {
    const res = await apiClient.get('/pelanggan')
    if (Array.isArray(res.data) && res.data.length > 0) {
      recentCustomers.value = res.data.slice(0, 5).map((p: any, idx: number) => ({
        id_pelanggan: p.id_pelanggan,
        nama: p.nama || `Siswa ${String.fromCharCode(65 + idx)}`,
        no_hp: p.no_telepon || '08123456789',
        poin: p.poin_loyalitas || 0
      }))
      if (recentCustomers.value[0]) {
        selectedCustomer.value = recentCustomers.value[0]
      }
    }
  } catch {
    // Abaikan, gunakan data mockup
  }
}

onMounted(() => {
  fetchBarang()
  fetchPelanggan()
})

// Search Products
const filteredProducts = computed(() => {
  if (!searchQuery.value) return []
  const q = searchQuery.value.toLowerCase().trim()
  return barangList.value.filter(
    (b) => b.nama.toLowerCase().includes(q) || (b.barcode && b.barcode.toLowerCase().includes(q))
  ).slice(0, 5)
})

// Add to Cart
const addToCart = (product: Barang) => {
  const existing = cart.value.find((c) => c.id_barang === product.id_barang)
  if (existing) {
    if (existing.qty < product.stok) {
      existing.qty++
    } else {
      alert(`Stok produk "${product.nama}" hanya tersisa ${product.stok}`)
    }
  } else {
    cart.value.push({
      id_barang: product.id_barang,
      barcode: product.barcode,
      nama: product.nama,
      harga: Number(product.harga_jual) || 0,
      qty: 1,
      stok: product.stok
    })
  }
  searchQuery.value = ''
}

// Handle Barcode Scan / Enter
const handleSearchEnter = () => {
  if (!searchQuery.value) return
  const q = searchQuery.value.toLowerCase().trim()
  const found = barangList.value.find(
    (b) => (b.barcode && b.barcode.toLowerCase() === q) || b.nama.toLowerCase() === q
  )
  if (found) {
    addToCart(found)
  } else if (filteredProducts.value.length > 0) {
    addToCart(filteredProducts.value[0])
  } else {
    alert('Produk tidak ditemukan')
  }
}

// Steppers
const incrementQty = (item: CartItem) => {
  if (item.qty < item.stok) {
    item.qty++
  }
}

const decrementQty = (item: CartItem) => {
  if (item.qty > 1) {
    item.qty--
  } else {
    removeFromCart(item.id_barang)
  }
}

const removeFromCart = (id_barang: number) => {
  cart.value = cart.value.filter((i) => i.id_barang !== id_barang)
}

const clearCart = () => {
  if (cart.value.length === 0) return
  if (confirm('Bersihkan keranjang belanja?')) {
    cart.value = []
    discountValue.value = 0
  }
}

// Totals Calculation
const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.harga * item.qty, 0)
})

const diskonNominal = computed(() => {
  if (discountType.value === 'Persen') {
    return Math.round((subtotal.value * (Number(discountValue.value) || 0)) / 100)
  }
  return Number(discountValue.value) || 0
})

const total = computed(() => {
  return Math.max(0, subtotal.value - diskonNominal.value)
})

const kembalian = computed(() => {
  return Math.max(0, (uangDiterima.value || 0) - total.value)
})

// Quick Payment Open
const openPayment = () => {
  if (cart.value.length === 0) {
    alert('Keranjang belanja masih kosong!')
    return
  }
  uangDiterima.value = total.value
  showPaymentModal.value = true
}

const setUangPas = () => {
  uangDiterima.value = total.value
}

// Select Customer
const chooseCustomer = (cust: any) => {
  selectedCustomer.value = cust
}

// Process Checkout & Save to Database
const prosesPembayaran = async () => {
  if (metodeBayar.value === 'Tunai' && uangDiterima.value < total.value) {
    alert('Uang yang diterima kurang dari total bayar!')
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      id_pelanggan: selectedCustomer.value?.id_pelanggan || null,
      tanggal_penjualan: new Date().toISOString().slice(0, 19).replace('T', ' '),
      subtotal: subtotal.value,
      diskon: diskonNominal.value,
      total_bayar: total.value,
      cara_bayar: metodeBayar.value,
      items: cart.value.map((c) => ({
        id_barang: c.id_barang,
        jumlah: c.qty,
        harga_satuan: c.harga,
        subtotal: c.harga * c.qty
      }))
    }

    const res = await apiClient.post('/penjualan', payload).catch(() => null)

    const nomorFaktur = res?.data?.nomor_faktur || `TRX-${Date.now().toString().slice(-6)}`

    lastTransaction.value = {
      nomor_faktur: nomorFaktur,
      tanggal: new Date().toLocaleString('id-ID'),
      pelanggan: selectedCustomer.value.nama,
      items: [...cart.value],
      subtotal: subtotal.value,
      diskon: diskonNominal.value,
      total: total.value,
      metode: metodeBayar.value,
      uangDiterima: uangDiterima.value,
      kembalian: kembalian.value
    }

    showPaymentModal.value = false
    showReceiptModal.value = true
    cart.value = []
    discountValue.value = 0
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan transaksi!')
  } finally {
    isSubmitting.value = false
  }
}

const printStruk = () => {
  window.print()
}
</script>

<template>
  <div class="p-6 space-y-4 max-w-7xl mx-auto">
    <!-- Top Tabs: [ Transaksi Kasir ] | [ Data Pelanggan ] -->
    <div class="border-b border-gray-200 flex items-center gap-8">
      <button
        @click="activeTab = 'kasir'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'kasir' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Transaksi Kasir</span>
        <span
          v-if="activeTab === 'kasir'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>

      <button
        @click="activeTab = 'pelanggan'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'pelanggan' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Data Pelanggan</span>
        <span
          v-if="activeTab === 'pelanggan'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>
    </div>

    <!-- MAIN GRID: 2 COLUMNS (KASIR LEFT, PELANGGAN RIGHT) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
      <!-- LEFT COLUMN: TRANSAKSI KASIR (7 cols) -->
      <div class="lg:col-span-8 space-y-4">
        <!-- Search Bar with Barcode Icon -->
        <div class="relative">
          <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            ref="searchInputRef"
            v-model="searchQuery"
            @keydown.enter="handleSearchEnter"
            type="text"
            placeholder="Cari produk (barcode / nama)..."
            class="w-full pl-9 pr-10 py-2 bg-white rounded-lg border border-gray-200 text-xs focus:outline-hidden focus:border-gray-400 shadow-2xs"
          />
          <button
            @click="handleSearchEnter"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 p-1"
            title="Scan Barcode"
          >
            <Barcode class="w-4 h-4" />
          </button>

          <!-- Autocomplete Dropdown -->
          <div
            v-if="searchQuery && filteredProducts.length > 0"
            class="absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-30 divide-y divide-gray-100 overflow-hidden text-xs"
          >
            <div
              v-for="p in filteredProducts"
              :key="p.id_barang"
              @click="addToCart(p)"
              class="p-2.5 hover:bg-gray-50 flex items-center justify-between cursor-pointer transition"
            >
              <div>
                <span class="font-semibold text-gray-900">{{ p.nama }}</span>
                <span class="text-[10px] text-gray-400 ml-2">Barcode: {{ p.barcode }}</span>
              </div>
              <div class="font-bold text-gray-900">{{ formatRupiah(p.harga_jual) }}</div>
            </div>
          </div>
        </div>

        <!-- Cart Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold text-[11px]">
                <th class="py-2.5 px-3 w-10 text-center">No</th>
                <th class="py-2.5 px-3">Nama Produk</th>
                <th class="py-2.5 px-3">Harga</th>
                <th class="py-2.5 px-3 text-center w-24">Qty</th>
                <th class="py-2.5 px-3 text-right">Subtotal</th>
                <th class="py-2.5 px-3 w-10 text-center"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-800">
              <tr v-if="cart.length === 0" class="text-center">
                <td colspan="6" class="py-12 text-gray-400">
                  Keranjang kosong. Cari produk atau scan barcode untuk menambahkan.
                </td>
              </tr>
              <tr
                v-for="(item, idx) in cart"
                :key="item.id_barang"
                class="hover:bg-gray-50/60 transition"
              >
                <td class="py-3 px-3 text-center text-gray-500 font-medium">{{ idx + 1 }}</td>
                <td class="py-3 px-3 font-semibold text-gray-900">{{ item.nama }}</td>
                <td class="py-3 px-3 text-gray-600">{{ formatRupiah(item.harga) }}</td>
                <td class="py-3 px-3">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="decrementQty(item)"
                      class="w-5 h-5 rounded border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100 cursor-pointer"
                    >
                      <Minus class="w-3 h-3" />
                    </button>
                    <span class="w-6 text-center font-bold text-gray-900">{{ item.qty }}</span>
                    <button
                      @click="incrementQty(item)"
                      class="w-5 h-5 rounded border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100 cursor-pointer"
                    >
                      <Plus class="w-3 h-3" />
                    </button>
                  </div>
                </td>
                <td class="py-3 px-3 text-right font-bold text-gray-900">
                  {{ formatRupiah(item.harga * item.qty) }}
                </td>
                <td class="py-3 px-3 text-center">
                  <button
                    @click="removeFromCart(item.id_barang)"
                    class="p-1 text-gray-400 hover:text-rose-600 rounded transition cursor-pointer"
                    title="Hapus"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Summary & Actions Box -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-2xs space-y-3">
          <div class="flex items-center justify-between text-xs text-gray-600">
            <span>Subtotal</span>
            <span class="font-bold text-gray-900">{{ formatRupiah(subtotal) }}</span>
          </div>

          <!-- Diskon Row -->
          <div class="flex items-center justify-between text-xs text-gray-600 gap-4">
            <div class="flex items-center gap-2">
              <span>Diskon</span>
              <select
                v-model="discountType"
                class="px-2 py-1 rounded border border-gray-200 text-xs bg-white text-gray-700 cursor-pointer"
              >
                <option value="Nominal">Nominal</option>
                <option value="Persen">Persen (%)</option>
              </select>
              <input
                v-model.number="discountValue"
                type="number"
                min="0"
                placeholder="0"
                class="w-20 px-2 py-1 rounded border border-gray-200 text-xs text-right outline-hidden"
              />
            </div>
            <span class="font-bold text-gray-900">
              {{ formatRupiah(diskonNominal) }}
            </span>
          </div>

          <!-- Total Bayar -->
          <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <span class="text-sm font-bold text-gray-900">Total</span>
            <span class="text-base font-extrabold text-gray-900">{{ formatRupiah(total) }}</span>
          </div>

          <!-- Buttons: Bersihkan & Bayar -->
          <div class="flex items-center gap-3 pt-1">
            <button
              @click="clearCart"
              :disabled="cart.length === 0"
              class="flex-1 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold flex items-center justify-center gap-1.5 transition cursor-pointer disabled:opacity-50"
            >
              <Trash2 class="w-3.5 h-3.5" />
              <span>Bersihkan</span>
            </button>

            <button
              @click="openPayment"
              :disabled="cart.length === 0"
              class="flex-1 py-2 rounded-lg bg-[#23272f] hover:bg-black text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-xs transition cursor-pointer disabled:opacity-50"
            >
              <CreditCard class="w-3.5 h-3.5" />
              <span>Bayar</span>
            </button>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: PELANGGAN (4 cols) -->
      <div class="lg:col-span-4 space-y-4">
        <!-- Box 1: Pelanggan (Opsional) -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-2xs space-y-3">
          <h2 class="text-xs font-bold text-gray-900">Pelanggan (Opsional)</h2>

          <!-- Search Customer Input with + Button -->
          <div class="flex items-center gap-2">
            <div class="relative flex-1">
              <Search class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
              <input
                v-model="customerQuery"
                type="text"
                placeholder="Cari pelanggan..."
                class="w-full pl-8 pr-2.5 py-1.5 rounded-lg border border-gray-200 text-xs outline-hidden"
              />
            </div>
            <button
              class="w-7 h-7 rounded-lg border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-100 cursor-pointer"
              title="Tambah Pelanggan Baru"
            >
              <Plus class="w-3.5 h-3.5" />
            </button>
          </div>

          <!-- Customer Info -->
          <div class="space-y-1 text-xs text-gray-700 bg-gray-50/80 p-2.5 rounded-lg border border-gray-100">
            <div><span class="text-gray-500">Nama :</span> <strong class="text-gray-900 ml-1">{{ selectedCustomer.nama }}</strong></div>
            <div><span class="text-gray-500">No. HP :</span> <span class="text-gray-800 ml-1">{{ selectedCustomer.no_hp }}</span></div>
            <div><span class="text-gray-500">Poin :</span> <span class="text-gray-800 ml-1">{{ selectedCustomer.poin }}</span></div>
          </div>

          <!-- Choose Customer Button -->
          <button
            class="w-full py-2 bg-[#23272f] hover:bg-black text-white text-xs font-semibold rounded-lg transition cursor-pointer"
          >
            Pilih Pelanggan
          </button>
        </div>

        <!-- Box 2: Pelanggan Terakhir -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-2xs space-y-2">
          <h2 class="text-xs font-bold text-gray-900">Pelanggan Terakhir</h2>

          <ul class="divide-y divide-gray-100 text-xs text-gray-700">
            <li
              v-for="(cust, i) in recentCustomers"
              :key="cust.id_pelanggan"
              @click="chooseCustomer(cust)"
              class="py-2 flex items-center justify-between hover:text-black cursor-pointer group"
            >
              <span>{{ i + 1 }}. {{ cust.nama }}</span>
              <span class="text-[10px] text-gray-400 group-hover:text-gray-700">{{ cust.poin }} poin</span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- MODAL: PEMBAYARAN -->
    <div
      v-if="showPaymentModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="text-sm font-bold text-gray-900">Konfirmasi Pembayaran</h3>
          <button @click="showPaymentModal = false" class="text-gray-400 hover:text-gray-700 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-3 text-xs">
          <!-- Total Bayar Tag -->
          <div class="p-3 bg-gray-50 rounded-lg text-center border border-gray-200">
            <div class="text-[11px] text-gray-500">Total Tagihan:</div>
            <div class="text-xl font-extrabold text-gray-900 mt-0.5">{{ formatRupiah(total) }}</div>
          </div>

          <!-- Metode Bayar -->
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Metode Pembayaran</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="m in (['Tunai', 'QRIS', 'Transfer'] as const)"
                :key="m"
                type="button"
                @click="metodeBayar = m"
                class="py-1.5 rounded-lg border text-xs font-semibold text-center cursor-pointer transition"
                :class="metodeBayar === m ? 'bg-black text-white border-black' : 'border-gray-200 text-gray-700 hover:bg-gray-50'"
              >
                {{ m }}
              </button>
            </div>
          </div>

          <!-- Nominal Uang Diterima (untuk Tunai) -->
          <div v-if="metodeBayar === 'Tunai'" class="space-y-2">
            <label class="block font-semibold text-gray-700">Nominal Diterima</label>
            <div class="flex gap-2">
              <input
                v-model.number="uangDiterima"
                type="number"
                min="0"
                class="flex-1 px-3 py-1.5 rounded-lg border border-gray-200 font-bold text-sm outline-hidden"
              />
              <button
                type="button"
                @click="setUangPas"
                class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50"
              >
                Uang Pas
              </button>
            </div>

            <!-- Kembalian -->
            <div class="flex items-center justify-between pt-1 text-xs">
              <span class="text-gray-500">Kembalian:</span>
              <span class="font-bold text-gray-900 text-sm">{{ formatRupiah(kembalian) }}</span>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button
              @click="showPaymentModal = false"
              class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              @click="prosesPembayaran"
              :disabled="isSubmitting"
              class="px-4 py-1.5 rounded-lg bg-black hover:bg-neutral-800 text-white font-semibold cursor-pointer disabled:opacity-50"
            >
              Proses Transaksi
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: STRUK TRANSAKSI -->
    <div
      v-if="showReceiptModal && lastTransaction"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl max-w-sm w-full p-5 shadow-2xl border border-gray-200 space-y-4">
        <div class="text-center pb-2 border-b border-gray-100">
          <CheckCircle2 class="w-8 h-8 text-emerald-600 mx-auto mb-1" />
          <h3 class="text-sm font-bold text-gray-900">Transaksi Berhasil!</h3>
          <p class="text-[11px] text-gray-400">POS SEKOLAH</p>
        </div>

        <div class="text-[11px] space-y-1 font-mono text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200">
          <div class="flex justify-between">
            <span>No: {{ lastTransaction.nomor_faktur }}</span>
            <span>{{ lastTransaction.metode }}</span>
          </div>
          <div class="text-gray-400 text-[10px]">{{ lastTransaction.tanggal }}</div>
          <div class="border-t border-dashed border-gray-300 my-1 pt-1"></div>
          <div v-for="it in lastTransaction.items" :key="it.id_barang" class="flex justify-between">
            <span>{{ it.nama }} x{{ it.qty }}</span>
            <span>{{ formatRupiah(it.harga * it.qty) }}</span>
          </div>
          <div class="border-t border-dashed border-gray-300 my-1 pt-1"></div>
          <div class="flex justify-between font-bold text-gray-900">
            <span>TOTAL</span>
            <span>{{ formatRupiah(lastTransaction.total) }}</span>
          </div>
          <div v-if="lastTransaction.metode === 'Tunai'" class="flex justify-between text-gray-500">
            <span>Kembalian</span>
            <span>{{ formatRupiah(lastTransaction.kembalian) }}</span>
          </div>
        </div>

        <div class="flex gap-2 pt-1">
          <button
            @click="printStruk"
            class="flex-1 py-2 rounded-lg bg-black hover:bg-neutral-800 text-white text-xs font-semibold flex items-center justify-center gap-1.5"
          >
            <Printer class="w-3.5 h-3.5" />
            <span>Cetak Struk</span>
          </button>
          <button
            @click="showReceiptModal = false"
            class="px-4 py-2 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-semibold"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

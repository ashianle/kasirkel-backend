<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  Plus,
  Search,
  Calendar,
  Filter,
  Eye,
  Trash2,
  Edit2,
  ChevronLeft,
  ChevronRight,
  Truck,
  ShoppingBag,
  X,
  AlertCircle,
  Loader2,
  CheckCircle2,
  Clock
} from 'lucide-vue-next'
import api from '@/api/client'

// Active tab
const activeTab = ref<'pembelian' | 'supplier'>('pembelian')

// Data states
const loading = ref(false)
const pembelianList = ref<any[]>([])
const supplierList = ref<any[]>([])
const barangList = ref<any[]>([])

// Filters
const searchPembelian = ref('')
const statusFilter = ref('')
const startDate = ref('')
const endDate = ref('')
const searchSupplier = ref('')

// Pagination
const currentPagePembelian = ref(1)
const itemsPerPagePembelian = ref(8)
const currentPageSupplier = ref(1)
const itemsPerPageSupplier = ref(8)

// Modals
const showModalPembelian = ref(false)
const showModalSupplier = ref(false)
const showDetailModal = ref(false)
const selectedDetail = ref<any>(null)
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

// New Pembelian Form
const formPembelian = ref({
  id_supplier: '',
  nomor_faktur: '',
  tanggal_faktur: new Date().toISOString().split('T')[0],
  status_pembelian: 'selesai',
  jenis_transaksi: 'Tunai',
  cara_bayar: 'Tunai',
  note: '',
  items: [
    { id_barang: '', satuan: 'Pcs', jumlah: 1, harga_beli: 0 }
  ]
})

// New / Edit Supplier Form
const isEditingSupplier = ref(false)
const formSupplier = ref({
  id_supplier: null as number | null,
  nama: '',
  no_telepon: '',
  alamat_supplier: ''
})

// Format Currency
const formatRupiah = (val: number | string) => {
  const num = Number(val) || 0
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(num)
}

// Fetch all data
const fetchData = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const [pembelianRes, supplierRes, barangRes] = await Promise.all([
      api.get('/pembelian').catch(() => ({ data: [] })),
      api.get('/supplier').catch(() => ({ data: [] })),
      api.get('/barang').catch(() => ({ data: [] }))
    ])
    pembelianList.value = pembelianRes.data || []
    supplierList.value = supplierRes.data || []
    barangList.value = barangRes.data || []
  } catch (err: any) {
    console.error('Error fetching data:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Pembelian Computed Filtering & Pagination
const filteredPembelian = computed(() => {
  return pembelianList.value.filter((item) => {
    const matchSearch =
      !searchPembelian.value ||
      item.nomor_faktur?.toLowerCase().includes(searchPembelian.value.toLowerCase()) ||
      item.supplier?.nama?.toLowerCase().includes(searchPembelian.value.toLowerCase())

    const matchStatus =
      !statusFilter.value ||
      item.status_pembelian?.toLowerCase() === statusFilter.value.toLowerCase()

    const itemDate = item.tanggal_faktur ? item.tanggal_faktur.split('T')[0] : ''
    const matchStart = !startDate.value || itemDate >= startDate.value
    const matchEnd = !endDate.value || itemDate <= endDate.value

    return matchSearch && matchStatus && matchStart && matchEnd
  })
})

const totalPagesPembelian = computed(() => {
  return Math.ceil(filteredPembelian.value.length / itemsPerPagePembelian.value) || 1
})

const paginatedPembelian = computed(() => {
  const start = (currentPagePembelian.value - 1) * itemsPerPagePembelian.value
  return filteredPembelian.value.slice(start, start + itemsPerPagePembelian.value)
})

// Supplier Computed Filtering & Pagination
const filteredSupplier = computed(() => {
  return supplierList.value.filter((item) => {
    return (
      !searchSupplier.value ||
      item.nama?.toLowerCase().includes(searchSupplier.value.toLowerCase()) ||
      item.no_telepon?.toLowerCase().includes(searchSupplier.value.toLowerCase()) ||
      item.alamat_supplier?.toLowerCase().includes(searchSupplier.value.toLowerCase())
    )
  })
})

const totalPagesSupplier = computed(() => {
  return Math.ceil(filteredSupplier.value.length / itemsPerPageSupplier.value) || 1
})

const paginatedSupplier = computed(() => {
  const start = (currentPageSupplier.value - 1) * itemsPerPageSupplier.value
  return filteredSupplier.value.slice(start, start + itemsPerPageSupplier.value)
})

// Open Add Pembelian
const openAddPembelian = () => {
  const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, '')
  const randomNum = Math.floor(100 + Math.random() * 900)
  formPembelian.value = {
    id_supplier: supplierList.value[0]?.id_supplier || '',
    nomor_faktur: `PO-${dateStr}-${randomNum}`,
    tanggal_faktur: new Date().toISOString().split('T')[0],
    status_pembelian: 'selesai',
    jenis_transaksi: 'Tunai',
    cara_bayar: 'Tunai',
    note: '',
    items: [
      { id_barang: barangList.value[0]?.id_barang || '', satuan: 'Pcs', jumlah: 1, harga_beli: barangList.value[0]?.harga_beli || 0 }
    ]
  }
  errorMessage.value = ''
  showModalPembelian.value = true
}

// Add / Remove item in Pembelian form
const addItemRow = () => {
  const defaultBarang = barangList.value[0]
  formPembelian.value.items.push({
    id_barang: defaultBarang?.id_barang || '',
    satuan: 'Pcs',
    jumlah: 1,
    harga_beli: defaultBarang?.harga_beli || 0
  })
}

const removeItemRow = (index: number) => {
  if (formPembelian.value.items.length > 1) {
    formPembelian.value.items.splice(index, 1)
  }
}

const onBarangChange = (index: number) => {
  const item = formPembelian.value.items[index]
  const found = barangList.value.find((b) => b.id_barang === Number(item.id_barang))
  if (found) {
    item.harga_beli = Number(found.harga_beli) || 0
  }
}

const formTotalBayar = computed(() => {
  return formPembelian.value.items.reduce((sum, item) => {
    return sum + (Number(item.harga_beli) || 0) * (Number(item.jumlah) || 0)
  }, 0)
})

// Save Pembelian
const handleSavePembelian = async () => {
  if (!formPembelian.value.id_supplier) {
    errorMessage.value = 'Silakan pilih supplier'
    return
  }
  if (formPembelian.value.items.some((it) => !it.id_barang || it.jumlah < 1)) {
    errorMessage.value = 'Pastikan semua produk dan kuantitas valid'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''
  try {
    const payload = {
      id_supplier: Number(formPembelian.value.id_supplier),
      nomor_faktur: formPembelian.value.nomor_faktur,
      tanggal_faktur: formPembelian.value.tanggal_faktur,
      status_pembelian: formPembelian.value.status_pembelian,
      jenis_transaksi: formPembelian.value.jenis_transaksi,
      cara_bayar: formPembelian.value.cara_bayar,
      note: formPembelian.value.note,
      detail: formPembelian.value.items.map((it) => ({
        id_barang: Number(it.id_barang),
        satuan: it.satuan || 'Pcs',
        jumlah: Number(it.jumlah)
      }))
    }

    await api.post('/pembelian', payload)
    showModalPembelian.value = false
    successMessage.value = 'Data transaksi pembelian berhasil ditambahkan!'
    setTimeout(() => { successMessage.value = '' }, 3000)
    await fetchData()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.error || err.response?.data?.message || 'Gagal menyimpan transaksi pembelian'
  } finally {
    isSubmitting.value = false
  }
}

// Delete Pembelian
const handleDeletePembelian = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus data pembelian ini? Stok barang akan dikembalikan.')) return
  try {
    await api.delete(`/pembelian/${id}`)
    successMessage.value = 'Data pembelian berhasil dihapus'
    setTimeout(() => { successMessage.value = '' }, 3000)
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.error || 'Gagal menghapus pembelian')
  }
}

// Show Detail Pembelian
const viewDetailPembelian = (pembelian: any) => {
  selectedDetail.value = pembelian
  showDetailModal.value = true
}

// Open Add / Edit Supplier
const openAddSupplier = () => {
  isEditingSupplier.value = false
  formSupplier.value = {
    id_supplier: null,
    nama: '',
    no_telepon: '',
    alamat_supplier: ''
  }
  showModalSupplier.value = true
}

const openEditSupplier = (supplier: any) => {
  isEditingSupplier.value = true
  formSupplier.value = {
    id_supplier: supplier.id_supplier,
    nama: supplier.nama,
    no_telepon: supplier.no_telepon || '',
    alamat_supplier: supplier.alamat_supplier || ''
  }
  showModalSupplier.value = true
}

// Save Supplier
const handleSaveSupplier = async () => {
  if (!formSupplier.value.nama) {
    errorMessage.value = 'Nama supplier wajib diisi'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''
  try {
    if (isEditingSupplier.value && formSupplier.value.id_supplier) {
      await api.put(`/supplier/${formSupplier.value.id_supplier}`, {
        nama: formSupplier.value.nama,
        no_telepon: formSupplier.value.no_telepon,
        alamat_supplier: formSupplier.value.alamat_supplier
      })
      successMessage.value = 'Supplier berhasil diperbarui!'
    } else {
      await api.post('/supplier', {
        nama: formSupplier.value.nama,
        no_telepon: formSupplier.value.no_telepon,
        alamat_supplier: formSupplier.value.alamat_supplier
      })
      successMessage.value = 'Supplier berhasil ditambahkan!'
    }
    showModalSupplier.value = false
    setTimeout(() => { successMessage.value = '' }, 3000)
    await fetchData()
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan data supplier'
  } finally {
    isSubmitting.value = false
  }
}

// Delete Supplier
const handleDeleteSupplier = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus data supplier ini?')) return
  try {
    await api.delete(`/supplier/${id}`)
    successMessage.value = 'Supplier berhasil dihapus'
    setTimeout(() => { successMessage.value = '' }, 3000)
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus supplier')
  }
}
</script>

<template>
  <div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Success Banner -->
    <div
      v-if="successMessage"
      class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2 animate-in fade-in duration-200"
    >
      <CheckCircle2 class="w-5 h-5 text-emerald-600 shrink-0" />
      <span>{{ successMessage }}</span>
    </div>

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Pembelian & Supplier</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola data transaksi faktur pembelian barang dan mitra pemasok</p>
      </div>

      <div class="flex items-center gap-3">
        <button
          v-if="activeTab === 'pembelian'"
          @click="openAddPembelian"
          class="flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs hover:shadow transition cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Pembelian</span>
        </button>

        <button
          v-if="activeTab === 'supplier'"
          @click="openAddSupplier"
          class="flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs hover:shadow transition cursor-pointer"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Supplier</span>
        </button>
      </div>
    </div>

    <!-- Main Container Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
      <!-- Tabs Header -->
      <div class="px-6 pt-4 border-b border-slate-200 flex items-center gap-8">
        <button
          @click="activeTab = 'pembelian'"
          class="pb-3 text-xs font-bold transition-all relative flex items-center gap-2 cursor-pointer"
          :class="activeTab === 'pembelian' ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800'"
        >
          <ShoppingBag class="w-4 h-4" />
          <span>Data Pembelian</span>
          <span
            v-if="pembelianList.length > 0"
            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
            :class="activeTab === 'pembelian' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
          >
            {{ pembelianList.length }}
          </span>
          <span
            v-if="activeTab === 'pembelian'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-t-full"
          ></span>
        </button>

        <button
          @click="activeTab = 'supplier'"
          class="pb-3 text-xs font-bold transition-all relative flex items-center gap-2 cursor-pointer"
          :class="activeTab === 'supplier' ? 'text-indigo-600' : 'text-slate-500 hover:text-slate-800'"
        >
          <Truck class="w-4 h-4" />
          <span>Supplier</span>
          <span
            v-if="supplierList.length > 0"
            class="px-2 py-0.5 rounded-full text-[10px] font-bold"
            :class="activeTab === 'supplier' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-600'"
          >
            {{ supplierList.length }}
          </span>
          <span
            v-if="activeTab === 'supplier'"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-t-full"
          ></span>
        </button>
      </div>

      <!-- TAB 1: DATA PEMBELIAN -->
      <div v-if="activeTab === 'pembelian'" class="p-6 space-y-5">
        <!-- Filter Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <!-- Search -->
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchPembelian"
              type="text"
              placeholder="Cari transaksi pembelian..."
              class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>

          <!-- Date Start -->
          <div class="relative">
            <Calendar class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="startDate"
              type="date"
              class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
              title="Tanggal Mulai"
            />
          </div>

          <!-- Date End -->
          <div class="relative">
            <Calendar class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="endDate"
              type="date"
              class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
              title="Tanggal Akhir"
            />
          </div>

          <!-- Status Dropdown -->
          <div class="relative">
            <Filter class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            <select
              v-model="statusFilter"
              class="w-full pl-9 pr-8 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 bg-white focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition appearance-none cursor-pointer"
            >
              <option value="">Semua Status</option>
              <option value="selesai">Selesai</option>
              <option value="draft">Draft / Diproses</option>
            </select>
          </div>
        </div>

        <!-- Table Pembelian -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 w-12 text-center">No</th>
                <th class="py-3 px-4">No Pembelian</th>
                <th class="py-3 px-4">Tanggal</th>
                <th class="py-3 px-4">Supplier</th>
                <th class="py-3 px-4 text-right">Total</th>
                <th class="py-3 px-4 text-center">Status</th>
                <th class="py-3 px-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="loading" class="text-center">
                <td colspan="7" class="py-12 text-slate-400">
                  <div class="flex flex-col items-center gap-2">
                    <Loader2 class="w-6 h-6 animate-spin text-indigo-500" />
                    <span>Memuat data pembelian...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="paginatedPembelian.length === 0" class="text-center">
                <td colspan="7" class="py-12 text-slate-400">
                  Tidak ada transaksi pembelian yang cocok
                </td>
              </tr>
              <tr
                v-for="(item, index) in paginatedPembelian"
                :key="item.id_pembelian"
                class="hover:bg-slate-50/70 transition-colors"
              >
                <td class="py-3 px-4 text-center text-slate-500 font-medium">
                  {{ (currentPagePembelian - 1) * itemsPerPagePembelian + index + 1 }}
                </td>
                <td class="py-3 px-4 font-bold text-slate-800">
                  {{ item.nomor_faktur }}
                </td>
                <td class="py-3 px-4 text-slate-600">
                  {{ item.tanggal_faktur ? new Date(item.tanggal_faktur).toLocaleDateString('id-ID') : '-' }}
                </td>
                <td class="py-3 px-4 text-slate-800 font-medium">
                  {{ item.supplier?.nama || '-' }}
                </td>
                <td class="py-3 px-4 text-right font-bold text-slate-900">
                  {{ formatRupiah(item.total_bayar) }}
                </td>
                <td class="py-3 px-4 text-center">
                  <span
                    v-if="item.status_pembelian === 'selesai'"
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 inline-flex items-center gap-1"
                  >
                    <CheckCircle2 class="w-3 h-3" />
                    Selesai
                  </span>
                  <span
                    v-else
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 inline-flex items-center gap-1"
                  >
                    <Clock class="w-3 h-3" />
                    Diproses
                  </span>
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="viewDetailPembelian(item)"
                      class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition cursor-pointer"
                      title="Lihat Detail"
                    >
                      <Eye class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDeletePembelian(item.id_pembelian)"
                      class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50/50 transition cursor-pointer"
                      title="Hapus"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Pembelian -->
        <div class="flex items-center justify-between text-xs text-slate-500 pt-2">
          <span>Menampilkan {{ paginatedPembelian.length }} dari {{ filteredPembelian.length }} data</span>
          <div class="flex items-center gap-1">
            <button
              :disabled="currentPagePembelian === 1"
              @click="currentPagePembelian--"
              class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
            <span class="px-3 py-1 font-semibold text-slate-700">
              {{ currentPagePembelian }} / {{ totalPagesPembelian }}
            </span>
            <button
              :disabled="currentPagePembelian >= totalPagesPembelian"
              @click="currentPagePembelian++"
              class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- TAB 2: SUPPLIER -->
      <div v-if="activeTab === 'supplier'" class="p-6 space-y-5">
        <!-- Search Filter -->
        <div class="max-w-md">
          <div class="relative">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchSupplier"
              type="text"
              placeholder="Cari nama atau telepon supplier..."
              class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition"
            />
          </div>
        </div>

        <!-- Table Supplier -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                <th class="py-3 px-4 w-12 text-center">No</th>
                <th class="py-3 px-4">Nama Supplier</th>
                <th class="py-3 px-4">No. Telepon / Kontak</th>
                <th class="py-3 px-4">Alamat</th>
                <th class="py-3 px-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="loading" class="text-center">
                <td colspan="5" class="py-12 text-slate-400">
                  <div class="flex flex-col items-center gap-2">
                    <Loader2 class="w-6 h-6 animate-spin text-indigo-500" />
                    <span>Memuat data supplier...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="paginatedSupplier.length === 0" class="text-center">
                <td colspan="5" class="py-12 text-slate-400">
                  Tidak ada data supplier ditemukan
                </td>
              </tr>
              <tr
                v-for="(sup, idx) in paginatedSupplier"
                :key="sup.id_supplier"
                class="hover:bg-slate-50/70 transition-colors"
              >
                <td class="py-3 px-4 text-center text-slate-500 font-medium">
                  {{ (currentPageSupplier - 1) * itemsPerPageSupplier + idx + 1 }}
                </td>
                <td class="py-3 px-4 font-bold text-slate-800">
                  {{ sup.nama }}
                </td>
                <td class="py-3 px-4 text-slate-600">
                  {{ sup.no_telepon || '-' }}
                </td>
                <td class="py-3 px-4 text-slate-600">
                  {{ sup.alamat_supplier || '-' }}
                </td>
                <td class="py-3 px-4 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="openEditSupplier(sup)"
                      class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-300 hover:bg-indigo-50/50 transition cursor-pointer"
                      title="Edit Supplier"
                    >
                      <Edit2 class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDeleteSupplier(sup.id_supplier)"
                      class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:text-rose-600 hover:border-rose-300 hover:bg-rose-50/50 transition cursor-pointer"
                      title="Hapus Supplier"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Supplier -->
        <div class="flex items-center justify-between text-xs text-slate-500 pt-2">
          <span>Menampilkan {{ paginatedSupplier.length }} dari {{ filteredSupplier.length }} supplier</span>
          <div class="flex items-center gap-1">
            <button
              :disabled="currentPageSupplier === 1"
              @click="currentPageSupplier--"
              class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              <ChevronLeft class="w-4 h-4" />
            </button>
            <span class="px-3 py-1 font-semibold text-slate-700">
              {{ currentPageSupplier }} / {{ totalPagesSupplier }}
            </span>
            <button
              :disabled="currentPageSupplier >= totalPagesSupplier"
              @click="currentPageSupplier++"
              class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed transition"
            >
              <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: TAMBAH PEMBELIAN -->
    <div
      v-if="showModalPembelian"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs overflow-y-auto"
    >
      <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 space-y-5 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h2 class="text-base font-bold text-slate-800">Tambah Transaksi Pembelian</h2>
          <button @click="showModalPembelian = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div v-if="errorMessage" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSavePembelian" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">No. Faktur / PO</label>
              <input
                v-model="formPembelian.nomor_faktur"
                type="text"
                required
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden font-mono"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Faktur</label>
              <input
                v-model="formPembelian.tanggal_faktur"
                type="date"
                required
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Supplier</label>
              <select
                v-model="formPembelian.id_supplier"
                required
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden bg-white"
              >
                <option value="" disabled>-- Pilih Supplier --</option>
                <option v-for="s in supplierList" :key="s.id_supplier" :value="s.id_supplier">
                  {{ s.nama }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pembelian</label>
              <select
                v-model="formPembelian.status_pembelian"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden bg-white"
              >
                <option value="selesai">Selesai (Stok langsung bertambah)</option>
                <option value="draft">Draft (Diproses)</option>
              </select>
            </div>
          </div>

          <!-- Items Row -->
          <div class="border-t border-slate-100 pt-3">
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-slate-800">Daftar Barang yang Dibeli</label>
              <button
                type="button"
                @click="addItemRow"
                class="text-xs text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1 cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                Tambah Baris
              </button>
            </div>

            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
              <div
                v-for="(item, idx) in formPembelian.items"
                :key="idx"
                class="flex items-center gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-200/70"
              >
                <!-- Produk -->
                <div class="flex-1">
                  <select
                    v-model="item.id_barang"
                    @change="onBarangChange(idx)"
                    required
                    class="w-full px-2.5 py-1.5 bg-white rounded-lg border border-slate-200 text-xs outline-hidden"
                  >
                    <option value="" disabled>Pilih Produk</option>
                    <option v-for="b in barangList" :key="b.id_barang" :value="b.id_barang">
                      {{ b.nama_barang }} (Stok: {{ b.stok }})
                    </option>
                  </select>
                </div>

                <!-- Qty -->
                <div class="w-20">
                  <input
                    v-model.number="item.jumlah"
                    type="number"
                    min="1"
                    required
                    placeholder="Qty"
                    class="w-full px-2 py-1.5 bg-white rounded-lg border border-slate-200 text-xs text-center outline-hidden"
                  />
                </div>

                <!-- Satuan -->
                <div class="w-20">
                  <input
                    v-model="item.satuan"
                    type="text"
                    placeholder="Satuan"
                    class="w-full px-2 py-1.5 bg-white rounded-lg border border-slate-200 text-xs text-center outline-hidden"
                  />
                </div>

                <!-- Subtotal Preview -->
                <div class="w-28 text-right font-semibold text-slate-700 text-xs px-2">
                  {{ formatRupiah((item.harga_beli || 0) * (item.jumlah || 1)) }}
                </div>

                <!-- Delete row button -->
                <button
                  type="button"
                  @click="removeItemRow(idx)"
                  class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Total Bayar Summary -->
          <div class="flex items-center justify-between p-3 bg-slate-900 text-white rounded-xl">
            <span class="text-xs font-semibold text-slate-300">Estimasi Total Bayar:</span>
            <span class="text-base font-bold">{{ formatRupiah(formTotalBayar) }}</span>
          </div>

          <!-- Modal Actions -->
          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="showModalPembelian = false"
              class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <Loader2 v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
              <span>Simpan Pembelian</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: DETAIL PEMBELIAN -->
    <div
      v-if="showDetailModal && selectedDetail"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h2 class="text-base font-bold text-slate-800">Detail Faktur Pembelian</h2>
            <p class="text-xs text-slate-500">{{ selectedDetail.nomor_faktur }}</p>
          </div>
          <button @click="showDetailModal = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs bg-slate-50 p-3 rounded-xl">
          <div>
            <span class="text-slate-400">Supplier:</span>
            <p class="font-bold text-slate-800">{{ selectedDetail.supplier?.nama || '-' }}</p>
          </div>
          <div>
            <span class="text-slate-400">Tanggal:</span>
            <p class="font-bold text-slate-800">{{ selectedDetail.tanggal_faktur ? new Date(selectedDetail.tanggal_faktur).toLocaleDateString('id-ID') : '-' }}</p>
          </div>
          <div>
            <span class="text-slate-400">Status:</span>
            <p class="font-bold text-slate-800 capitalize">{{ selectedDetail.status_pembelian }}</p>
          </div>
          <div>
            <span class="text-slate-400">Petugas / User:</span>
            <p class="font-bold text-slate-800">{{ selectedDetail.user?.username || '-' }}</p>
          </div>
        </div>

        <!-- Detail Items -->
        <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
          <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase">
              <tr>
                <th class="py-2 px-3">Barang</th>
                <th class="py-2 px-3 text-center">Jumlah</th>
                <th class="py-2 px-3 text-right">Harga</th>
                <th class="py-2 px-3 text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="d in selectedDetail.detail" :key="d.id_detail_pembelian">
                <td class="py-2.5 px-3 font-medium text-slate-800">{{ d.barang?.nama_barang || 'Barang #' + d.id_barang }}</td>
                <td class="py-2.5 px-3 text-center">{{ d.jumlah }} {{ d.satuan }}</td>
                <td class="py-2.5 px-3 text-right">{{ formatRupiah(d.harga_beli) }}</td>
                <td class="py-2.5 px-3 text-right font-bold text-slate-900">{{ formatRupiah(d.subtotal) }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200 font-bold">
              <tr>
                <td colspan="3" class="py-2 px-3 text-right">Total Bayar:</td>
                <td class="py-2 px-3 text-right text-indigo-600">{{ formatRupiah(selectedDetail.total_bayar) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="showDetailModal = false"
            class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: TAMBAH / EDIT SUPPLIER -->
    <div
      v-if="showModalSupplier"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
    >
      <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h2 class="text-base font-bold text-slate-800">
            {{ isEditingSupplier ? 'Edit Data Supplier' : 'Tambah Supplier Baru' }}
          </h2>
          <button @click="showModalSupplier = false" class="p-1 text-slate-400 hover:text-slate-600 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <div v-if="errorMessage" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 flex items-center gap-2">
          <AlertCircle class="w-4 h-4 shrink-0" />
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleSaveSupplier" class="space-y-3">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Supplier / PT</label>
            <input
              v-model="formSupplier.nama"
              type="text"
              required
              placeholder="Contoh: PT. Sumber Makmur"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
            <input
              v-model="formSupplier.no_telepon"
              type="text"
              placeholder="Contoh: 08123456789"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Supplier</label>
            <textarea
              v-model="formSupplier.alamat_supplier"
              rows="3"
              placeholder="Alamat lengkap kantor / gudang supplier"
              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-hidden"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-3">
            <button
              type="button"
              @click="showModalSupplier = false"
              class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition flex items-center gap-2 cursor-pointer disabled:opacity-50"
            >
              <Loader2 v-if="isSubmitting" class="w-3.5 h-3.5 animate-spin" />
              <span>Simpan Supplier</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

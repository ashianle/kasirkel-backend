<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  Plus,
  Search,
  Eye,
  Trash2,
  Edit2,
  ChevronLeft,
  ChevronRight,
  X,
  Loader2
} from 'lucide-vue-next'
import apiClient from '@/api/client'

const activeTab = ref<'pembelian' | 'supplier'>('pembelian')
const loading = ref(false)

const pembelianList = ref<any[]>([
  { id_pembelian: 1, nomor_faktur: 'PB23240830001', tanggal_faktur: '2024-08-30', supplier: { nama: 'CV. Maju Jaya' }, total_bayar: 1200000, status_pembelian: 'selesai' },
  { id_pembelian: 2, nomor_faktur: 'PB23240828001', tanggal_faktur: '2024-08-28', supplier: { nama: 'PT. Sumber Rezeki' }, total_bayar: 850000, status_pembelian: 'selesai' },
  { id_pembelian: 3, nomor_faktur: 'PB23240825001', tanggal_faktur: '2024-08-25', supplier: { nama: 'Toko ATK Makmur' }, total_bayar: 540000, status_pembelian: 'selesai' },
  { id_pembelian: 4, nomor_faktur: 'PB23240820001', tanggal_faktur: '2024-08-20', supplier: { nama: 'CV. Utama' }, total_bayar: 1750000, status_pembelian: 'diproses' },
  { id_pembelian: 5, nomor_faktur: 'PB23240818001', tanggal_faktur: '2024-08-18', supplier: { nama: 'Toko Bina Sarana' }, total_bayar: 620000, status_pembelian: 'selesai' }
])

const supplierList = ref<any[]>([
  { id_supplier: 1, nama: 'CV. Maju Jaya', no_telepon: '08123456789', alamat_supplier: 'Jl. Perintis No. 12' },
  { id_supplier: 2, nama: 'PT. Sumber Rezeki', no_telepon: '08129876543', alamat_supplier: 'Jl. Industri No. 5' },
  { id_supplier: 3, nama: 'Toko ATK Makmur', no_telepon: '08137788990', alamat_supplier: 'Kawasan Grosir ATK' }
])
const barangList = ref<any[]>([])

// Filters
const searchPembelian = ref('')
const startDate = ref('')
const endDate = ref('')
const statusFilter = ref('Semua')
const searchSupplier = ref('')

// Pagination
const itemsPerPage = ref(10)
const currentPagePembelian = ref(1)
const currentPageSupplier = ref(1)

// Modals
const showModalPembelian = ref(false)
const showModalSupplier = ref(false)
const showDetailModal = ref(false)
const selectedDetail = ref<any>(null)
const isSubmitting = ref(false)
const isEditingSupplier = ref(false)

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

const formSupplier = ref({
  id_supplier: null as number | null,
  nama: '',
  no_telepon: '',
  alamat_supplier: ''
})

const formatRupiah = (val: number | string) => {
  const num = Number(val) || 0
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(num)
}

const fetchData = async () => {
  loading.value = true
  try {
    const [pembelianRes, supplierRes, barangRes] = await Promise.all([
      apiClient.get('/pembelian').catch(() => ({ data: [] })),
      apiClient.get('/supplier').catch(() => ({ data: [] })),
      apiClient.get('/barang').catch(() => ({ data: [] }))
    ])
    if (Array.isArray(pembelianRes.data) && pembelianRes.data.length > 0) {
      pembelianList.value = pembelianRes.data
    }
    if (Array.isArray(supplierRes.data) && supplierRes.data.length > 0) {
      supplierList.value = supplierRes.data
    }
    if (Array.isArray(barangRes.data) && barangRes.data.length > 0) {
      barangList.value = barangRes.data
    }
  } catch (err) {
    console.error('Error fetching pembelian:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Pembelian Filter & Pagination
const filteredPembelian = computed(() => {
  return pembelianList.value.filter((item) => {
    const matchSearch =
      !searchPembelian.value ||
      item.nomor_faktur?.toLowerCase().includes(searchPembelian.value.toLowerCase()) ||
      item.supplier?.nama?.toLowerCase().includes(searchPembelian.value.toLowerCase())

    const matchStatus =
      statusFilter.value === 'Semua' ||
      item.status_pembelian?.toLowerCase() === statusFilter.value.toLowerCase()

    const itemDate = item.tanggal_faktur ? item.tanggal_faktur.split('T')[0] : ''
    const matchStart = !startDate.value || itemDate >= startDate.value
    const matchEnd = !endDate.value || itemDate <= endDate.value

    return matchSearch && matchStatus && matchStart && matchEnd
  })
})

const totalPagesPembelian = computed(() => {
  return Math.ceil(filteredPembelian.value.length / itemsPerPage.value) || 1
})

const paginatedPembelian = computed(() => {
  const start = (currentPagePembelian.value - 1) * itemsPerPage.value
  return filteredPembelian.value.slice(start, start + itemsPerPage.value)
})

// Supplier Filter & Pagination
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
  return Math.ceil(filteredSupplier.value.length / itemsPerPage.value) || 1
})

const paginatedSupplier = computed(() => {
  const start = (currentPageSupplier.value - 1) * itemsPerPage.value
  return filteredSupplier.value.slice(start, start + itemsPerPage.value)
})

// Open Add Pembelian
const openAddPembelian = () => {
  const dateStr = new Date().toISOString().slice(2, 10).replace(/-/g, '')
  const randomNum = Math.floor(1000 + Math.random() * 9000)
  formPembelian.value = {
    id_supplier: supplierList.value[0]?.id_supplier || '',
    nomor_faktur: `PB${dateStr}${randomNum}`,
    tanggal_faktur: new Date().toISOString().split('T')[0],
    status_pembelian: 'selesai',
    jenis_transaksi: 'Tunai',
    cara_bayar: 'Tunai',
    note: '',
    items: [
      { id_barang: barangList.value[0]?.id_barang || '', satuan: 'Pcs', jumlah: 1, harga_beli: barangList.value[0]?.harga_beli || 0 }
    ]
  }
  showModalPembelian.value = true
}

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

const handleSavePembelian = async () => {
  if (!formPembelian.value.id_supplier) {
    alert('Silakan pilih supplier!')
    return
  }

  isSubmitting.value = true
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

    await apiClient.post('/pembelian', payload)
    showModalPembelian.value = false
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan pembelian!')
  } finally {
    isSubmitting.value = false
  }
}

const handleDeletePembelian = async (id: number) => {
  if (!confirm('Hapus transaksi pembelian ini?')) return
  try {
    await apiClient.delete(`/pembelian/${id}`)
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus pembelian!')
  }
}

const viewDetailPembelian = (item: any) => {
  selectedDetail.value = item
  showDetailModal.value = true
}

// Supplier Modal Handlers
const openAddSupplier = () => {
  isEditingSupplier.value = false
  formSupplier.value = { id_supplier: null, nama: '', no_telepon: '', alamat_supplier: '' }
  showModalSupplier.value = true
}

const openEditSupplier = (s: any) => {
  isEditingSupplier.value = true
  formSupplier.value = {
    id_supplier: s.id_supplier,
    nama: s.nama,
    no_telepon: s.no_telepon || '',
    alamat_supplier: s.alamat_supplier || ''
  }
  showModalSupplier.value = true
}

const handleSaveSupplier = async () => {
  if (!formSupplier.value.nama) {
    alert('Nama supplier wajib diisi!')
    return
  }
  isSubmitting.value = true
  try {
    if (isEditingSupplier.value && formSupplier.value.id_supplier) {
      await apiClient.put(`/supplier/${formSupplier.value.id_supplier}`, formSupplier.value)
    } else {
      await apiClient.post('/supplier', formSupplier.value)
    }
    showModalSupplier.value = false
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan supplier!')
  } finally {
    isSubmitting.value = false
  }
}

const handleDeleteSupplier = async (id: number) => {
  if (!confirm('Hapus data supplier ini?')) return
  try {
    await apiClient.delete(`/supplier/${id}`)
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus supplier!')
  }
}
</script>

<template>
  <div class="p-6 space-y-4 max-w-7xl mx-auto">
    <!-- Top Tabs: [ Data Pembelian ] | [ Supplier ] -->
    <div class="border-b border-gray-200 flex items-center gap-8">
      <button
        @click="activeTab = 'pembelian'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'pembelian' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Data Pembelian</span>
        <span
          v-if="activeTab === 'pembelian'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>

      <button
        @click="activeTab = 'supplier'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'supplier' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Supplier</span>
        <span
          v-if="activeTab === 'supplier'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>
    </div>

    <!-- TAB 1: DATA PEMBELIAN -->
    <div v-if="activeTab === 'pembelian'" class="space-y-4">
      <!-- Action Header -->
      <div class="flex items-center justify-between pt-1">
        <h1 class="text-base font-bold text-gray-900">Data Pembelian</h1>
        <button
          @click="openAddPembelian"
          class="flex items-center gap-1.5 px-3 py-2 bg-[#23272f] hover:bg-black text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>Tambah Pembelian</span>
        </button>
      </div>

      <!-- Filter Row -->
      <div class="flex flex-col sm:flex-row items-end gap-3">
        <!-- Search Input -->
        <div class="flex-1 w-full relative">
          <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchPembelian"
            type="text"
            placeholder="Cari no. pembelian..."
            class="w-full pl-9 pr-3 py-2 bg-white rounded-lg border border-gray-200 text-xs outline-hidden"
          />
        </div>

        <!-- Tanggal Filter [ dd/mm/yyyy ] - [ dd/mm/yyyy ] -->
        <div class="flex items-center gap-1.5 w-full sm:w-auto">
          <div>
            <label class="block text-[11px] text-gray-500 font-medium mb-1">Tanggal</label>
            <div class="flex items-center gap-1">
              <input
                v-model="startDate"
                type="date"
                class="px-2.5 py-1.5 bg-white rounded-lg border border-gray-200 text-xs text-gray-700 outline-hidden"
              />
              <span class="text-gray-400">-</span>
              <input
                v-model="endDate"
                type="date"
                class="px-2.5 py-1.5 bg-white rounded-lg border border-gray-200 text-xs text-gray-700 outline-hidden"
              />
            </div>
          </div>
        </div>

        <!-- Status Filter -->
        <div class="w-full sm:w-36">
          <label class="block text-[11px] text-gray-500 font-medium mb-1">Status</label>
          <select
            v-model="statusFilter"
            class="w-full px-3 py-2 bg-white rounded-lg border border-gray-200 text-xs text-gray-700 outline-hidden cursor-pointer"
          >
            <option value="Semua">Semua</option>
            <option value="selesai">Selesai</option>
            <option value="diproses">Diproses</option>
          </select>
        </div>
      </div>

      <!-- Table Pembelian -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold text-[11px]">
                <th class="py-2.5 px-3 w-10 text-center">No</th>
                <th class="py-2.5 px-3">No Pembelian</th>
                <th class="py-2.5 px-3">Tanggal</th>
                <th class="py-2.5 px-3">Supplier</th>
                <th class="py-2.5 px-3">Total</th>
                <th class="py-2.5 px-3 text-center">Status</th>
                <th class="py-2.5 px-3 text-center w-24">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-800">
              <tr v-if="loading" class="text-center">
                <td colspan="7" class="py-12 text-gray-400">
                  <Loader2 class="w-5 h-5 animate-spin mx-auto text-gray-400 mb-1" />
                  <span>Memuat data pembelian...</span>
                </td>
              </tr>
              <tr v-else-if="paginatedPembelian.length === 0" class="text-center">
                <td colspan="7" class="py-12 text-gray-400">
                  Tidak ada data pembelian ditemukan
                </td>
              </tr>
              <tr
                v-for="(item, idx) in paginatedPembelian"
                :key="item.id_pembelian"
                class="hover:bg-gray-50/60 transition"
              >
                <td class="py-3 px-3 text-center text-gray-500 font-medium">
                  {{ (currentPagePembelian - 1) * itemsPerPage + idx + 1 }}
                </td>
                <td class="py-3 px-3 font-semibold text-gray-900 font-mono text-[11px]">
                  {{ item.nomor_faktur }}
                </td>
                <td class="py-3 px-3 text-gray-600">
                  {{ item.tanggal_faktur ? new Date(item.tanggal_faktur).toLocaleDateString('id-ID') : '30-08-2024' }}
                </td>
                <td class="py-3 px-3 text-gray-800">
                  {{ item.supplier?.nama || 'CV. Maju Jaya' }}
                </td>
                <td class="py-3 px-3 font-medium text-gray-900">
                  {{ formatRupiah(item.total_bayar) }}
                </td>
                <td class="py-3 px-3 text-center">
                  <span
                    class="px-2.5 py-0.5 rounded-md text-[10px] font-medium border capitalize"
                    :class="
                      item.status_pembelian === 'selesai'
                        ? 'border-gray-300 text-gray-800 bg-white'
                        : 'border-gray-200 text-gray-500 bg-gray-50'
                    "
                  >
                    {{ item.status_pembelian }}
                  </span>
                </td>
                <td class="py-3 px-3 text-center">
                  <div class="flex items-center justify-center gap-1.5">
                    <button
                      @click="viewDetailPembelian(item)"
                      class="p-1 text-gray-500 hover:text-gray-900 cursor-pointer"
                      title="Lihat Detail"
                    >
                      <Eye class="w-3.5 h-3.5" />
                    </button>
                    <button
                      @click="handleDeletePembelian(item.id_pembelian)"
                      class="p-1 text-gray-500 hover:text-rose-600 cursor-pointer"
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
      </div>

      <!-- Pagination Row -->
      <div class="flex items-center justify-between text-xs text-gray-600 pt-1">
        <div class="flex items-center gap-2">
          <span>Tampilkan</span>
          <select
            v-model="itemsPerPage"
            class="px-2 py-1 rounded border border-gray-200 bg-white text-xs cursor-pointer"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
          </select>
          <span>data</span>
        </div>

        <div class="flex items-center gap-1">
          <button
            :disabled="currentPagePembelian === 1"
            @click="currentPagePembelian--"
            class="w-7 h-7 rounded border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <ChevronLeft class="w-3.5 h-3.5 text-gray-600" />
          </button>

          <button
            v-for="p in totalPagesPembelian"
            :key="p"
            @click="currentPagePembelian = p"
            class="w-7 h-7 rounded text-xs font-semibold flex items-center justify-center transition"
            :class="currentPagePembelian === p ? 'bg-black text-white' : 'border border-gray-200 text-gray-700 hover:bg-gray-50'"
          >
            {{ p }}
          </button>

          <button
            :disabled="currentPagePembelian >= totalPagesPembelian"
            @click="currentPagePembelian++"
            class="w-7 h-7 rounded border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <ChevronRight class="w-3.5 h-3.5 text-gray-600" />
          </button>
        </div>
      </div>
    </div>

    <!-- TAB 2: SUPPLIER -->
    <div v-if="activeTab === 'supplier'" class="space-y-4">
      <div class="flex items-center justify-between pt-1">
        <h1 class="text-base font-bold text-gray-900">Data Supplier</h1>
        <button
          @click="openAddSupplier"
          class="flex items-center gap-1.5 px-3 py-2 bg-[#23272f] hover:bg-black text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>Tambah Supplier</span>
        </button>
      </div>

      <div class="max-w-md relative">
        <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input
          v-model="searchSupplier"
          type="text"
          placeholder="Cari nama atau telepon supplier..."
          class="w-full pl-9 pr-3 py-2 bg-white rounded-lg border border-gray-200 text-xs outline-hidden"
        />
      </div>

      <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold text-[11px]">
              <th class="py-2.5 px-3 w-10 text-center">No</th>
              <th class="py-2.5 px-3">Nama Supplier</th>
              <th class="py-2.5 px-3">No. Telepon / Kontak</th>
              <th class="py-2.5 px-3">Alamat</th>
              <th class="py-2.5 px-3 text-center w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-800">
            <tr v-for="(sup, idx) in paginatedSupplier" :key="sup.id_supplier" class="hover:bg-gray-50/60">
              <td class="py-3 px-3 text-center text-gray-500 font-medium">{{ idx + 1 }}</td>
              <td class="py-3 px-3 font-semibold text-gray-900">{{ sup.nama }}</td>
              <td class="py-3 px-3 text-gray-600">{{ sup.no_telepon || '-' }}</td>
              <td class="py-3 px-3 text-gray-600">{{ sup.alamat_supplier || '-' }}</td>
              <td class="py-3 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button @click="openEditSupplier(sup)" class="p-1 text-gray-500 hover:text-gray-900">
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button @click="handleDeleteSupplier(sup.id_supplier)" class="p-1 text-gray-500 hover:text-rose-600">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Supplier -->
      <div class="flex items-center justify-between text-xs text-gray-600 pt-1">
        <span>Menampilkan {{ paginatedSupplier.length }} dari {{ filteredSupplier.length }} supplier</span>
        <div class="flex items-center gap-1">
          <button
            :disabled="currentPageSupplier === 1"
            @click="currentPageSupplier--"
            class="w-7 h-7 rounded border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <ChevronLeft class="w-3.5 h-3.5 text-gray-600" />
          </button>
          <button
            v-for="p in totalPagesSupplier"
            :key="p"
            @click="currentPageSupplier = p"
            class="w-7 h-7 rounded text-xs font-semibold flex items-center justify-center transition"
            :class="currentPageSupplier === p ? 'bg-black text-white' : 'border border-gray-200 text-gray-700 hover:bg-gray-50'"
          >
            {{ p }}
          </button>
          <button
            :disabled="currentPageSupplier >= totalPagesSupplier"
            @click="currentPageSupplier++"
            class="w-7 h-7 rounded border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
          >
            <ChevronRight class="w-3.5 h-3.5 text-gray-600" />
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: TAMBAH PEMBELIAN -->
    <div
      v-if="showModalPembelian"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs overflow-y-auto"
    >
      <div class="bg-white rounded-xl max-w-xl w-full p-5 shadow-2xl border border-gray-200 space-y-4 my-8">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="text-sm font-bold text-gray-900">Tambah Transaksi Pembelian</h3>
          <button @click="showModalPembelian = false" class="text-gray-400 hover:text-gray-700 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSavePembelian" class="space-y-3 text-xs">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">No. Faktur / PO</label>
              <input
                v-model="formPembelian.nomor_faktur"
                type="text"
                required
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden font-mono"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Tanggal</label>
              <input
                v-model="formPembelian.tanggal_faktur"
                type="date"
                required
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Supplier</label>
              <select
                v-model="formPembelian.id_supplier"
                required
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden bg-white cursor-pointer"
              >
                <option value="" disabled>-- Pilih Supplier --</option>
                <option v-for="s in supplierList" :key="s.id_supplier" :value="s.id_supplier">
                  {{ s.nama }}
                </option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Status</label>
              <select
                v-model="formPembelian.status_pembelian"
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden bg-white cursor-pointer"
              >
                <option value="selesai">Selesai</option>
                <option value="diproses">Diproses</option>
              </select>
            </div>
          </div>

          <!-- Items Row -->
          <div class="border-t border-gray-100 pt-2">
            <div class="flex items-center justify-between mb-1.5">
              <span class="font-bold text-gray-800">Daftar Barang</span>
              <button
                type="button"
                @click="addItemRow"
                class="text-xs font-semibold text-gray-700 hover:text-black flex items-center gap-1 cursor-pointer"
              >
                <Plus class="w-3.5 h-3.5" />
                Tambah Baris
              </button>
            </div>

            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <div
                v-for="(item, idx) in formPembelian.items"
                :key="idx"
                class="flex items-center gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200"
              >
                <div class="flex-1">
                  <select
                    v-model="item.id_barang"
                    @change="onBarangChange(idx)"
                    required
                    class="w-full px-2 py-1 bg-white rounded border border-gray-200 text-xs"
                  >
                    <option value="" disabled>Pilih Produk</option>
                    <option v-for="b in barangList" :key="b.id_barang" :value="b.id_barang">
                      {{ b.nama }}
                    </option>
                  </select>
                </div>
                <div class="w-16">
                  <input
                    v-model.number="item.jumlah"
                    type="number"
                    min="1"
                    required
                    placeholder="Qty"
                    class="w-full px-2 py-1 bg-white rounded border border-gray-200 text-xs text-center"
                  />
                </div>
                <div class="w-24 text-right font-bold text-gray-900 px-1">
                  {{ formatRupiah((item.harga_beli || 0) * (item.jumlah || 1)) }}
                </div>
                <button
                  type="button"
                  @click="removeItemRow(idx)"
                  class="p-1 text-gray-400 hover:text-rose-600"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between p-2.5 bg-gray-100 rounded-lg text-xs font-bold text-gray-900">
            <span>Estimasi Total:</span>
            <span>{{ formatRupiah(formTotalBayar) }}</span>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button
              type="button"
              @click="showModalPembelian = false"
              class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-1.5 rounded-lg bg-black hover:bg-neutral-800 text-white font-semibold cursor-pointer disabled:opacity-50"
            >
              Simpan Pembelian
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: TAMBAH / EDIT SUPPLIER -->
    <div
      v-if="showModalSupplier"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="text-sm font-bold text-gray-900">
            {{ isEditingSupplier ? 'Edit Data Supplier' : 'Tambah Supplier Baru' }}
          </h3>
          <button @click="showModalSupplier = false" class="text-gray-400 hover:text-gray-700 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSaveSupplier" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Supplier</label>
            <input
              v-model="formSupplier.nama"
              type="text"
              required
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">No. Telepon / HP</label>
            <input
              v-model="formSupplier.no_telepon"
              type="text"
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Alamat</label>
            <textarea
              v-model="formSupplier.alamat_supplier"
              rows="3"
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
            <button
              type="button"
              @click="showModalSupplier = false"
              class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-1.5 rounded-lg bg-black hover:bg-neutral-800 text-white font-semibold cursor-pointer disabled:opacity-50"
            >
              Simpan Supplier
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: DETAIL PEMBELIAN -->
    <div
      v-if="showDetailModal && selectedDetail"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <div>
            <h3 class="text-sm font-bold text-gray-900">Detail Pembelian</h3>
            <p class="text-[11px] text-gray-500 font-mono">{{ selectedDetail.nomor_faktur }}</p>
          </div>
          <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-700 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="space-y-1.5 text-xs text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-200">
          <div><span class="text-gray-400">Supplier:</span> <strong class="ml-1 text-gray-900">{{ selectedDetail.supplier?.nama || '-' }}</strong></div>
          <div><span class="text-gray-400">Tanggal:</span> <span class="ml-1">{{ selectedDetail.tanggal_faktur ? new Date(selectedDetail.tanggal_faktur).toLocaleDateString('id-ID') : '-' }}</span></div>
          <div><span class="text-gray-400">Status:</span> <span class="ml-1 font-semibold capitalize">{{ selectedDetail.status_pembelian }}</span></div>
          <div><span class="text-gray-400">Total Bayar:</span> <strong class="ml-1 text-gray-900">{{ formatRupiah(selectedDetail.total_bayar) }}</strong></div>
        </div>

        <div class="flex justify-end pt-2">
          <button
            @click="showDetailModal = false"
            class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-semibold"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

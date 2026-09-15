<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  Plus,
  Search,
  Edit2,
  Trash2,
  ChevronLeft,
  ChevronRight,
  X,
  Loader2
} from 'lucide-vue-next'
import apiClient from '@/api/client'

interface Barang {
  id_barang: number
  id_sekolah: number
  barcode: string
  nama: string
  nama_barang?: string
  satuan: string
  harga_beli: number
  harga_jual: number
  stok: number
  is_active: boolean
  kategori?: { id_kategori: number; nama_kategori: string }
  kelompok?: { id_kelompok_kategori: number; nama_kelompok_kategori: string }
}

const activeTab = ref<'produk' | 'kategori' | 'kelompok'>('produk')
const loading = ref(false)
const barangList = ref<Barang[]>([
  { id_barang: 1, id_sekolah: 1, barcode: '899123456', nama: 'Buku Tulis', satuan: 'Pcs', harga_beli: 4000, harga_jual: 5000, stok: 100, is_active: true, kategori: { id_kategori: 1, nama_kategori: 'ATK' } },
  { id_barang: 2, id_sekolah: 1, barcode: '899123457', nama: 'Pulpen', satuan: 'Pcs', harga_beli: 2500, harga_jual: 3000, stok: 200, is_active: true, kategori: { id_kategori: 1, nama_kategori: 'ATK' } },
  { id_barang: 3, id_sekolah: 1, barcode: '899123458', nama: 'Penghapus', satuan: 'Pcs', harga_beli: 1500, harga_jual: 2000, stok: 150, is_active: true, kategori: { id_kategori: 1, nama_kategori: 'ATK' } },
  { id_barang: 4, id_sekolah: 1, barcode: '899123459', nama: 'Penggaris', satuan: 'Pcs', harga_beli: 3000, harga_jual: 4000, stok: 120, is_active: true, kategori: { id_kategori: 1, nama_kategori: 'ATK' } },
  { id_barang: 5, id_sekolah: 1, barcode: '899123460', nama: 'Map Plastik', satuan: 'Pcs', harga_beli: 4500, harga_jual: 6000, stok: 80, is_active: false, kategori: { id_kategori: 2, nama_kategori: 'Perlengkapan' } }
])

// Filters
const search = ref('')
const filterKategori = ref('Semua')
const filterStatus = ref('Semua')

// Pagination
const itemsPerPage = ref(10)
const currentPage = ref(1)

// Modal State
const showModal = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const form = ref({
  id_barang: null as number | null,
  barcode: '',
  nama: '',
  satuan: 'Pcs',
  harga_beli: 0,
  harga_jual: 0,
  stok: 0,
  is_active: true
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
    const res = await apiClient.get('/barang')
    if (Array.isArray(res.data) && res.data.length > 0) {
      barangList.value = res.data.map((b: any) => ({
        ...b,
        nama: b.nama || b.nama_barang || 'Produk'
      }))
    }
  } catch (err) {
    console.error('Error fetching barang:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Filtered Items
const filteredList = computed(() => {
  return barangList.value.filter((b) => {
    const matchSearch =
      !search.value ||
      b.nama.toLowerCase().includes(search.value.toLowerCase()) ||
      (b.barcode && b.barcode.toLowerCase().includes(search.value.toLowerCase()))

    const kategoriName = b.kategori?.nama_kategori || 'ATK'
    const matchKategori =
      filterKategori.value === 'Semua' ||
      kategoriName.toLowerCase() === filterKategori.value.toLowerCase()

    const matchStatus =
      filterStatus.value === 'Semua' ||
      (filterStatus.value === 'Active' && b.is_active) ||
      (filterStatus.value === 'Nonaktif' && !b.is_active)

    return matchSearch && matchKategori && matchStatus
  })
})

const totalPages = computed(() => {
  return Math.ceil(filteredList.value.length / itemsPerPage.value) || 1
})

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredList.value.slice(start, start + itemsPerPage.value)
})

// Open Add Modal
const openAddModal = () => {
  isEditing.value = false
  form.value = {
    id_barang: null,
    barcode: `${Math.floor(100000000 + Math.random() * 900000000)}`,
    nama: '',
    satuan: 'Pcs',
    harga_beli: 0,
    harga_jual: 0,
    stok: 0,
    is_active: true
  }
  showModal.value = true
}

// Open Edit Modal
const openEditModal = (item: Barang) => {
  isEditing.value = true
  form.value = {
    id_barang: item.id_barang,
    barcode: item.barcode,
    nama: item.nama,
    satuan: item.satuan || 'Pcs',
    harga_beli: Number(item.harga_beli) || 0,
    harga_jual: Number(item.harga_jual) || 0,
    stok: item.stok || 0,
    is_active: item.is_active
  }
  showModal.value = true
}

// Save Product
const handleSave = async () => {
  if (!form.value.nama) {
    alert('Nama produk wajib diisi!')
    return
  }

  isSubmitting.value = true
  try {
    if (isEditing.value && form.value.id_barang) {
      await apiClient.put(`/barang/${form.value.id_barang}`, form.value)
    } else {
      await apiClient.post('/barang', form.value)
    }
    showModal.value = false
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan produk!')
  } finally {
    isSubmitting.value = false
  }
}

// Delete Product
const handleDelete = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return
  try {
    await apiClient.delete(`/barang/${id}`)
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus produk!')
  }
}
</script>

<template>
  <div class="p-6 space-y-4 max-w-7xl mx-auto">
    <!-- Top Tabs: [ Daftar Produk ] | [ Kategori ] | [ Kelompok Kategori ] -->
    <div class="border-b border-gray-200 flex items-center gap-8">
      <button
        @click="activeTab = 'produk'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'produk' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Daftar Produk</span>
        <span
          v-if="activeTab === 'produk'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>

      <button
        @click="activeTab = 'kategori'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'kategori' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Kategori</span>
        <span
          v-if="activeTab === 'kategori'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>

      <button
        @click="activeTab = 'kelompok'"
        class="pb-2.5 text-xs font-bold transition-all relative cursor-pointer"
        :class="activeTab === 'kelompok' ? 'text-gray-900' : 'text-gray-500 hover:text-gray-800'"
      >
        <span>Kelompok Kategori</span>
        <span
          v-if="activeTab === 'kelompok'"
          class="absolute bottom-0 left-0 right-0 h-0.5 bg-black rounded-t-full"
        ></span>
      </button>
    </div>

    <!-- Header: Title & + Tambah Produk Button -->
    <div class="flex items-center justify-between pt-1">
      <h1 class="text-base font-bold text-gray-900">Data Produk</h1>
      <button
        @click="openAddModal"
        class="flex items-center gap-1.5 px-3 py-2 bg-[#23272f] hover:bg-black text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
      >
        <Plus class="w-3.5 h-3.5" />
        <span>Tambah Produk</span>
      </button>
    </div>

    <!-- Filter Row -->
    <div class="flex flex-col sm:flex-row items-end gap-3">
      <!-- Search Input -->
      <div class="flex-1 w-full relative">
        <Search class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input
          v-model="search"
          type="text"
          placeholder="Cari produk..."
          class="w-full pl-9 pr-3 py-2 bg-white rounded-lg border border-gray-200 text-xs outline-hidden"
        />
      </div>

      <!-- Filter Kategori -->
      <div class="w-full sm:w-44">
        <label class="block text-[11px] text-gray-500 font-medium mb-1">Filter Kategori</label>
        <select
          v-model="filterKategori"
          class="w-full px-3 py-2 bg-white rounded-lg border border-gray-200 text-xs text-gray-700 outline-hidden cursor-pointer"
        >
          <option value="Semua">Semua</option>
          <option value="ATK">ATK</option>
          <option value="Perlengkapan">Perlengkapan</option>
          <option value="Makanan">Makanan</option>
          <option value="Minuman">Minuman</option>
        </select>
      </div>

      <!-- Status -->
      <div class="w-full sm:w-36">
        <label class="block text-[11px] text-gray-500 font-medium mb-1">Status</label>
        <select
          v-model="filterStatus"
          class="w-full px-3 py-2 bg-white rounded-lg border border-gray-200 text-xs text-gray-700 outline-hidden cursor-pointer"
        >
          <option value="Semua">Semua</option>
          <option value="Active">Active</option>
          <option value="Nonaktif">Nonaktif</option>
        </select>
      </div>
    </div>

    <!-- Table Produk -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold text-[11px]">
              <th class="py-2.5 px-3 w-10 text-center">No</th>
              <th class="py-2.5 px-3">Barcode</th>
              <th class="py-2.5 px-3">Nama Produk</th>
              <th class="py-2.5 px-3">Kategori</th>
              <th class="py-2.5 px-3">Harga Jual</th>
              <th class="py-2.5 px-3 text-center">Stok</th>
              <th class="py-2.5 px-3 text-center">Status</th>
              <th class="py-2.5 px-3 text-center w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-800">
            <tr v-if="loading" class="text-center">
              <td colspan="8" class="py-12 text-gray-400">
                <Loader2 class="w-5 h-5 animate-spin mx-auto text-gray-400 mb-1" />
                <span>Memuat data produk...</span>
              </td>
            </tr>
            <tr v-else-if="paginatedList.length === 0" class="text-center">
              <td colspan="8" class="py-12 text-gray-400">
                Tidak ada produk ditemukan
              </td>
            </tr>
            <tr
              v-for="(item, idx) in paginatedList"
              :key="item.id_barang"
              class="hover:bg-gray-50/60 transition"
            >
              <td class="py-3 px-3 text-center text-gray-500 font-medium">
                {{ (currentPage - 1) * itemsPerPage + idx + 1 }}
              </td>
              <td class="py-3 px-3 text-gray-600 font-mono text-[11px]">
                {{ item.barcode || '-' }}
              </td>
              <td class="py-3 px-3 font-semibold text-gray-900">
                {{ item.nama }}
              </td>
              <td class="py-3 px-3 text-gray-600">
                {{ item.kategori?.nama_kategori || 'ATK' }}
              </td>
              <td class="py-3 px-3 font-medium text-gray-900">
                {{ formatRupiah(item.harga_jual) }}
              </td>
              <td class="py-3 px-3 text-center font-semibold text-gray-800">
                {{ item.stok }}
              </td>
              <td class="py-3 px-3 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-md text-[10px] font-medium border"
                  :class="
                    item.is_active
                      ? 'border-gray-300 text-gray-800 bg-white'
                      : 'border-gray-200 text-gray-400 bg-gray-50'
                  "
                >
                  {{ item.is_active ? 'Active' : 'Nonaktif' }}
                </span>
              </td>
              <td class="py-3 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openEditModal(item)"
                    class="p-1 text-gray-500 hover:text-gray-900 cursor-pointer"
                    title="Edit Produk"
                  >
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="handleDelete(item.id_barang)"
                    class="p-1 text-gray-500 hover:text-rose-600 cursor-pointer"
                    title="Hapus Produk"
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
          :disabled="currentPage === 1"
          @click="currentPage--"
          class="w-7 h-7 rounded border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <ChevronLeft class="w-3.5 h-3.5 text-gray-600" />
        </button>

        <button
          v-for="p in totalPages"
          :key="p"
          @click="currentPage = p"
          class="w-7 h-7 rounded text-xs font-semibold flex items-center justify-center transition"
          :class="currentPage === p ? 'bg-black text-white' : 'border border-gray-200 text-gray-700 hover:bg-gray-50'"
        >
          {{ p }}
        </button>

        <button
          :disabled="currentPage >= totalPages"
          @click="currentPage++"
          class="w-7 h-7 rounded border border-gray-200 flex items-center justify-center hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
        >
          <ChevronRight class="w-3.5 h-3.5 text-gray-600" />
        </button>
      </div>
    </div>

    <!-- MODAL: TAMBAH / EDIT PRODUK -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="text-sm font-bold text-gray-900">
            {{ isEditing ? 'Edit Data Produk' : 'Tambah Produk Baru' }}
          </h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-700 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSave" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Barcode / SKU</label>
            <input
              v-model="form.barcode"
              type="text"
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden font-mono"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Produk</label>
            <input
              v-model="form.nama"
              type="text"
              required
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Satuan</label>
              <input
                v-model="form.satuan"
                type="text"
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Stok</label>
              <input
                v-model.number="form.stok"
                type="number"
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Harga Beli</label>
              <input
                v-model.number="form.harga_beli"
                type="number"
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
              />
            </div>
            <div>
              <label class="block font-semibold text-gray-700 mb-1">Harga Jual</label>
              <input
                v-model.number="form.harga_jual"
                type="number"
                class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
              />
            </div>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input
              id="is_active_prod"
              v-model="form.is_active"
              type="checkbox"
              class="rounded border-gray-300 text-black"
            />
            <label for="is_active_prod" class="text-gray-700 font-medium cursor-pointer">Status Produk Aktif</label>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
            <button
              type="button"
              @click="showModal = false"
              class="px-3 py-1.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="px-4 py-1.5 rounded-lg bg-black hover:bg-neutral-800 text-white font-semibold cursor-pointer disabled:opacity-50"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

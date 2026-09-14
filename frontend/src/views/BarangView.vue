<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import apiClient from '@/api/client'
import {
  Search,
  Plus,
  Edit2,
  Trash2,
  ChevronLeft,
  ChevronRight,
  X,
  RefreshCw
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
  id_kelompok_kategori?: number
  id_supplier?: number
  is_active: boolean
}

const activeTab = ref<'produk' | 'kategori' | 'kelompok'>('produk')
const listBarang = ref<Barang[]>([])
const listKategori = ref<any[]>([])
const listKelompok = ref<any[]>([])
const listSupplier = ref<any[]>([])
const searchQuery = ref('')
const selectedKategori = ref<number | null>(null)
const selectedStatus = ref<string>('semua')
const isLoading = ref(false)

// Modal Tambah Produk
const showAddModal = ref(false)
const isSaving = ref(false)
const form = ref({
  nama: '',
  barcode: '',
  id_kategori: 1,
  id_kelompok_kategori: 1,
  id_supplier: 1,
  satuan: 'pcs',
  harga_beli: 0,
  harga_jual: 0,
  stok: 10,
  is_active: true,
})

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(val)
}

const fetchData = async () => {
  isLoading.value = true
  try {
    const [resBarang, resKategori, resKelompok, resSupplier] = await Promise.allSettled([
      apiClient.get('/barang'),
      apiClient.get('/kategori'),
      apiClient.get('/kelompok-kategori'),
      apiClient.get('/supplier'),
    ])

    if (resBarang.status === 'fulfilled') {
      listBarang.value = Array.isArray(resBarang.value.data) ? resBarang.value.data : []
    }
    if (resKategori.status === 'fulfilled') {
      listKategori.value = Array.isArray(resKategori.value.data) ? resKategori.value.data : []
    }
    if (resKelompok.status === 'fulfilled') {
      listKelompok.value = Array.isArray(resKelompok.value.data) ? resKelompok.value.data : []
    }
    if (resSupplier.status === 'fulfilled') {
      listSupplier.value = Array.isArray(resSupplier.value.data) ? resSupplier.value.data : []
    }
  } catch (err) {
    console.error('Failed to fetch data:', err)
  } finally {
    isLoading.value = false
  }
}

const filteredBarang = computed(() => {
  return listBarang.value.filter((b) => {
    const q = searchQuery.value.toLowerCase()
    const matchQuery =
      !q ||
      b.nama.toLowerCase().includes(q) ||
      b.barcode.toLowerCase().includes(q)
    const matchKat =
      selectedKategori.value === null || b.id_kategori === selectedKategori.value
    const matchStatus =
      selectedStatus.value === 'semua' ||
      (selectedStatus.value === 'active' && b.is_active) ||
      (selectedStatus.value === 'nonaktif' && !b.is_active)

    return matchQuery && matchKat && matchStatus
  })
})

const getCategoryName = (id: number) => {
  const found = listKategori.value.find((k) => k.id_kategori === id)
  return found ? found.nama : 'ATK'
}

const handleSaveProduk = async () => {
  if (!form.value.nama || !form.value.barcode) {
    alert('Nama barang dan barcode wajib diisi')
    return
  }

  isSaving.value = true
  try {
    await apiClient.post('/barang', form.value)
    alert('Produk berhasil ditambahkan')
    showAddModal.value = false
    form.value = {
      nama: '',
      barcode: '',
      id_kategori: listKategori.value[0]?.id_kategori || 1,
      id_kelompok_kategori: listKelompok.value[0]?.id_kelompok || 1,
      id_supplier: listSupplier.value[0]?.id_supplier || 1,
      satuan: 'pcs',
      harga_beli: 0,
      harga_jual: 0,
      stok: 10,
      is_active: true,
    }
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan barang')
  } finally {
    isSaving.value = false
  }
}

const handleDelete = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus barang ini?')) {
    try {
      await apiClient.delete(`/barang/${id}`)
      await fetchData()
    } catch (err: any) {
      alert('Gagal menghapus barang')
    }
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="space-y-4">
    <!-- Top Tabs (Screen 4) -->
    <div class="border-b border-slate-200">
      <div class="flex gap-6">
        <button
          @click="activeTab = 'produk'"
          class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
          :class="activeTab === 'produk' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
        >
          Daftar Produk
        </button>
        <button
          @click="activeTab = 'kategori'"
          class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
          :class="activeTab === 'kategori' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
        >
          Kategori
        </button>
        <button
          @click="activeTab = 'kelompok'"
          class="pb-2.5 text-xs font-bold transition border-b-2 cursor-pointer"
          :class="activeTab === 'kelompok' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'"
        >
          Kelompok Kategori
        </button>
      </div>
    </div>

    <!-- Content Box -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-2xs space-y-4">
      <!-- Title & Tambah Produk Button (Screen 4) -->
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-bold text-slate-900">
          {{ activeTab === 'produk' ? 'Data Produk' : activeTab === 'kategori' ? 'Data Kategori' : 'Data Kelompok Kategori' }}
        </h2>

        <button
          @click="showAddModal = true"
          class="px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center gap-1.5 transition cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>Tambah Produk</span>
        </button>
      </div>

      <!-- Filters Row (Screen 4) -->
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Search -->
        <div class="relative flex-1 w-full">
          <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari produk..."
            class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-slate-800"
          />
        </div>

        <!-- Filter Kategori -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <span class="text-xs text-slate-400 whitespace-nowrap hidden md:inline">Filter Kategori</span>
          <select
            v-model="selectedKategori"
            class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 cursor-pointer"
          >
            <option :value="null">Semua</option>
            <option v-for="kat in listKategori" :key="kat.id_kategori" :value="kat.id_kategori">
              {{ kat.nama }}
            </option>
          </select>
        </div>

        <!-- Filter Status -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <span class="text-xs text-slate-400 whitespace-nowrap hidden md:inline">Status</span>
          <select
            v-model="selectedStatus"
            class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 cursor-pointer"
          >
            <option value="semua">Semua</option>
            <option value="active">Active</option>
            <option value="nonaktif">Nonaktif</option>
          </select>
        </div>
      </div>

      <!-- Table (Screen 4) -->
      <div class="border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-semibold border-b border-slate-200">
            <tr>
              <th class="py-2.5 px-3 w-10 text-center">No</th>
              <th class="py-2.5 px-3">Barcode</th>
              <th class="py-2.5 px-3">Nama Produk</th>
              <th class="py-2.5 px-3">Kategori</th>
              <th class="py-2.5 px-3 text-right">Harga Jual</th>
              <th class="py-2.5 px-3 text-center">Stok</th>
              <th class="py-2.5 px-3 text-center">Status</th>
              <th class="py-2.5 px-3 text-center w-20">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr v-if="isLoading">
              <td colspan="8" class="py-10 text-center text-slate-400">
                <RefreshCw class="w-4 h-4 animate-spin mx-auto mb-1 text-slate-300" />
                Memuat data...
              </td>
            </tr>
            <tr v-else-if="filteredBarang.length === 0">
              <td colspan="8" class="py-10 text-center text-slate-400">
                Tidak ada produk ditemukan.
              </td>
            </tr>
            <tr
              v-else
              v-for="(item, idx) in filteredBarang"
              :key="item.id_barang"
              class="hover:bg-slate-50/60 transition"
            >
              <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 font-mono text-slate-600">{{ item.barcode }}</td>
              <td class="py-2.5 px-3 font-semibold text-slate-900">{{ item.nama }}</td>
              <td class="py-2.5 px-3 text-slate-600">{{ getCategoryName(item.id_kategori) }}</td>
              <td class="py-2.5 px-3 text-right font-medium text-slate-800">{{ formatRupiah(item.harga_jual) }}</td>
              <td class="py-2.5 px-3 text-center font-bold" :class="item.stok <= 10 ? 'text-rose-600' : 'text-slate-800'">
                {{ item.stok }}
              </td>
              <td class="py-2.5 px-3 text-center">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold"
                  :class="item.is_active ? 'bg-slate-100 text-slate-800 border border-slate-200' : 'bg-rose-50 text-rose-600'"
                >
                  {{ item.is_active ? 'Active' : 'Nonaktif' }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button class="p-1 text-slate-400 hover:text-slate-800 cursor-pointer" title="Edit">
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="handleDelete(item.id_barang)"
                    class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer"
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

      <!-- Pagination Footer (Screen 4) -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 text-xs text-slate-500">
        <div class="flex items-center gap-1.5">
          <span>Tampilkan</span>
          <select class="px-2 py-0.5 bg-slate-50 border border-slate-200 rounded text-xs cursor-pointer">
            <option>10</option>
            <option>25</option>
            <option>50</option>
          </select>
          <span>data (Total: {{ filteredBarang.length }})</span>
        </div>

        <!-- Page Steppers matching mockup -->
        <div class="flex items-center gap-1">
          <button class="p-1 rounded border border-slate-200 text-slate-400 hover:text-slate-700 cursor-pointer">
            <ChevronLeft class="w-3.5 h-3.5" />
          </button>
          <button class="w-6 h-6 rounded bg-slate-900 text-white font-bold text-xs flex items-center justify-center">
            1
          </button>
          <button class="w-6 h-6 rounded border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs flex items-center justify-center">
            2
          </button>
          <button class="w-6 h-6 rounded border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs flex items-center justify-center">
            3
          </button>
          <button class="p-1 rounded border border-slate-200 text-slate-400 hover:text-slate-700 cursor-pointer">
            <ChevronRight class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL TAMBAH PRODUK -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-bold text-sm text-slate-900">Tambah Produk Baru</h3>
          <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSaveProduk" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-slate-600 mb-1">Nama Barang</label>
            <input
              v-model="form.nama"
              type="text"
              required
              class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 mb-1">Barcode</label>
              <input
                v-model="form.barcode"
                type="text"
                required
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-mono"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-1">Satuan</label>
              <input
                v-model="form.satuan"
                type="text"
                required
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 mb-1">Harga Beli</label>
              <input
                v-model.number="form.harga_beli"
                type="number"
                min="0"
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-1">Harga Jual</label>
              <input
                v-model.number="form.harga_jual"
                type="number"
                min="0"
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-bold"
              />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-600 mb-1">Kategori</label>
              <select
                v-model="form.id_kategori"
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
              >
                <option v-for="k in listKategori" :key="k.id_kategori" :value="k.id_kategori">
                  {{ k.nama }}
                </option>
              </select>
            </div>
            <div>
              <label class="block font-semibold text-slate-600 mb-1">Stok Awal</label>
              <input
                v-model.number="form.stok"
                type="number"
                min="0"
                class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
              />
            </div>
          </div>

          <div class="pt-3 border-t border-slate-100 flex items-center gap-2 justify-end">
            <button
              type="button"
              @click="showAddModal = false"
              class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-xs hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs cursor-pointer"
            >
              {{ isSaving ? 'Menyimpan...' : 'Simpan Produk' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

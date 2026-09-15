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

interface UserItem {
  id_user: number
  id_sekolah: number
  id_role: number
  nama_lengkap: string
  username: string
  role?: string
  is_active: boolean
}

const loading = ref(false)
const userList = ref<UserItem[]>([
  { id_user: 1, id_sekolah: 1, id_role: 1, nama_lengkap: 'Admin Sekolah', username: 'admin', role: 'super admin', is_active: true },
  { id_user: 2, id_sekolah: 1, id_role: 3, nama_lengkap: 'Budi Santoso', username: 'budi', role: 'kasir', is_active: true },
  { id_user: 3, id_sekolah: 1, id_role: 3, nama_lengkap: 'Siti Aminah', username: 'siti', role: 'kasir', is_active: true },
  { id_user: 4, id_sekolah: 1, id_role: 2, nama_lengkap: 'Andi Wijaya', username: 'andi', role: 'admin', is_active: false },
  { id_user: 5, id_sekolah: 1, id_role: 3, nama_lengkap: 'Rina Dewi', username: 'rina', role: 'kasir', is_active: true }
])

// Filters
const search = ref('')
const roleFilter = ref('Semua')

// Pagination
const itemsPerPage = ref(10)
const currentPage = ref(1)

// Modal
const showModal = ref(false)
const isEditing = ref(false)
const isSubmitting = ref(false)
const form = ref({
  id_user: null as number | null,
  nama_lengkap: '',
  username: '',
  password: '',
  id_role: 3,
  is_active: true
})

const getRoleName = (item: any) => {
  if (item.role) return item.role
  if (item.id_role === 1) return 'super admin'
  if (item.id_role === 2) return 'admin'
  return 'kasir'
}

const fetchData = async () => {
  loading.value = true
  try {
    const res = await apiClient.get('/users')
    if (Array.isArray(res.data) && res.data.length > 0) {
      userList.value = res.data.map((u: any) => ({
        ...u,
        role: getRoleName(u)
      }))
    }
  } catch (err) {
    console.error('Error fetching users:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Filtered List
const filteredList = computed(() => {
  return userList.value.filter((u) => {
    const matchSearch =
      !search.value ||
      u.nama_lengkap.toLowerCase().includes(search.value.toLowerCase()) ||
      u.username.toLowerCase().includes(search.value.toLowerCase())

    const rName = getRoleName(u).toLowerCase()
    const matchRole =
      roleFilter.value === 'Semua' ||
      rName === roleFilter.value.toLowerCase()

    return matchSearch && matchRole
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
    id_user: null,
    nama_lengkap: '',
    username: '',
    password: '',
    id_role: 3,
    is_active: true
  }
  showModal.value = true
}

// Open Edit Modal
const openEditModal = (u: UserItem) => {
  isEditing.value = true
  form.value = {
    id_user: u.id_user,
    nama_lengkap: u.nama_lengkap,
    username: u.username,
    password: '',
    id_role: u.id_role || 3,
    is_active: u.is_active
  }
  showModal.value = true
}

// Save User
const handleSave = async () => {
  if (!form.value.nama_lengkap || !form.value.username) {
    alert('Nama lengkap dan username wajib diisi!')
    return
  }

  isSubmitting.value = true
  try {
    if (isEditing.value && form.value.id_user) {
      await apiClient.put(`/users/${form.value.id_user}`, form.value)
    } else {
      await apiClient.post('/users', form.value)
    }
    showModal.value = false
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan user!')
  } finally {
    isSubmitting.value = false
  }
}

// Delete User
const handleDelete = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return
  try {
    await apiClient.delete(`/users/${id}`)
    await fetchData()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus user!')
  }
}
</script>

<template>
  <div class="p-6 space-y-4 max-w-7xl mx-auto">
    <!-- Header: Title & + Tambah User Button -->
    <div class="flex items-center justify-between">
      <h1 class="text-base font-bold text-gray-900">Data User</h1>
      <button
        @click="openAddModal"
        class="flex items-center gap-1.5 px-3 py-2 bg-[#23272f] hover:bg-black text-white text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer"
      >
        <Plus class="w-3.5 h-3.5" />
        <span>Tambah User</span>
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
          placeholder="Cari user..."
          class="w-full pl-9 pr-3 py-2 bg-white rounded-lg border border-gray-200 text-xs outline-hidden"
        />
      </div>

      <!-- Role Dropdown -->
      <div class="w-full sm:w-48 flex items-center gap-2">
        <label class="text-xs text-gray-600 font-medium shrink-0">Role</label>
        <select
          v-model="roleFilter"
          class="flex-1 px-3 py-2 bg-white rounded-lg border border-gray-200 text-xs text-gray-700 outline-hidden cursor-pointer"
        >
          <option value="Semua">Semua</option>
          <option value="super admin">Super Admin</option>
          <option value="admin">Admin</option>
          <option value="kasir">Kasir</option>
        </select>
      </div>
    </div>

    <!-- Table User -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-2xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold text-[11px]">
              <th class="py-2.5 px-3 w-10 text-center">No</th>
              <th class="py-2.5 px-3">Nama</th>
              <th class="py-2.5 px-3">Username</th>
              <th class="py-2.5 px-3">Role</th>
              <th class="py-2.5 px-3 text-center">Status</th>
              <th class="py-2.5 px-3 text-center w-24">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-800">
            <tr v-if="loading" class="text-center">
              <td colspan="6" class="py-12 text-gray-400">
                <Loader2 class="w-5 h-5 animate-spin mx-auto text-gray-400 mb-1" />
                <span>Memuat data user...</span>
              </td>
            </tr>
            <tr v-else-if="paginatedList.length === 0" class="text-center">
              <td colspan="6" class="py-12 text-gray-400">
                Tidak ada user ditemukan
              </td>
            </tr>
            <tr
              v-for="(u, idx) in paginatedList"
              :key="u.id_user"
              class="hover:bg-gray-50/60 transition"
            >
              <td class="py-3 px-3 text-center text-gray-500 font-medium">
                {{ (currentPage - 1) * itemsPerPage + idx + 1 }}
              </td>
              <td class="py-3 px-3 font-semibold text-gray-900">
                {{ u.nama_lengkap }}
              </td>
              <td class="py-3 px-3 text-gray-600">
                {{ u.username }}
              </td>
              <td class="py-3 px-3 text-gray-700 capitalize">
                {{ getRoleName(u) }}
              </td>
              <td class="py-3 px-3 text-center">
                <span
                  class="px-2.5 py-0.5 rounded-md text-[10px] font-medium border"
                  :class="
                    u.is_active
                      ? 'border-gray-300 text-gray-800 bg-white'
                      : 'border-gray-200 text-gray-400 bg-gray-50'
                  "
                >
                  {{ u.is_active ? 'Active' : 'Nonaktif' }}
                </span>
              </td>
              <td class="py-3 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    @click="openEditModal(u)"
                    class="p-1 text-gray-500 hover:text-gray-900 cursor-pointer"
                    title="Edit User"
                  >
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="handleDelete(u.id_user)"
                    class="p-1 text-gray-500 hover:text-rose-600 cursor-pointer"
                    title="Hapus User"
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

    <!-- MODAL: TAMBAH / EDIT USER -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
    >
      <div class="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2">
          <h3 class="text-sm font-bold text-gray-900">
            {{ isEditing ? 'Edit Data User' : 'Tambah User Baru' }}
          </h3>
          <button @click="showModal = false" class="text-gray-400 hover:text-gray-700 p-1">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSave" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <input
              v-model="form.nama_lengkap"
              type="text"
              required
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Username</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">
              {{ isEditing ? 'Password Baru (Opsional)' : 'Password' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!isEditing"
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden"
            />
          </div>

          <div>
            <label class="block font-semibold text-gray-700 mb-1">Role Pengguna</label>
            <select
              v-model="form.id_role"
              class="w-full px-3 py-1.5 rounded-lg border border-gray-200 outline-hidden bg-white cursor-pointer"
            >
              <option :value="1">Super Admin</option>
              <option :value="2">Admin</option>
              <option :value="3">Kasir</option>
            </select>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input
              id="is_active_user"
              v-model="form.is_active"
              type="checkbox"
              class="rounded border-gray-300 text-black"
            />
            <label for="is_active_user" class="text-gray-700 font-medium cursor-pointer">Status Akun Aktif</label>
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

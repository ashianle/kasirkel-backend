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
  RefreshCw,
  X
} from 'lucide-vue-next'

interface UserItem {
  id_user: number
  username: string
  nama_lengkap: string
  id_role: number
  is_active: boolean
}

const listUsers = ref<UserItem[]>([])
const searchQuery = ref('')
const selectedRole = ref<string>('semua')
const isLoading = ref(false)
const showAddModal = ref(false)
const isSaving = ref(false)

const form = ref({
  nama_lengkap: '',
  username: '',
  password: '',
  id_role: 3,
})

const fetchUsers = async () => {
  isLoading.value = true
  try {
    const res = await apiClient.get('/users')
    listUsers.value = Array.isArray(res.data) ? res.data : []
  } catch (err) {
    console.error('Failed to fetch users:', err)
  } finally {
    isLoading.value = false
  }
}

const getRoleName = (roleId: number) => {
  if (roleId === 1) return 'super admin'
  if (roleId === 2) return 'admin'
  return 'kasir'
}

const filteredUsers = computed(() => {
  return listUsers.value.filter((u) => {
    const q = searchQuery.value.toLowerCase()
    const matchQuery =
      !q ||
      u.username.toLowerCase().includes(q) ||
      (u.nama_lengkap && u.nama_lengkap.toLowerCase().includes(q))
    const roleName = getRoleName(u.id_role)
    const matchRole =
      selectedRole.value === 'semua' || roleName === selectedRole.value

    return matchQuery && matchRole
  })
})

const handleSaveUser = async () => {
  if (!form.value.nama_lengkap || !form.value.username || !form.value.password) {
    alert('Nama, username, dan password wajib diisi')
    return
  }

  isSaving.value = true
  try {
    await apiClient.post('/users', form.value)
    alert('User berhasil ditambahkan')
    showAddModal.value = false
    form.value = {
      nama_lengkap: '',
      username: '',
      password: '',
      id_role: 3,
    }
    await fetchUsers()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan user')
  } finally {
    isSaving.value = false
  }
}

const handleDeleteUser = async (id: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
    try {
      await apiClient.delete(`/users/${id}`)
      await fetchUsers()
    } catch (err) {
      alert('Gagal menghapus user')
    }
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<template>
  <div class="space-y-4">
    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-2xs space-y-4">
      <!-- Header with Tambah User (Screen 5) -->
      <div class="flex items-center justify-between">
        <h1 class="text-sm font-bold text-slate-900">Data User</h1>

        <button
          @click="showAddModal = true"
          class="px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs flex items-center gap-1.5 transition cursor-pointer"
        >
          <Plus class="w-3.5 h-3.5" />
          <span>Tambah User</span>
        </button>
      </div>

      <!-- Filters Row (Screen 5) -->
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Search -->
        <div class="relative flex-1 w-full">
          <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari user..."
            class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg text-slate-800 focus:outline-none focus:border-slate-800"
          />
        </div>

        <!-- Filter Role -->
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <span class="text-xs text-slate-400 whitespace-nowrap">Role</span>
          <select
            v-model="selectedRole"
            class="px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-700 cursor-pointer"
          >
            <option value="semua">Semua</option>
            <option value="super admin">super admin</option>
            <option value="admin">admin</option>
            <option value="kasir">kasir</option>
          </select>
        </div>
      </div>

      <!-- Table (Screen 5) -->
      <div class="border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-semibold border-b border-slate-200">
            <tr>
              <th class="py-2.5 px-3 w-10 text-center">No</th>
              <th class="py-2.5 px-3">Nama</th>
              <th class="py-2.5 px-3">Username</th>
              <th class="py-2.5 px-3">Role</th>
              <th class="py-2.5 px-3 text-center">Status</th>
              <th class="py-2.5 px-3 text-center w-20">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <tr v-if="isLoading">
              <td colspan="6" class="py-8 text-center text-slate-400">
                <RefreshCw class="w-4 h-4 animate-spin mx-auto mb-1 text-slate-300" />
                Memuat data user...
              </td>
            </tr>
            <tr v-else-if="filteredUsers.length === 0">
              <td colspan="6" class="py-8 text-center text-slate-400">
                Belum ada data user.
              </td>
            </tr>
            <tr
              v-else
              v-for="(u, idx) in filteredUsers"
              :key="u.id_user"
              class="hover:bg-slate-50/60 transition"
            >
              <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ idx + 1 }}</td>
              <td class="py-2.5 px-3 font-semibold text-slate-900">{{ u.nama_lengkap || '-' }}</td>
              <td class="py-2.5 px-3 text-slate-600 font-mono">{{ u.username }}</td>
              <td class="py-2.5 px-3 capitalize">
                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                  {{ getRoleName(u.id_role) }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-center">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold"
                  :class="u.is_active ? 'bg-slate-100 text-slate-800 border border-slate-200' : 'bg-rose-50 text-rose-600'"
                >
                  {{ u.is_active ? 'Active' : 'Nonaktif' }}
                </span>
              </td>
              <td class="py-2.5 px-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button class="p-1 text-slate-400 hover:text-slate-800 cursor-pointer" title="Edit">
                    <Edit2 class="w-3.5 h-3.5" />
                  </button>
                  <button
                    @click="handleDeleteUser(u.id_user)"
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

      <!-- Pagination Footer (Screen 5) -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-2 pt-2 text-xs text-slate-500">
        <div class="flex items-center gap-1.5">
          <span>Tampilkan</span>
          <select class="px-2 py-0.5 bg-slate-50 border border-slate-200 rounded text-xs cursor-pointer">
            <option>10</option>
            <option>25</option>
          </select>
          <span>data</span>
        </div>

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
          <button class="p-1 rounded border border-slate-200 text-slate-400 hover:text-slate-700 cursor-pointer">
            <ChevronRight class="w-3.5 h-3.5" />
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL TAMBAH USER -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-2xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="font-bold text-sm text-slate-900">Tambah User Baru</h3>
          <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="handleSaveUser" class="space-y-3 text-xs">
          <div>
            <label class="block font-semibold text-slate-600 mb-1">Nama Lengkap</label>
            <input
              v-model="form.nama_lengkap"
              type="text"
              required
              class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-600 mb-1">Username</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-mono"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-600 mb-1">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
            />
          </div>

          <div>
            <label class="block font-semibold text-slate-600 mb-1">Role</label>
            <select
              v-model="form.id_role"
              class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-900"
            >
              <option :value="1">Super Admin</option>
              <option :value="2">Admin</option>
              <option :value="3">Kasir</option>
            </select>
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
              {{ isSaving ? 'Menyimpan...' : 'Simpan User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

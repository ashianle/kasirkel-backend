<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Menu,
  ChevronDown,
  Bell,
  User,
  LogOut
} from 'lucide-vue-next'

const emit = defineEmits(['toggle-sidebar'])
const router = useRouter()
const authStore = useAuthStore()

const showUserMenu = ref(false)
const showSchoolMenu = ref(false)
const selectedSchool = ref('SMA Negeri 1')

const schools = ['SMA Negeri 1', 'SMKN 2 Tasikmalaya', 'SMKN 1 Tasikmalaya']

const selectSchool = (name: string) => {
  selectedSchool.value = name
  showSchoolMenu.value = false
}

const handleLogout = async () => {
  if (confirm('Apakah Anda yakin ingin logout?')) {
    await authStore.logout()
    router.push('/login')
  }
}
</script>

<template>
  <header class="h-14 bg-white border-b border-gray-200 px-6 flex items-center justify-between sticky top-0 z-30 select-none">
    <!-- Left: Hamburger & School Dropdown -->
    <div class="flex items-center gap-4">
      <button
        @click="emit('toggle-sidebar')"
        class="p-1.5 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 cursor-pointer"
        title="Toggle Menu"
      >
        <Menu class="w-4 h-4" />
      </button>

      <!-- School / Tenant Selector -->
      <div class="relative">
        <button
          @click="showSchoolMenu = !showSchoolMenu"
          class="flex items-center gap-1.5 text-xs font-semibold text-gray-800 hover:text-gray-900 cursor-pointer"
        >
          <span>{{ selectedSchool }}</span>
          <ChevronDown class="w-3.5 h-3.5 text-gray-500" />
        </button>

        <div
          v-if="showSchoolMenu"
          class="absolute left-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-md py-1 z-50 text-xs font-medium"
        >
          <button
            v-for="s in schools"
            :key="s"
            @click="selectSchool(s)"
            class="w-full px-3 py-1.5 text-left text-gray-700 hover:bg-gray-50 hover:text-gray-900 cursor-pointer"
          >
            {{ s }}
          </button>
        </div>
      </div>
    </div>

    <!-- Right: Notification & Profile Dropdown -->
    <div class="flex items-center gap-4">
      <!-- Notification -->
      <button
        class="p-1.5 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition relative cursor-pointer"
        title="Notifikasi"
      >
        <Bell class="w-4 h-4" />
      </button>

      <!-- User Profile -->
      <div class="relative">
        <button
          @click="showUserMenu = !showUserMenu"
          class="flex items-center gap-2 text-xs font-semibold text-gray-800 hover:text-gray-900 cursor-pointer"
        >
          <div class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center font-bold text-xs">
            <User class="w-3.5 h-3.5 text-gray-600" />
          </div>
          <span>{{ authStore.user?.username ? (authStore.user.username.charAt(0).toUpperCase() + authStore.user.username.slice(1)) : 'Admin' }}</span>
          <ChevronDown class="w-3.5 h-3.5 text-gray-500" />
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="showUserMenu"
          class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-md py-1.5 z-50 text-xs font-medium"
        >
          <div class="px-3 py-1.5 border-b border-gray-100">
            <div class="font-bold text-gray-900">{{ authStore.user?.nama_lengkap || authStore.user?.username || 'Admin' }}</div>
            <div class="text-[10px] text-gray-400 capitalize">{{ authStore.user?.role || 'Administrator' }}</div>
          </div>
          <button
            @click="handleLogout"
            class="w-full px-3 py-2 text-left text-rose-600 hover:bg-rose-50 flex items-center gap-2 cursor-pointer transition"
          >
            <LogOut class="w-3.5 h-3.5" />
            <span>Keluar (Logout)</span>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

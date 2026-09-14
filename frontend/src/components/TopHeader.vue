<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  Menu,
  ChevronDown,
  Bell,
  User,
  LogOut,
  Building
} from 'lucide-vue-next'

const emit = defineEmits(['toggle-sidebar'])
const router = useRouter()
const authStore = useAuthStore()

const showUserMenu = ref(false)
const selectedSchool = ref('SMA Negeri 1')

const handleLogout = async () => {
  if (confirm('Apakah Anda yakin ingin logout?')) {
    await authStore.logout()
    router.push('/login')
  }
}
</script>

<template>
  <header class="h-16 bg-white border-b border-slate-200/90 px-6 flex items-center justify-between sticky top-0 z-30 select-none">
    <!-- Left: Hamburger & School Dropdown -->
    <div class="flex items-center gap-4">
      <button
        @click="emit('toggle-sidebar')"
        class="p-2 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 lg:hidden cursor-pointer"
        title="Toggle Menu"
      >
        <Menu class="w-5 h-5" />
      </button>

      <!-- School / Tenant Selector -->
      <div class="relative">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50/50 text-xs font-semibold text-slate-700 hover:border-slate-300 transition">
          <Building class="w-3.5 h-3.5 text-slate-500" />
          <span>{{ selectedSchool }}</span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
        </div>
      </div>
    </div>

    <!-- Right: Notification & Profile Dropdown -->
    <div class="flex items-center gap-3">
      <!-- Notification -->
      <button
        class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition relative cursor-pointer"
        title="Notifikasi"
      >
        <Bell class="w-4 h-4" />
        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 absolute top-2 right-2"></span>
      </button>

      <!-- User Profile -->
      <div class="relative">
        <button
          @click="showUserMenu = !showUserMenu"
          class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 transition text-xs font-semibold text-slate-800 cursor-pointer"
        >
          <div class="w-7 h-7 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-xs">
            <User class="w-4 h-4" />
          </div>
          <span>{{ authStore.user?.username || 'Admin' }}</span>
          <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="showUserMenu"
          class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-lg py-1.5 z-50 text-xs font-medium animate-in fade-in zoom-in-95 duration-100"
        >
          <div class="px-3 py-2 border-b border-slate-100">
            <div class="font-bold text-slate-800">{{ authStore.user?.nama_lengkap || authStore.user?.username }}</div>
            <div class="text-[10px] text-slate-400">{{ authStore.user?.email || 'User Kasir' }}</div>
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

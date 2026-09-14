<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { 
  Store, 
  LayoutDashboard, 
  Package, 
  ShoppingCart, 
  LogOut, 
  User as UserIcon 
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <header class="bg-slate-900 border-b border-slate-800 text-white sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Logo & Title -->
      <div class="flex items-center gap-8">
        <router-link to="/" class="flex items-center gap-2 font-bold text-xl text-emerald-400 hover:text-emerald-300">
          <Store class="w-6 h-6" />
          <span>KasirKel</span>
        </router-link>

        <!-- Nav Links -->
        <nav class="hidden md:flex items-center gap-1">
          <router-link
            to="/"
            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
            :class="$route.path === '/' ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
          >
            <LayoutDashboard class="w-4 h-4" />
            Dashboard
          </router-link>

          <router-link
            to="/barang"
            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
            :class="$route.path === '/barang' ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
          >
            <Package class="w-4 h-4" />
            Data Barang
          </router-link>

          <router-link
            to="/penjualan"
            class="px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
            :class="$route.path === '/penjualan' ? 'bg-slate-800 text-emerald-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white'"
          >
            <ShoppingCart class="w-4 h-4" />
            Transaksi Penjualan
          </router-link>
        </nav>
      </div>

      <!-- User & Logout -->
      <div class="flex items-center gap-4">
        <div class="hidden sm:flex items-center gap-2 text-sm text-slate-300">
          <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-emerald-400">
            <UserIcon class="w-4 h-4" />
          </div>
          <div>
            <div class="font-medium text-white">{{ authStore.user?.nama || authStore.user?.username || 'Kasir' }}</div>
            <div class="text-xs text-slate-400">Sekolah ID: {{ authStore.user?.id_sekolah || '-' }}</div>
          </div>
        </div>

        <button
          @click="handleLogout"
          class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 border border-rose-900/50 transition-colors"
          title="Keluar"
        >
          <LogOut class="w-4 h-4" />
          <span class="hidden sm:inline">Logout</span>
        </button>
      </div>
    </div>
  </header>
</template>

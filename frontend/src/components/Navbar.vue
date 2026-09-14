<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { 
  Store, 
  LayoutDashboard, 
  Package, 
  ShoppingCart, 
  Receipt,
  LogOut, 
  User as UserIcon
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
    await authStore.logout()
    router.push('/login')
  }
}
</script>

<template>
  <header class="bg-white border-b border-slate-200/80 sticky top-0 z-40 shadow-2xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      <!-- Brand & Navigation -->
      <div class="flex items-center gap-8">
        <!-- Logo -->
        <router-link to="/pos" class="flex items-center gap-2.5 font-bold text-slate-900 group">
          <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-xs group-hover:bg-indigo-700 transition">
            <Store class="w-5 h-5" />
          </div>
          <div class="leading-none">
            <span class="text-base tracking-tight font-black text-slate-900">KasirKel</span>
            <div class="text-[10px] font-semibold text-indigo-600 tracking-wider uppercase mt-0.5">Point of Sale</div>
          </div>
        </router-link>

        <!-- Navigation Tabs -->
        <nav class="hidden md:flex items-center gap-1">
          <router-link
            to="/pos"
            class="px-3 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5"
            :class="$route.path === '/pos' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            <ShoppingCart class="w-4 h-4" />
            <span>Kasir (POS)</span>
          </router-link>

          <router-link
            to="/"
            class="px-3 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5"
            :class="$route.path === '/' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            <LayoutDashboard class="w-4 h-4" />
            <span>Dashboard</span>
          </router-link>

          <router-link
            to="/barang"
            class="px-3 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5"
            :class="$route.path === '/barang' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            <Package class="w-4 h-4" />
            <span>Data Barang</span>
          </router-link>

          <router-link
            to="/penjualan"
            class="px-3 py-2 rounded-xl text-xs font-semibold transition-all flex items-center gap-1.5"
            :class="$route.path === '/penjualan' ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
          >
            <Receipt class="w-4 h-4" />
            <span>Riwayat Transaksi</span>
          </router-link>
        </nav>
      </div>

      <!-- Right Header Elements -->
      <div class="flex items-center gap-3">
        <!-- DB Badge -->
        <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60 text-[11px] font-semibold">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>MySQL Ready</span>
        </div>

        <!-- Cashier Profile -->
        <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
          <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
            <UserIcon class="w-4 h-4" />
          </div>
          <div class="hidden sm:block text-left">
            <div class="text-xs font-bold text-slate-800 leading-tight">
              {{ authStore.user?.nama_lengkap || authStore.user?.username || 'Kasir' }}
            </div>
            <div class="text-[10px] text-slate-400 font-medium">
              ID User: #{{ authStore.user?.id_user || 1 }}
            </div>
          </div>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="ml-1 p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
            title="Keluar / Logout"
          >
            <LogOut class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

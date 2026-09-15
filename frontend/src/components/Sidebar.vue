<script setup lang="ts">
import {
  Store,
  LayoutDashboard,
  ShoppingCart,
  ShoppingBag,
  Package,
  Users,
  Truck,
  UserCog,
  FileText,
  Settings
} from 'lucide-vue-next'

const menuItems = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'Kasir / Transaksi', path: '/pos', icon: ShoppingCart },
  { name: 'Pembelian', path: '/pembelian', icon: ShoppingBag },
  { name: 'Produk', path: '/barang', icon: Package },
  { name: 'Pelanggan', path: '/pelanggan', icon: Users },
  { name: 'Supplier', path: '/supplier', icon: Truck },
  { name: 'User', path: '/user', icon: UserCog },
  { name: 'Laporan', path: '/penjualan', icon: FileText },
  { name: 'Pengaturan', path: '/pengaturan', icon: Settings },
]
</script>

<template>
  <aside class="w-60 bg-white text-gray-700 flex flex-col shrink-0 min-h-screen border-r border-gray-200 select-none">
    <!-- Brand Header -->
    <div class="h-14 px-5 flex items-center gap-2.5 border-b border-gray-100">
      <Store class="w-5 h-5 text-gray-900" />
      <span class="text-sm font-bold text-gray-900 tracking-wide uppercase">POS SEKOLAH</span>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in menuItems"
        :key="item.path"
        :to="item.path"
        class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-medium transition-colors"
        :class="
          $route.path === item.path
            ? 'bg-[#23272f] text-white shadow-xs font-semibold'
            : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100/80'
        "
      >
        <component
          :is="item.icon"
          class="w-4 h-4 shrink-0"
          :class="$route.path === item.path ? 'text-white' : 'text-gray-600'"
        />
        <span>{{ item.name }}</span>
      </router-link>
    </nav>

    <!-- Footer Copyright -->
    <div class="p-3.5 border-t border-gray-100 text-[11px] text-gray-400 text-center">
      © 2024 POS Sekolah
    </div>
  </aside>
</template>

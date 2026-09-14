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
  <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 min-h-screen border-r border-slate-800 select-none">
    <!-- Brand Header -->
    <div class="h-16 px-6 flex items-center gap-3 border-b border-slate-800">
      <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
        <Store class="w-5 h-5" />
      </div>
      <div>
        <div class="text-sm font-bold text-white tracking-wider uppercase">POS SEKOLAH</div>
        <div class="text-[10px] text-slate-400 font-medium">Sistem Kasir Multi Tenant</div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
      <router-link
        v-for="item in menuItems"
        :key="item.path"
        :to="item.path"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group"
        :class="
          $route.path === item.path
            ? 'bg-slate-800 text-white shadow-xs font-bold border-l-2 border-indigo-500'
            : 'text-slate-400 hover:text-slate-100 hover:bg-slate-800/60'
        "
      >
        <component
          :is="item.icon"
          class="w-4 h-4 transition-colors"
          :class="$route.path === item.path ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'"
        />
        <span>{{ item.name }}</span>
      </router-link>
    </nav>

    <!-- Footer Info -->
    <div class="p-4 border-t border-slate-800 text-[11px] text-slate-500 text-center">
      v2.0 • Multi Tenant POS
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import Sidebar from '@/components/Sidebar.vue'
import TopHeader from '@/components/TopHeader.vue'

const route = useRoute()
const sidebarOpen = ref(false)

const isAuthPage = computed(() => {
  return route.path === '/login' || route.path === '/register'
})

// Auto close mobile sidebar when route changes
watch(
  () => route.path,
  () => {
    sidebarOpen.value = false
  }
)
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased selection:bg-indigo-500 selection:text-white">
    <!-- Auth Pages (Login & Register) -->
    <div v-if="isAuthPage" class="min-h-screen">
      <router-view />
    </div>

    <!-- Authenticated App Layout -->
    <div v-else class="flex h-screen overflow-hidden">
      <!-- Desktop Sidebar -->
      <div class="hidden lg:flex shrink-0">
        <Sidebar />
      </div>

      <!-- Mobile Sidebar Overlay & Drawer -->
      <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden flex">
        <div
          class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
          @click="sidebarOpen = false"
        ></div>
        <div class="relative z-50 flex">
          <Sidebar />
        </div>
      </div>

      <!-- Main Layout: Top Header + Content -->
      <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        <TopHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />
        <main class="flex-1 overflow-y-auto bg-slate-50">
          <router-view />
        </main>
      </div>
    </div>
  </div>
</template>

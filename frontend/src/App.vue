<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Navbar from '@/components/Navbar.vue'

const route = useRoute()
const authStore = useAuthStore()

const showNavbar = computed(() => {
  return authStore.isAuthenticated && route.path !== '/login'
})
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 flex flex-col font-sans selection:bg-indigo-500 selection:text-white">
    <!-- Top Navigation -->
    <Navbar v-if="showNavbar" />

    <!-- Main View -->
    <main
      class="flex-1 w-full"
      :class="showNavbar ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6' : ''"
    >
      <router-view />
    </main>
  </div>
</template>

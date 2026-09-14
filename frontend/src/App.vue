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
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans">
    <Navbar v-if="showNavbar" />

    <main class="flex-1" :class="{ 'p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full': showNavbar }">
      <router-view />
    </main>
  </div>
</template>

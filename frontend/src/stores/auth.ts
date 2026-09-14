import { defineStore } from 'pinia'
import apiClient from '@/api/client'

export interface User {
  id_user: number
  username: string
  nama?: string
  nama_lengkap?: string
  email?: string
  role?: string
  id_sekolah: number
}

interface AuthState {
  token: string | null
  user: User | null
  isLoading: boolean
  error: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    token: localStorage.getItem('auth_token'),
    user: localStorage.getItem('auth_user')
      ? JSON.parse(localStorage.getItem('auth_user') as string)
      : null,
    isLoading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    async login(credentials: { username: string; password: string }) {
      this.isLoading = true
      this.error = null
      try {
        const response = await apiClient.post('/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        if (this.token) localStorage.setItem('auth_token', this.token)
        if (this.user) localStorage.setItem('auth_user', JSON.stringify(this.user))
        return response.data
      } catch (err: any) {
        this.error = err.response?.data?.message || 'Login gagal'
        throw err
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await apiClient.post('/logout')
        }
      } catch {
        // Abaikan error saat logout
      } finally {
        this.token = null
        this.user = null
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
    },
  },
})

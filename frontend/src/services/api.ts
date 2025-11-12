import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig, AxiosResponse } from 'axios'
import { useAppStore } from '@/stores/app'

// Create axios instance with default config
const api: AxiosInstance = axios.create({
  baseURL: process.env.VUE_APP_API_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const appStore = useAppStore()
    appStore.setLoading(true)

    // Get token from localStorage
    const token = localStorage.getItem('token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error: AxiosError) => {
    const appStore = useAppStore()
    appStore.setLoading(false)
    console.error('Request error:', error)
    appStore.setError('Request failed')
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response: AxiosResponse) => {
    const appStore = useAppStore()
    appStore.setLoading(false)
    return response
  },
  (error: AxiosError) => {
    const appStore = useAppStore()
    appStore.setLoading(false)
    console.error('Response error:', error)

    if (error.response) {
      console.error('Error response:', error.response.data)
      switch (error.response.status) {
        case 401:
          // Handle unauthorized
          appStore.setError('Unauthorized access')
          // Redirect to login if needed
          break
        case 403:
          appStore.setError('Access forbidden')
          break
        case 404:
          appStore.setError('Resource not found')
          break
        case 500:
          appStore.setError('Server error')
          break
        default:
          appStore.setError('An error occurred')
      }
    } else if (error.request) {
      console.error('No response received:', error.request)
      appStore.setError('No response from server')
    } else {
      console.error('Request setup error:', error.message)
      appStore.setError('Request failed')
    }

    return Promise.reject(error)
  }
)

export const sampleApi = {
  getMessage: async () => {
    try {
      const response = await api.get('/sample')
      return response.data
    } catch (error) {
      console.error('Sample API error:', error)
      throw error
    }
  }
}

export const authApi = {
  forgotPassword(email: string) {
    return api.post('/forgot-password', { email })
  },
  resetPassword(payload: { token: string, email: string, password: string, password_confirmation: string }) {
    return api.post('/reset-password', payload)
  },
}


export const userService = {
    getUsers(params?: { search?: string; role?: string | number; status?: string; page?: number }) {
        return api.get('/users', { params });
    },

    getUserStats() {
        return api.get('/users/stats');
    },

    getManufacturers() {
        return api.get('/management/manufacturers');
    },

    getClinics() {
        return api.get('/management/users/clinics');
    },

    getUser(id: string) {
        return api.get(`/users/${id}`);
    },

    createUser(userData: any) {
        return api.post('/users', userData);
    },

    updateUser(id: string, userData: any) {
        return api.put(`/users/${id}`, userData);
    },

    toggleUserStatus(id: string) {
        return api.patch(`/users/${id}/toggle-status`);
    },

    archiveUser(id: string) {
        return api.patch(`/users/${id}/archive`);
    },

    softDeleteUser(id: string) {
        return api.delete(`/users/${id}/soft-delete`);
    },

    restoreUser(id: string) {
        return api.patch(`/users/${id}/restore`);
    },
};

export default api 
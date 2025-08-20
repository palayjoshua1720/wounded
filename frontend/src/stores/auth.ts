import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import router from '@/router'

interface User {
	id: number
	email: string
	name: string
	user_role?: number
	first_name: string
	middle_name: string
	last_name: string
	full_name: string
}

interface LoginCredentials {
	email: string
	password: string
}

interface AuthResponse {
	token: string
	user: User
}

interface ApiError {
	message: string
	status: number
}

export const useAuthStore = defineStore('auth', () => {
	// State
	const user = ref<User | null>(null)
	const token = ref<string | null>(localStorage.getItem('token'))
	const loading = ref(false)
	const error = ref<string | null>(null)
	const userTemp = ref<string | null>(null)
	const tokenTemp = ref<string | null>(null)

	// Getters
	const isAuthenticated = computed(() => !!token.value)
	const currentUser = computed(() => user.value)
	const isLoading = computed(() => loading.value)
	const hasError = computed(() => !!error.value)

  	// Actions
	async function login(credentials: LoginCredentials) {
		loading.value = true
		error.value = null

		try {
			const response = await api.post<AuthResponse>('/auth/login', credentials)
			const { token: newToken, user: userData } = response.data
			
			token.value = newToken
			user.value = userData
			
			localStorage.setItem('token', newToken)
			
			// Redirect based on user role
			const role = Number(userData.user_role || localStorage.getItem('mock-role'));
			if (role === 2) {
				router.push('/clinic-dashboard');
			} else if (role === 0 || role === 1) {
				router.push('/admin-dashboard');
			} else {
				router.push('/'); // default/fallback
			}
		} catch (err) {
			const apiError = err as ApiError
			error.value = apiError.message || 'Failed to login. Please try again.'
			throw err
		} finally {
			loading.value = false
		}
	}

	async function logout() {
		loading.value = true
		error.value = null

		try {
			await api.post('/auth/logout')
		} catch (err) {
			const apiError = err as ApiError
			error.value = apiError.message || 'Failed to logout. Please try again.'
			console.error('Logout error:', err)
		} finally {
			token.value = null
			user.value = null
			localStorage.removeItem('token')
			localStorage.removeItem('mock-email')
			localStorage.removeItem('mock-role')
			router.push('/login')
			loading.value = false
		}
	}

	async function checkAuth() {
		if (!token.value) return false

		loading.value = true
		error.value = null

		try {
			const response = await api.get<User>('/auth/me')
			user.value = response.data
			return true
		} catch (err) {
			const apiError = err as ApiError
			error.value = apiError.message || 'Session expired. Please login again.'
			token.value = null
			user.value = null
			localStorage.removeItem('token')
			return false
		} finally {
			loading.value = false
		}
	}

	function clearError() {
		error.value = null
	}

	return {
		// State
		user,
		token,
		loading,
		error,
		// Getters
		isAuthenticated,
		currentUser,
		isLoading,
		hasError,
		// Actions
		login,
		logout,
		checkAuth,
		clearError
	}
}) 
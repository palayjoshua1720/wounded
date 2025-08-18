/**
 * useLogin Composable
 * 
 * A composable that handles user authentication functionality including login and logout.
 * It manages the login form state, handles form submission, and provides error handling.
 * 
 * Features:
 * - Form state management (email, password)
 * - Login form submission with validation
 * - Error handling and loading states
 * - Logout functionality
 * - Automatic redirection after successful login
 * 
 * @returns {Object} An object containing form state, loading state, error state, and auth methods
 */

import { ref } from 'vue'
import { watchEffect } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

// Global loader state for logout
export const logoutLoading = ref(false)

export function useLogin() {
	const router = useRouter()
	const route = useRoute()
	const authStore = useAuthStore()

	const email = ref('')
	const password = ref('')
	const tfaInput = ref('')
	const loading = ref(false)
	const error = ref('')
	const proceedLogin = ref(false)
	const continue2FA = ref(false)

	const handleLogin = async () => {
		
		try {
			loading.value = true
			error.value = ''

			// Validate input
			if (!email.value || !password.value) {
				throw new Error('Please enter both email and password')
			}

			// Static credentials
			let role = ''
			if (email.value === 'admin@medicalinv.com' && password.value === 'password') {
				role = 'admin'
			} else if (email.value === 'clinic@healthcare.com' && password.value === 'password') {
				role = 'clinic'
			} else if (email.value === 'sales@medicalinv.com' && password.value === 'password') {
				role = 'sales'
			} else {
				throw new Error('Invalid email or password')
			}

			if (localStorage.getItem('2fa-enabled') === 'true') {
				continue2FA.value = true
				proceedLogin.value = false
				loading.value = false
				return
			}

			// Mock user with role
			const mockUser = {
				id: 1,
				email: email.value,
				name: email.value.split('@')[0],
				role
			}

			// Set mock user in store
			authStore.user = mockUser
			authStore.token = 'mock-token'
			localStorage.setItem('token', 'mock-token')
			localStorage.setItem('mock-email', email.value)
			localStorage.setItem('mock-role', role)

			// Add a short delay so the loader is visible
			await new Promise(resolve => setTimeout(resolve, 400));

			// Redirect based on role
			if (role === 'admin' || role === 'sales') {
				await router.push('/admin-dashboard')
			} else if (role === 'clinic') {
				await router.push('/clinic-dashboard')
			} else {
				await router.push('/')
			}
		
		} catch (err) {
			toast.error('Login failed: ' + (err instanceof Error ? err.message : 'An error occurred'))
		} finally {
			loading.value = false
		}
	}

	const handleLogout = async () => {
		try {
			logoutLoading.value = true
			await authStore.logout()
			// Add a short delay so the loader is visible
			await new Promise(resolve => setTimeout(resolve, 200));
		} catch (error) {
			console.error('Logout failed:', error)
		}
			logoutLoading.value = false
	}

	const handleSecurity = async () => {
		try {
			proceedLogin.value = false
			continue2FA.value = true
			error.value = ''
			const pin = tfaInput.value.trim()
			const savedPin = localStorage.getItem('2fa-pin')
			if (!pin || pin !== savedPin) {
				toast.error('Invalid 2FA code. Please try again.')
				return
			}
			const role = localStorage.getItem('mock-role')
			if (role === 'admin' || role === 'sales') {
				await router.push('/admin-dashboard')
			} else if (role === 'clinic') {
				await router.push('/clinic-dashboard')
			} else {
				await router.push('/')
			}
		} catch (err) {
			error.value = err instanceof Error ? err.message : 'An error occurred during 2FA verification'
		}
	}

	return {
		email,
		password,
		loading,
		error,
		handleLogin,
		handleLogout,
		proceedLogin,
		continue2FA,
		handleSecurity,
		tfaInput,
	}
} 
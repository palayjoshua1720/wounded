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
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import axios from 'axios'
import api from '@/services/api'
import { pageLoader } from '@/composables/ui/usePageLoader'

export function useLogin() {
	const router = useRouter()
	const authStore = useAuthStore()

	const email = ref('')
	const password = ref('')
	const loading = ref(false)
	const error = ref('')
	const proceedLogin = ref(true)
	const continue2FA = ref(false)
	const loading2FA = ref(false)
	const pinBoxes = ref(['', '', '', '']);

	const tempToken = ref('')
	const tempUser = ref<any>(null)

	const handleLogin = async () => {

		try {
			loading.value = true
			error.value = ''

			if (!email.value || !password.value) {
				throw new Error('Please enter both email and password')
			}

			const response = await api.post('/api/auth/login', {
				email: email.value,
				password: password.value
			})

			const { user, token } = response.data

			await new Promise(resolve => setTimeout(resolve, 400));

			if (user.tfa_enabled) {
				tempUser.value = user
				tempToken.value = token

				continue2FA.value = true
				proceedLogin.value = false
			} else {
				saveData(user, token)
				continue2FA.value = false
				proceedLogin.value = true
				redirectBasedOnRole(user.user_role)
			}

		} catch (err: unknown) {
			if (axios.isAxiosError(err) && err.response?.data) {
				const message = (err.response.data as { message?: string }).message
				toast.error('Login failed: ' + (message || 'An error occurred'))
			} else if (err instanceof Error) {
				toast.error('Login failed: ' + err.message)
			} else {
				toast.error('Login failed: An unknown error occurred')
			}
		} finally {
			loading.value = false
		}
	}

	const handleLogout = async () => {
		try {
			pageLoader.value = true
			await authStore.logout()
			await new Promise(resolve => setTimeout(resolve, 200));
		} catch (error) {
			console.error('Logout failed:', error)
		}
		pageLoader.value = false
	}

	async function handleSecurity() {
		loading2FA.value = true
		const pin = pinBoxes.value.join('');
		console.log('PIN:', pin, pin.length, pinBoxes.value);

		if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
			toast.error('Please enter your 4-digit 2FA PIN.');
			loading2FA.value = false;
			return;
		}

		try {
			await api.post('/auth/validation/validate-tfauth', {
				pinBoxes: pin,
				email: tempUser.value?.email,
			});

			// Clear inputs
			pinBoxes.value = ['', '', '', ''];

			if (tempUser.value && tempToken.value) {
				saveData(tempUser.value, tempToken.value);
				tempUser.value = null;
				tempToken.value = '';
			}

			redirectBasedOnRole(authStore.user?.user_role || 0);
		} catch (err: unknown) {
			if (axios.isAxiosError(err) && err.response?.data) {
				const message = (err.response.data as { message?: string }).message
				toast.error('Login failed: ' + (message || 'An error occurred'))
			} else if (err instanceof Error) {
				toast.error('Login failed: ' + err.message)
			} else {
				toast.error('Login failed: An unknown error occurred')
			}
		} finally {
			continue2FA.value = true;
			loading2FA.value = false
		}
	}

	function redirectBasedOnRole(role: number) {
		if (role === 0) {
			router.push('/admin/dashboard')
		} else if (role === 1) {
			router.push('/office-staff/dashboard')
		} else if (role === 2) {
			router.push('/clinic/dashboard')
		} else if (role === 3) {
			router.push('/clinician/dashboard')
		} else if (role === 4) {
			router.push('/manufacturer/ivr-management')
		} else if (role === 5) {
			router.push('/biller/ivr-management')
		} else {
			router.push('/')
		}
	}

	function saveData(user: any, token: string) {
		authStore.user = user
		authStore.token = token
		localStorage.setItem('token', token)
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
		pinBoxes,
		loading2FA
	}
} 
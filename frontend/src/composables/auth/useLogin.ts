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
	const verificationCodeModal = ref(false)
	const verificationCode = ref('')
	const verificationLoading = ref(false)
	const verificationUserId = ref<number | null>(null)

	// Backup code properties
	const backupCodeModal = ref(false)
	const backupCode = ref('')
	const backupCodeLoading = ref(false)
	const backupUserId = ref<number | null>(null)

	const tempToken = ref('')
	const tempUser = ref<any>(null)

	const handleLogin = async () => {

		try {
			loading.value = true
			error.value = ''

			if (!email.value || !password.value) {
				throw new Error('Please enter both email and password')
			}

			const response = await api.post('/auth/login', {
				email: email.value,
				password: password.value
			})

			const responseData = response.data

			await new Promise(resolve => setTimeout(resolve, 400));

			// Check if verification is required
			if (responseData.requires_verification) {
				// Show email verification modal
				verificationUserId.value = responseData.user_id
				verificationCodeModal.value = true
				toast.info('Verification code sent to your email. Please check your inbox.')
			} else if (responseData.requires_backup_code) {
				// Show backup code modal
				backupUserId.value = responseData.user_id
				backupCodeModal.value = true
				toast.info('Backup code required for login. Please enter one of your backup codes.')
			} else if (responseData.requires_tfa) {
				// Handle TFA requirement
				// tempUser.value = { id: responseData.user_id } 
				tempUser.value = { id: responseData.user_id, email: email.value }

				continue2FA.value = true
				proceedLogin.value = false
				toast.info('Please enter your 2FA code to complete login.')
			} else {
				// Normal login flow
				const { user, token } = responseData

				if (user?.tfa_enabled) {
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
			const response = await api.post('/auth/validation/validate-tfauth', {
				// pin: pin,
				// user_id: tempUser.value?.id,
				pinBoxes: pin,
				email: tempUser.value?.email || '',
			});

			// Clear inputs
			(pinBoxes.value as string[]) = ['', '', '', ''];

			// On successful TFA validation, we should get user and token
			const { user, token } = response.data;

			saveData(user, token);
			tempUser.value = null;
			tempToken.value = '';

			// Set flags to show main content again
			continue2FA.value = false;
			proceedLogin.value = true;

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

	// Functions for email verification modal
	const closeVerificationModal = () => {
		verificationCodeModal.value = false
		verificationCode.value = ''
		verificationLoading.value = false
		verificationUserId.value = null
	}

	const verifyCode = async () => {
		if (verificationCode.value.length !== 6) {
			toast.error('Please enter a 6-digit verification code')
			return
		}

		verificationLoading.value = true

		try {
			const response = await api.post('/auth/login/verify-code', {
				user_id: verificationUserId.value,
				code: verificationCode.value
			})

			const { user, token } = response.data
			saveData(user, token)
			closeVerificationModal()
			continue2FA.value = false
			proceedLogin.value = true
			redirectBasedOnRole(user.user_role)
		} catch (err: unknown) {
			if (axios.isAxiosError(err) && err.response?.data) {
				const message = (err.response.data as { message?: string }).message
				toast.error('Verification failed: ' + (message || 'An error occurred'))
			} else if (err instanceof Error) {
				toast.error('Verification failed: ' + err.message)
			} else {
				toast.error('Verification failed: An unknown error occurred')
			}
		} finally {
			verificationLoading.value = false
		}
	}

	const onVerificationCodeInput = (e: Event) => {
		const input = e.target as HTMLInputElement
		const value = input.value.replace(/\D/g, '').substring(0, 6) // Only allow digits, max 6 chars
		verificationCode.value = value
	}

	// Functions for backup code modal
	const closeBackupCodeModal = () => {
		backupCodeModal.value = false
		backupCode.value = ''
		backupCodeLoading.value = false
		backupUserId.value = null
	}

	const verifyBackupCode = async () => {
		if (backupCode.value.length !== 8) { // 8 character backup codes
			toast.error('Please enter an 8-character backup code')
			return
		}

		backupCodeLoading.value = true

		try {
			const response = await api.post('/auth/login/verify-backup-code', {
				user_id: backupUserId.value,
				code: backupCode.value
			})

			const { user, token } = response.data
			saveData(user, token)
			closeBackupCodeModal()
			continue2FA.value = false
			proceedLogin.value = true
			redirectBasedOnRole(user.user_role)
		} catch (err: unknown) {
			if (axios.isAxiosError(err) && err.response?.data) {
				const message = (err.response.data as { message?: string }).message
				toast.error('Backup code verification failed: ' + (message || 'An error occurred'))
			} else if (err instanceof Error) {
				toast.error('Backup code verification failed: ' + err.message)
			} else {
				toast.error('Backup code verification failed: An unknown error occurred')
			}
		} finally {
			backupCodeLoading.value = false
		}
	}

	const onBackupCodeInput = (e: Event) => {
		const input = e.target as HTMLInputElement
		const value = input.value.toUpperCase().substring(0, 8) // Only allow up to 8 chars, convert to uppercase
		backupCode.value = value
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
		loading2FA,
		// Email verification modal properties
		verificationCodeModal,
		verificationCode,
		verificationLoading,
		verificationUserId,
		closeVerificationModal,
		verifyCode,
		onVerificationCodeInput,
		// Backup code properties
		backupCodeModal,
		backupCode,
		backupCodeLoading,
		backupUserId,
		closeBackupCodeModal,
		verifyBackupCode,
		onBackupCodeInput
	}
}
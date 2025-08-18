<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseModal from '@/components/common/BaseModal.vue'

const accounts = ref<{ email: string, username: string }[]>([])
const selectedAccount = ref<{ email: string, username: string } | null>(null)
const password = ref('')
const error = ref('')
const loading = ref(false)
const router = useRouter()
const authStore = useAuthStore()

const showLoginModal = ref(false)
const loginEmail = ref('')
const loginPassword = ref('')
const loginError = ref('')
const loginLoading = ref(false)

const showSavePrompt = ref(false)
const currentEmail = ref(localStorage.getItem('mock-email') || '')
const isAuthenticated = computed(() => !!(authStore.token || localStorage.getItem('token')))

function shouldPromptSaveCurrent() {
	if (!currentEmail.value) return false
	const accs = JSON.parse(localStorage.getItem('accounts') || '[]')
	return !accs.find((a: any) => a.email === currentEmail.value)
}

function handleLoginModalOpen() {
	showLoginModal.value = true
	loginEmail.value = ''
	loginPassword.value = ''
	loginError.value = ''
	showSavePrompt.value = shouldPromptSaveCurrent()
}

function saveCurrentAccountAndSwitch(save: boolean) {
	showSavePrompt.value = false
	if (save && currentEmail.value) {
		let accs = JSON.parse(localStorage.getItem('accounts') || '[]')
		if (!accs.find((a: any) => a.email === currentEmail.value)) {
			accs.push({ email: currentEmail.value, username: currentEmail.value.split('@')[0] })
			localStorage.setItem('accounts', JSON.stringify(accs))
		}
	}
	handleLoginSubmit()
}

onMounted(() => {
	const stored = localStorage.getItem('accounts')
	accounts.value = stored ? JSON.parse(stored) : []
})

function goBack() {
	router.back()
}

function addAccount() {
	handleLoginModalOpen()
}

function selectAccount(account: { email: string, username: string }) {
	selectedAccount.value = account
	loading.value = true
	setTimeout(() => {
		localStorage.removeItem('token')
		localStorage.setItem('mock-email', account.email)

		// Determine role based on email (mimic useLogin.ts logic)
		let role = ''
		if (account.email === 'admin@medicalinv.com') {
			role = 'admin'
		} else if (account.email === 'clinic@healthcare.com') {
			role = 'clinic'
		} else if (account.email === 'sales@medicalinv.com') {
			role = 'sales'
		}
		localStorage.setItem('mock-role', role)
		localStorage.setItem('token', 'mock-token')

		// Update Pinia store
		authStore.token = 'mock-token'
		authStore.user = {
			id: 1,
			email: account.email,
			name: account.username,
			role
		}
		loading.value = false
		// Redirect based on role
		if (role === 'admin' || role === 'sales') {
			router.push('/admin-dashboard')
		} else if (role === 'clinic') {
			router.push('/clinic-dashboard')
		} else {
			router.push('/')
		}
	}, 1000)
}

function removeAccount(account: { email: string, username: string }) {
	const accs = accounts.value.filter(a => a.email !== account.email)
	accounts.value = accs
	localStorage.setItem('accounts', JSON.stringify(accs))
	// If the removed account is the current one, clear mock-email
	if (localStorage.getItem('mock-email') === account.email) {
		localStorage.removeItem('mock-email')
	}
}

async function handleLoginSubmit() {
	loginError.value = ''
	if (!loginEmail.value || !loginPassword.value) {
		loginError.value = 'Please enter both email and password.'
		return
	}
	loginLoading.value = true

	// Simulate logout and login
	await new Promise(resolve => setTimeout(resolve, 1000))
	localStorage.removeItem('token')

	// Add new account to localStorage if not present
	let accs = JSON.parse(localStorage.getItem('accounts') || '[]')
	if (!accs.find((a: any) => a.email === loginEmail.value)) {
		accs.push({ email: loginEmail.value, username: loginEmail.value.split('@')[0] })
		localStorage.setItem('accounts', JSON.stringify(accs))
	}
	localStorage.setItem('mock-email', loginEmail.value)
	loginLoading.value = false
	showLoginModal.value = false
	// Refresh account list
	accounts.value = accs
	alert('Logged in as ' + loginEmail.value + ' (simulate login)')
	// router.push('/dashboard')
}

function getInitials(email: string, username: string) {
	if (username && username.trim().length > 0) {
		const parts = username.split(/\s|\./).filter(Boolean)
		if (parts.length > 1) return (parts[0][0] + parts[1][0]).toUpperCase()
		return parts[0].slice(0, 2).toUpperCase()
	}
	// fallback: use email
	return email.slice(0, 2).toUpperCase()
}
</script>

<template>
	<div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 px-4 py-8">
		<div class="w-full max-w-md bg-white dark:bg-gray-900 rounded shadow-lg p-8 relative">
			
			<!-- Loader Overlay -->
			<div v-if="loading && selectedAccount" class="absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/80 dark:bg-gray-900/80 rounded-xl">
				<svg class="animate-spin h-10 w-10 text-blue-600 dark:text-blue-400 mb-4" fill="none" viewBox="0 0 24 24">
					<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
					<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
				</svg>
				<div class="text-lg font-medium text-gray-700 dark:text-gray-200">Switching to <span class="font-semibold">{{ selectedAccount.username }}</span>...</div>
			</div>

			<!-- Back Button -->
			<button @click="goBack" class="absolute left-4 top-4 flex items-center text-gray-500 hover:text-blue-600 dark:hover:text-blue-400">
				<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
					<path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
				</svg>
				Back
			</button>
			<h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">Change Account</h2>
			<div v-if="accounts.length === 0" class="flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 mb-4">
				<div class="mb-2">No accounts found. Add a new account to get started.</div>
				<button @click="addAccount" class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-2xl mb-2">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
				</button>
				<div class="text-xs text-gray-400 dark:text-gray-500">Click the plus to add a new account. You can switch between accounts here for quick access.</div>
			</div>
			<div v-else>
				<ul class="space-y-4 mb-6">
					<li v-for="account in accounts" :key="account.email" class="flex items-center justify-between">
						<button @click="selectAccount(account)" :disabled="account.email === currentEmail && isAuthenticated" class="flex-1 flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors mr-2 relative">
							<div class="flex items-center">

								<!-- Initials Circle -->
								<span class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-200 dark:bg-blue-800 text-blue-800 dark:text-blue-200 font-bold text-lg mr-3">
									{{ getInitials(account.email, account.username) }}
								</span>
								<div class="text-left">
									<div class="font-medium text-gray-900 dark:text-white flex items-center">
										{{ account.username }}
										<span v-if="account.email === currentEmail && isAuthenticated" class="ml-2 px-2 py-0.5 rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-xs font-semibold">(currently logged in)</span>
									</div>
									<div class="text-xs text-gray-500 dark:text-gray-400">{{ account.email }}</div>
								</div>
							</div>
							<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
						</button>
						<button @click="removeAccount(account)" class="ml-2 p-2 rounded-full hover:bg-red-100 dark:hover:bg-red-900" title="Remove Account" :disabled="account.email === currentEmail && isAuthenticated">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-red-500" >
								<path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
							</svg>
						</button>
					</li>
				</ul>
				
				<!-- Floating + button for adding account -->
				<button @click="addAccount" class="fixed bottom-8 right-8 z-50 w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:focus:ring-blue-800">
					<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
					<span class="sr-only">Add Account</span>
				</button>
			</div>
		</div>

		<!-- Login Modal -->
		<BaseModal v-model="showLoginModal" title="Add Account" width="max-w-md w-full">
			<form v-if="!showSavePrompt" @submit.prevent="handleLoginSubmit" class="space-y-6">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
					<input v-model="loginEmail" type="email" required placeholder="Enter your email" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
					<input v-model="loginPassword" type="password" required placeholder="Enter your password" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white" />
				</div>
				<div v-if="loginError" class="text-sm text-red-600 text-center">{{ loginError }}</div>
				<div class="flex justify-end space-x-3">
					<button type="button" @click="showLoginModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
					<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center" :disabled="loginLoading">
						<svg v-if="loginLoading" class="animate-spin h-5 w-5 mr-2 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
						<span v-if="!loginLoading">Add Account</span>
						<span v-else>Adding...</span>
					</button>
				</div>
			</form>
			<div v-else class="space-y-6">
				<div class="text-center text-gray-700 dark:text-gray-200 mb-2">
					<span class="font-semibold">Your current account ({{ currentEmail }}) is not saved.</span>
					<div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Would you like to save this account for quick switching in the future?</div>
				</div>
				<div class="flex justify-center space-x-4">
					<button @click="saveCurrentAccountAndSwitch(true)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
					<button @click="saveCurrentAccountAndSwitch(false)" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Don't Save</button>
				</div>
			</div>
		</BaseModal>
	</div>
</template> 
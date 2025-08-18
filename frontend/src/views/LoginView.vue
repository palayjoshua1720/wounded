<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 px-2 sm:px-6 py-8">
        <div class="w-full max-w-5xl bg-white/80 dark:bg-gray-900/90 rounded shadow-2xl flex flex-col md:flex-row overflow-hidden">
            <!-- Left: Branding & Features -->
            <div class="hidden md:flex flex-col justify-between p-10 w-1/2 bg-gradient-to-br from-blue-700 to-green-600 text-white relative">
                <div>
                    <div class="flex items-center mb-8">
                        <div class="bg-white/20 rounded p-3 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0A9 9 0 11 3 12a9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold">Proweaver Test</div>
                            <div class="text-sm opacity-80">Medical Ordering & Inventory</div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="text-2xl font-light leading-tight">Streamline Your Medical<br />
                            <span class="font-bold">Graft & Device Operations</span>
                        </div>
                    </div>
                    <ul class="space-y-4 mb-10">
                        <li class="flex items-center text-base"><svg class="w-5 h-5 text-green-300 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Real-time inventory tracking</li>
                        <li class="flex items-center text-base"><svg class="w-5 h-5 text-green-300 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Automated ordering workflows</li>
                        <li class="flex items-center text-base"><svg class="w-5 h-5 text-green-300 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> Compliance & audit trails</li>
                    </ul>
                </div>
                <div class="flex items-center space-x-6 text-xs opacity-80">
                    <div class="flex items-center space-x-1">
                    <!-- Heroicons ShieldCheckIcon -->
                        <svg class="w-6 h-6 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        <span>HIPAA Compliant</span>
                    </div>
                    <div class="flex items-center space-x-1">
                    <!-- Heroicons ShieldCheckIcon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-500" >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                        <span>FDA Validated</span>
                    </div>
                </div>
            </div>

            <!-- Right: Login Form -->
            <div class="flex-1 flex flex-col justify-center p-8 sm:p-12 bg-white dark:bg-gray-900">
                <div class="max-w-md w-full mx-auto">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Welcome Back</h2>
                        <p class="text-gray-600 dark:text-gray-400">Sign in to access your medical inventory dashboard</p>
                    </div>
                    <form class="space-y-6 relative" @submit.prevent="handleLogin">
                        <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-900/70 z-10 rounded-lg">
                            <span class="relative flex items-center justify-center h-16 w-16">
                                <svg class="absolute animate-ping-slow h-16 w-16 text-blue-400 dark:text-blue-700 opacity-30" viewBox="0 0 64 64" fill="none"><circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4"/></svg>
                                <svg class="relative h-12 w-12 text-blue-600 dark:text-blue-400" viewBox="0 0 64 64" fill="none">
                                    <rect x="27" y="12" width="10" height="40" rx="3" fill="currentColor"/>
                                    <rect x="12" y="27" width="40" height="10" rx="3" fill="currentColor"/>
                                </svg>
                            </span>
                        </div>
                        <div v-if="proceedLogin" class="space-y-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 z-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-4.418 0-8 1.79-8 4v2h16v-2c0-2.21-3.582-4-8-4z" /></svg>
                                    <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    required
                                    class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter your email"
                                    v-model="email"
                                    />
                                </div>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 z-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5A4.5 4.5 0 0 0 7.5 7.5v3m9 0H7.5m9 0a2.25 2.25 0 0 1 2.25 2.25v4.5A2.25 2.25 0 0 1 16.5 19.5h-9A2.25 2.25 0 0 1 5.25 17.25v-4.5A2.25 2.25 0 0 1 7.5 10.5"/></svg>
                                    <input
                                    id="password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="appearance-none relative block w-full pl-10 pr-12 py-3 border border-gray-300 dark:border-gray-600 rounded placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter your password"
                                    v-model="password"
                                    />
                                    <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 z-10"
                                    @click="showPassword = !showPassword"
                                    tabindex="-1"
                                    >
                                        <EyeIcon v-if="showPassword" class="w-5 h-5" />
                                        <EyeSlashIcon v-else class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-if="proceedLogin" class="flex items-center justify-between mb-2">
                            <label class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" class="form-checkbox rounded text-blue-600 focus:ring-blue-500 mr-2" />
                                Remember me
                            </label>
                            <button
                            type="button"
                            @click="goToForgotPassword"
                            class="text-sm text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Forgot password?
                            </button>
                        </div>
                        <button v-if="proceedLogin"
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center items-center py-3 px-4 border border-transparent text-sm font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Sign In
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 ml-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"></path>
                            </svg>
                        </button>
                    </form>
                    <form v-if="continue2FA" @submit.prevent="handleSecurity" class="space-y-4">
                        <label for="tfa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enter 2FA PIN</label>
                        <input
                            id="tfa"
                            v-model="tfaInput"
                            maxlength="4"
                            inputmode="numeric"
                            class="block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="4-digit PIN"
                            autocomplete="one-time-code"
                        />
                        <button
                            type="submit"
                            class="w-full py-3 px-4 bg-blue-600 text-white rounded font-medium hover:bg-blue-700 transition-colors"
                        >
                            Verify
                        </button>
                    </form>
                    <div v-if="hasSavedAccounts" class="my-6 flex flex-col items-center">
                        <button @click="goToSwitchAccount" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Switch Account
                        </button>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">Switch to a saved account for quick access</span>
                    </div>
                    <div class="my-6 border-t border-gray-200 dark:border-gray-700"></div>
                    <div class="text-center mb-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Need access to the system?</span>
                        <div>
                            <a href="#" class="text-blue-600 hover:underline dark:text-blue-400">Contact your system administrator</a>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start p-4 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-700">
                            <svg class="w-7 h-7 text-green-500 mr-3 mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                            <div>
                                <div class="font-medium text-green-900 dark:text-green-300">Secure Access</div>
                                <div class="text-xs text-green-900 dark:text-green-200 opacity-80">This system is protected with enterprise-grade security and complies with HIPAA, SOX, and FDA regulations.</div>
                            </div>
                        </div>
                        <div class="flex items-start p-4 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-700">
                            <!-- Heroicons LockClosedIcon for Two-Factor Authentication -->
                            <svg class="w-6 h-6 text-blue-500 mr-3 mt-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.5A4.5 4.5 0 0 0 7.5 7.5v3m9 0H7.5m9 0a2.25 2.25 0 0 1 2.25 2.25v4.5A2.25 2.25 0 0 1 16.5 19.5h-9A2.25 2.25 0 0 1 5.25 17.25v-4.5A2.25 2.25 0 0 1 7.5 10.5"/></svg>
                            <div>
                                <div class="font-medium text-blue-900 dark:text-blue-300">Two-Factor Authentication</div>
                                <div class="text-xs text-blue-900 dark:text-blue-200 opacity-80">Enhanced security with 2FA is available for all medical staff accounts.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useLogin } from '@/composables/auth/useLogin'
import { useRouter } from 'vue-router'
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'

const { email, password, loading, error, tfaInput, handleLogin, proceedLogin, continue2FA, handleSecurity } = useLogin()
const showPassword = ref(false)
const router = useRouter()

// Add logic to check for saved accounts
const hasSavedAccounts = ref(false)

onMounted(() => {
    proceedLogin.value = true
    continue2FA.value = false
    const accs = localStorage.getItem('accounts')
    hasSavedAccounts.value = !!accs && JSON.parse(accs).length > 0
})

function goToForgotPassword() {
    router.push({ name: 'forgot-password' })
}

function goToSwitchAccount() {
    router.push({ name: 'change-account' })
}
</script> 

<style scoped>
@keyframes ping-slow {
    0% { transform: scale(1); opacity: 0.3; }
    70% { transform: scale(1.3); opacity: 0; }
    100% { transform: scale(1.3); opacity: 0; }
}
.animate-ping-slow {
    animation: ping-slow 1.2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style> 
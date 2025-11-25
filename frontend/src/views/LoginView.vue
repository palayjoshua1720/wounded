<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 px-2 sm:px-6 py-8">
        <div class="w-full max-w-5xl bg-white/80 dark:bg-gray-900/90 rounded shadow-2xl flex flex-col md:flex-row overflow-hidden">
            <!-- Left: Branding & Features -->
            <div class="hidden md:flex flex-col justify-between p-10 w-1/2 bg-gradient-to-br from-blue-700 to-green-600 text-white relative">
                <div>
                    <div class="flex items-center mb-8">
                        <div className="bg-white rounded p-3 mr-3 flex items-center justify-center">
                            <img
                            src="/main-logo.webp" 
                            alt="Main Logo" 
                            className="w-20 h-20 object-contain" 
                            />
                        </div>
                        <div>
                            <div class="text-xl font-bold">WOUNDMED INC.</div>
                            <div class="text-sm opacity-80">Medical Ordering & Inventory</div>
                        </div>
                    </div>
                    <div class="mb-8">
                        <div class="text-2xl leading-tight font-bold">Streamline Your Medical Graft & Device Operations<br />
                        </div>
                    </div>
                    <div class="mb-10">
                        <div class="text-lg font-light leading-tight">HIPPA-compliant inventory management for healthcare providers<br />
                        </div>
                    </div>
                    <ul class="space-y-6">
                        <!-- <li class="flex items-center text-lg">
                            <CircleCheckBig class="w-5 h-5 text-green-300 mr-2" />
                            Real-time inventory tracking
                        </li> -->
                        <li class="flex items-center text-lg">
                            <CircleCheckBig class="w-5 h-5 text-green-300 mr-2" />
                            Automated ordering workflows
                        </li>
                        <li class="flex items-center text-lg">
                            <CircleCheckBig class="w-5 h-5 text-green-300 mr-2" />
                            Compliance & audit trails
                        </li>
                        <li class="flex items-center text-lg">
                            <CircleCheckBig class="w-5 h-5 text-green-300 mr-2" />
                            Secure patient data protection
                        </li>
                        <li class="flex items-center text-lg">
                            <CircleCheckBig class="w-5 h-5 text-green-300 mr-2" />
                            Role-based-access control
                        </li>
                    </ul>
                </div>
                <div className="flex items-center space-x-3 border-l-4 border-blue-500 bg-white p-4 rounded shadow-sm text-sm">
                    <div className="flex-shrink-0 text-blue-500">
                        <ShieldCheck className="w-6 h-6" />
                    </div>
                    <div>
                        <p className="font-semibold text-gray-800">HIPAA Compliant</p>
                        <p className="text-gray-600">
                            All data is encrypted and managed in compliance with healthcare regulations
                        </p>
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
                                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 z-10" />
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
                                    <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 z-10" />
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
                                        <Eye v-if="showPassword" class="w-5 h-5" />
                                        <EyeOff v-else class="w-5 h-5" />
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
                            {{ loading ? 'Signing In...' : 'Sign In'}}
                            <LogIn class="w-5 h-5 ml-2" />
                        </button>
                    </form>
                    <div class="relative w-full mx-auto">
                        <!-- Loader overlay -->
                        <div v-if="loading2FA" class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-900/70 z-10 rounded-lg">
                            <span class="relative flex items-center justify-center h-16 w-16">
                                <svg class="absolute animate-ping-slow h-16 w-16 text-blue-400 dark:text-blue-700 opacity-30" viewBox="0 0 64 64" fill="none" >
                                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" />
                                </svg>
                                <svg class="relative h-12 w-12 text-blue-600 dark:text-blue-400" viewBox="0 0 64 64" fill="none">
                                    <rect x="27" y="12" width="10" height="40" rx="3" fill="currentColor" />
                                    <rect x="12" y="27" width="40" height="10" rx="3" fill="currentColor" />
                                </svg>
                            </span>
                        </div>

                        <!-- 2FA Form -->
                        <form v-if="continue2FA" @submit.prevent="handleSecurity" class="space-y-4 bg-white dark:bg-gray-900 rounded-lg relative">
                            <label for="tfa" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Enter your Two-Factor Authentication Pin
                            </label>
                            <div class="flex space-x-3 justify-center mb-4">
                                <input
                                v-for="(d, i) in 4"
                                :key="i"
                                :ref="el => { const input = el as HTMLInputElement; if (input && input.tagName === 'INPUT') setPinRefs[i].value = input }"
                                v-model="pinBoxes[i]"
                                type="text"
                                maxlength="1"
                                inputmode="numeric"
                                pattern="\d"
                                class="w-12 h-12 text-center border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                                @input="e => onPinBoxInput(i, 'set', e as InputEvent)"
                                @keydown="e => onPinBoxKeydown(i, 'set', e)"
                                />
                            </div>
                            <button
                            type="submit"
                            class="w-full py-3 px-4 bg-blue-600 text-white rounded font-medium hover:bg-blue-700 transition-colors"
                            >
                                {{ loading2FA ? 'Verifying...' : 'Verify'}}
                            </button>
                        </form>
                    </div>
                    <!-- <div v-if="hasSavedAccounts" class="my-6 flex flex-col items-center">
                        <button @click="goToSwitchAccount" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            Switch Account
                        </button>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">Switch to a saved account for quick access</span>
                    </div> -->
                    <div class="my-6 border-t border-gray-200 dark:border-gray-700"></div>
                    <!-- <div class="text-center mb-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Need access to the system?</span>
                        <div>
                            <a href="#" class="text-blue-600 hover:underline dark:text-blue-400">Contact your system administrator</a>
                        </div>
                    </div> -->
                    <div class="space-y-3">
                        <div class="flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-700">
                            <CircleCheck class="w-12 h-12 text-green-500 mr-3 mt-1" />
                            <div>
                                <div class="font-medium text-green-900 dark:text-green-300">
                                    Secure Access
                                </div>
                                <div class="text-xs text-green-900 dark:text-green-200 opacity-80">This system is protected with enterprise-grade security and complies with HIPAA requirements to ensure the confidentiality and security of patient and medical information.</div>
                            </div>
                        </div>
                        <div class="flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-700">
                            <KeySquare class="w-6 h-6 text-blue-500 mr-3 mt-1" />
                            <div>
                                <div class="font-medium text-blue-900 dark:text-blue-300">
                                    Two-Factor Authentication
                                </div>
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
import { ref, onMounted } from 'vue'
import { useLogin } from '@/composables/auth/useLogin'
import { useRouter } from 'vue-router'
import {
    Eye,
    EyeOff,
    Mail,
    Lock,
    ShieldCheck,
    CircleCheckBig,
    LogIn,
    CircleCheck,
    KeySquare
} from 'lucide-vue-next';

const { email, password, loading, loading2FA, pinBoxes, handleLogin, proceedLogin, continue2FA, handleSecurity } = useLogin()
const showPassword = ref(false)
const router = useRouter()
const hasSavedAccounts = ref(false)
const setPinRefs = [ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>()]
const newPinBoxes = ref<string[]>(['', '', '', ''])
const changePinRefs = [ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>(), ref<HTMLInputElement>()]

function goToForgotPassword() {
    router.push({ name: 'forgot-password' })
}

function goToSwitchAccount() {
    router.push({ name: 'change-account' })
}

function onPinBoxInput(i: number, mode: 'set' | 'change', e: InputEvent) {
    const arr = mode === 'set' ? pinBoxes.value : newPinBoxes.value
    const refs = mode === 'set' ? setPinRefs : changePinRefs
    const input = e.target as HTMLInputElement
    let val = input.value.replace(/[^\d]/g, '')

    // Handle paste or fast typing
    if (e.inputType === 'insertFromPaste' || val.length > 1) {
        const pasted = (e.data || val).replace(/[^\d]/g, '')
        for (let j = 0; j < 4; j++) {
            arr[j] = pasted[j] || ''
        }
        refs[Math.min(pasted.length, 4) - 1]?.value?.focus()
        return
    }

    arr[i] = val
    if (val && i < 3) {
        refs[i + 1].value?.focus()
    }
}

function onPinBoxKeydown(i: number, mode: 'set' | 'change', e: KeyboardEvent) {
    const arr = mode === 'set' ? pinBoxes.value : newPinBoxes.value
    const refs = mode === 'set' ? setPinRefs : changePinRefs

    if (e.key === 'Backspace') {
        if (arr[i]) {
            arr[i] = ''
        } else if (i > 0) {
            refs[i - 1].value?.focus()
            arr[i - 1] = ''
            e.preventDefault()
        }
    } else if (e.key === 'Delete') {
        if (arr[i]) {
            arr[i] = ''
        } else if (i < 3) {
            refs[i + 1].value?.focus()
            e.preventDefault()
        }
    } else if (e.key >= '0' && e.key <= '9' && arr[i] && i < 3) {
        refs[i + 1].value?.focus()
    } else if (e.key === 'ArrowLeft' && i > 0) {
        refs[i - 1].value?.focus()
        e.preventDefault()
    } else if (e.key === 'ArrowRight' && i < 3) {
        refs[i + 1].value?.focus()
        e.preventDefault()
    }
}

onMounted(() => {
    proceedLogin.value = true
    continue2FA.value = false
    const accs = localStorage.getItem('accounts')
    hasSavedAccounts.value = !!accs && JSON.parse(accs).length > 0
})
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
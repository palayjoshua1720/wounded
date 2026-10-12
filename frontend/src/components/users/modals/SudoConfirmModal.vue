<template>
    <BaseModal v-model="showModal" :title="title" width="max-w-md w-full">
        <div class="space-y-5">
            <!-- Icon & Message -->
            <div class="flex flex-col items-center text-center space-y-3">
                <div :class="[
                    'w-14 h-14 rounded-full flex items-center justify-center',
                    variant === 'danger'
                        ? 'bg-red-100 dark:bg-red-900/30'
                        : 'bg-amber-100 dark:bg-amber-900/30'
                ]">
                    <ShieldAlert :class="[
                        'w-7 h-7',
                        variant === 'danger'
                            ? 'text-red-600 dark:text-red-400'
                            : 'text-amber-600 dark:text-amber-400'
                    ]" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed" v-html="message" />
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <span class="flex items-center">
                        <Lock class="w-4 h-4 mr-2 text-gray-400" />
                        Current Password<span class="text-red-500 ml-1">*</span>
                    </span>
                </label>
                <div class="relative">
                    <input
                        ref="passwordInput"
                        v-model="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
                        placeholder="Enter your account password"
                        autocomplete="current-password"
                        @keydown.enter="handleConfirm"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    >
                        <Eye v-if="!showPassword" class="w-5 h-5" />
                        <EyeOff v-else class="w-5 h-5" />
                    </button>
                </div>
                <p v-if="errorMessage" class="mt-2 text-sm text-red-600 dark:text-red-400 flex items-center">
                    <AlertCircle class="w-4 h-4 mr-1 flex-shrink-0" />
                    {{ errorMessage }}
                </p>
            </div>

            <!-- HIPAA Notice -->
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2.5 border border-gray-200 dark:border-gray-600">
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    <span class="font-semibold">HIPAA Security Policy:</span> Password re-authentication is required for high-risk administrative actions to protect against unauthorized access.
                </p>
            </div>
        </div>

        <template #actions>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
                <button
                    type="button"
                    @click="handleCancel"
                    class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="handleConfirm"
                    :disabled="!password || isLoading"
                    :class="[
                        'w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-white rounded-xl transition-all duration-200 flex items-center justify-center gap-2',
                        variant === 'danger'
                            ? 'bg-red-600 hover:bg-red-700 disabled:bg-red-400'
                            : 'bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400',
                        (!password || isLoading) ? 'opacity-60 cursor-not-allowed' : ''
                    ]"
                >
                    <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
                    <ShieldCheck v-else class="w-4 h-4" />
                    {{ confirmButtonText }}
                </button>
            </div>
        </template>
    </BaseModal>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { ShieldAlert, ShieldCheck, Lock, Eye, EyeOff, AlertCircle, Loader2 } from 'lucide-vue-next'

const props = withDefaults(defineProps<{
    modelValue: boolean
    title?: string
    message?: string
    confirmButtonText?: string
    variant?: 'danger' | 'warning'
    isLoading?: boolean
}>(), {
    title: 'Authorization Required',
    message: 'Enter your current account password to authorize this action.',
    confirmButtonText: 'Authorize & Confirm',
    variant: 'danger',
    isLoading: false,
})

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void
    (e: 'confirm', password: string): void
    (e: 'cancel'): void
}>()

const password = ref('')
const showPassword = ref(false)
const errorMessage = ref('')
const passwordInput = ref<HTMLInputElement | null>(null)

const showModal = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

function handleConfirm() {
    if (!password.value) {
        errorMessage.value = 'Password is required to authorize this action.'
        return
    }
    errorMessage.value = ''
    emit('confirm', password.value)
}

function handleCancel() {
    resetState()
    emit('cancel')
    emit('update:modelValue', false)
}

function resetState() {
    password.value = ''
    showPassword.value = false
    errorMessage.value = ''
}

// Auto-focus password input when modal opens; reset state when it closes
watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        resetState()
        nextTick(() => {
            passwordInput.value?.focus()
        })
    }
})

// Expose setError for parent to show backend validation errors
function setError(msg: string) {
    errorMessage.value = msg
}

defineExpose({ setError })
</script>

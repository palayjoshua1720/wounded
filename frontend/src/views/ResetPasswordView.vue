<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 px-4">
    <div class="max-w-md w-full bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-white text-center mb-6">
        Reset Password
      </h2>

      <form @submit.prevent="handleReset" class="space-y-4">
        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            placeholder="Enter your email"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 py-2.5 px-3"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.email }}</p>
        </div>

        <!-- New Password -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            New Password
          </label>
          <div class="relative">
            <input
              id="password"
              v-model="form.password"
              :type="showNewPassword ? 'text' : 'password'"
              placeholder="Enter new password"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 py-2.5 pl-3 pr-12"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white"
              @click="showNewPassword = !showNewPassword"
              aria-label="Toggle new password visibility"
              tabindex="-1"
            >
              <Eye v-if="showNewPassword" class="w-5 h-5" />
              <EyeOff v-else class="w-5 h-5" />
            </button>
          </div>
          <p v-if="errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.password }}</p>
        </div>

        <!-- Confirm Password -->
        <div>
          <label
            for="password_confirmation"
            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
          >
            Confirm Password
          </label>
          <div class="relative">
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Confirm new password"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 py-2.5 pl-3 pr-12"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-white"
              @click="showConfirmPassword = !showConfirmPassword"
              aria-label="Toggle confirm password visibility"
              tabindex="-1"
            >
              <Eye v-if="showConfirmPassword" class="w-5 h-5" />
              <EyeOff v-else class="w-5 h-5" />
            </button>
          </div>
          <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.password_confirmation }}
          </p>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          :disabled="isLoading"
          class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          {{ isLoading ? 'Resetting...' : 'Reset Password' }}
        </button>
      </form>

      <!-- Alerts -->
      <div
        v-if="message"
        class="mt-6 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 p-3 rounded-lg text-center"
      >
        {{ message }}
      </div>
      <div
        v-if="error"
        class="mt-6 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 p-3 rounded-lg text-center"
      >
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Eye, EyeOff } from 'lucide-vue-next'
import { authApi } from '@/services/api'

const route = useRoute()
const router = useRouter()

const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const error = ref('')
const message = ref('')
const errors = ref<Record<string, string>>({})
const isLoading = ref(false)

onMounted(() => {
  form.value.token = (route.query.token as string) || ''
  form.value.email = (route.query.email as string) || ''
})

const handleReset = async () => {   
  isLoading.value = true
  try {
    const res = await authApi.resetPassword(form.value)
    message.value = res.data.message
    error.value = ''
    errors.value = {}

    // Redirect to login after short delay
    setTimeout(() => {
      router.push({ name: 'login' })
    }, 2000)
  } catch (err: any) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      // Map errors to single strings
      errors.value = Object.fromEntries(
        Object.entries(err.response.data.errors).map(([k, v]) => [k, v[0]])
      )
    } else {
      error.value = err.response?.data?.message || 'Something went wrong'
    }
    message.value = ''
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-green-50 dark:from-gray-900 dark:to-gray-800 px-4">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white">
          Reset your password
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          Enter your email address and we'll send you a link to reset your password
        </p>
      </div>

      <div v-if="isSubmitted" class="space-y-8">
        <div class="text-center">
          <div class="mx-auto w-16 h-16 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
          </div>
          <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">Check your email</h2>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            We've sent a password reset link to {{ email }}
          </p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
            If you don't see the email, check your spam folder or try again with a different email address.
          </p>
          <button
            @click="goToLogin"
            class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to sign in
          </button>
        </div>
      </div>

      <form v-else class="mt-8 space-y-6 bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg" @submit.prevent="handleSubmit">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Email address
          </label>
          <div class="relative">
            <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 01-8 0m8 0V8a4 4 0 10-8 0v4m8 0a4 4 0 01-8 0"/></svg>
            <input
              id="email"
              name="email"
              type="email"
              required
              class="appearance-none relative block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter your email"
              v-model="email"
            />
          </div>
        </div>
        <div class="space-y-4">
          <button
            type="submit"
            :disabled="isLoading"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ isLoading ? 'Sending...' : 'Send reset link' }}
          </button>
          <button
            type="button"
            @click="goToLogin"
            class="w-full flex items-center justify-center py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to sign in
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const email = ref('')
const isSubmitted = ref(false)
const isLoading = ref(false)
const router = useRouter()

async function handleSubmit() {
  isLoading.value = true
  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500))
  isSubmitted.value = true
  isLoading.value = false
}

function goToLogin() {
  router.push({ name: 'login' })
}
</script> 
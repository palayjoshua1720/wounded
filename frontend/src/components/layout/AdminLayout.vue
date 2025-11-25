<template>
  <div class="min-h-screen bg-gray-50">
    <div v-if="logoutLoading"
      class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/80 dark:bg-gray-900/80">
      <svg class="animate-spin h-16 w-16 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg"
        fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
    </div>
    <!-- Mobile sidebar -->
    <transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-50 lg:hidden">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false" />
        <nav class="relative flex flex-col w-full max-w-xs bg-white shadow-xl">
          <div class="flex items-center justify-between px-4 py-6">
            <h1 class="text-xl font-bold text-gray-900">Medical Inventory</h1>
            <button @click="sidebarOpen = false" class="text-gray-500 hover:text-gray-700">
              <XMarkIcon class="w-6 h-6" />
            </button>
          </div>
          <div class="flex-1 px-4 pb-6">
            <router-link v-for="item in navigation" :key="item.id" :to="item.to"
              class="w-full flex items-center px-3 py-3 mb-2 text-left rounded-lg transition-colors" :class="isActive(item.to)
                ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700'
                : 'text-gray-700 hover:bg-gray-50'" @click="sidebarOpen = false">
              <component :is="item.icon" class="w-5 h-5 mr-3" />
              {{ item.label }}
            </router-link>
          </div>
        </nav>
      </div>
    </transition>

    <!-- Desktop sidebar -->
    <nav class="hidden lg:flex lg:flex-col lg:fixed lg:inset-y-0 lg:w-64 lg:bg-white lg:shadow-lg lg:z-40">
      <div class="flex items-center px-6 py-6 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-900">Medical Inventory</h1>
      </div>
      <div class="flex-1 px-4 py-6">
        <router-link v-for="item in navigation" :key="item.id" :to="item.to"
          class="w-full flex items-center px-3 py-3 mb-2 text-left rounded-lg transition-colors" :class="isActive(item.to)
            ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-700'
            : 'text-gray-700 hover:bg-gray-50'">
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          {{ item.label }}
        </router-link>
      </div>
    </nav>

    <!-- Main content -->
    <div class="lg:ml-64">
      <!-- Header -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-4 py-4">
          <button @click="sidebarOpen = true"
            class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-50">
            <Bars3Icon class="w-6 h-6" />
          </button>
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                <UserIcon class="w-5 h-5 text-white" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                <p class="text-xs text-gray-500 capitalize">{{ user?.role }}</p>
              </div>
            </div>
            <button @click="logout"
              class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
              <ArrowRightOnRectangleIcon class="w-4 h-4 mr-2" />
              Logout
            </button>
          </div>
        </div>
      </header>
      <!-- Page content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { logoutLoading } from '@/composables/auth/useLogin'
import {
  UserIcon,
  Bars3Icon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  UsersIcon,
  ShoppingCartIcon,
  CubeIcon,
  ArrowPathIcon,
  DocumentTextIcon,
  ReceiptPercentIcon,
  ShieldCheckIcon,
  BellIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

const sidebarOpen = ref(false)
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const user = computed(() => authStore.user)
const logout = () => authStore.logout()

const navigation = [
  { id: 'dashboard', label: 'Dashboard', to: '/admin-dashboard', icon: ChartBarIcon },
  { id: 'users', label: 'User Management', to: '/users', icon: UsersIcon },
  { id: 'orders', label: 'Order Management', to: '/orders', icon: ShoppingCartIcon },
  { id: 'invoices', label: 'Invoices Management', to: '/invoices', icon: ShoppingCartIcon },
  { id: 'inventory', label: 'Inventory & Serials', to: '/inventory', icon: CubeIcon },
  { id: 'returns', label: 'Return Inventory', to: '/returns', icon: ArrowPathIcon },
  { id: 'usage', label: 'Graft Usage', to: '/usage', icon: DocumentTextIcon },
  { id: 'invoices', label: 'Invoices & Payments', to: '/invoices', icon: ReceiptPercentIcon },
  { id: 'ivr', label: 'IVR Management', to: '/ivr', icon: ShieldCheckIcon },
  { id: 'notifications', label: 'Notifications', to: '/notifications', icon: BellIcon },
  { id: 'reports', label: 'Reporting', to: '/reports', icon: ChartBarIcon },
]

const isActive = (to: string) => route.path === to
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
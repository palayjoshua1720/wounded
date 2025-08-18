<template>
  <div class="mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Profile</h1>
      <p class="text-gray-600 dark:text-gray-400">Manage your account settings and preferences</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Profile Card -->
      <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <!-- Avatar Section -->
          <div class="text-center mb-6">
            <div class="mx-auto h-24 w-24 rounded-full bg-indigo-600 flex items-center justify-center text-white text-2xl font-bold mb-4">
              {{ userInitials }}
            </div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ currentUser?.name }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ currentUser?.email }}</p>
            <div class="mt-2">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  role === 'admin' ? 'bg-purple-100 text-purple-800' :
                  role === 'clinic' ? 'bg-blue-100 text-blue-800' :
                  'bg-green-100 text-green-800'
                ]"
              >
                {{ role?.toUpperCase() || 'USER' }}
              </span>
            </div>
          </div>

          <!-- Quick Stats -->
          <div class="space-y-4">
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
              <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Account Statistics</h3>
              <div class="space-y-3">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500 dark:text-gray-400">Member Since</span>
                  <span class="text-gray-900 dark:text-gray-100">{{ memberSince }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500 dark:text-gray-400">Last Login</span>
                  <span class="text-gray-900 dark:text-gray-100">{{ lastLogin }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500 dark:text-gray-400">Status</span>
                  <span class="text-green-600 dark:text-green-400">Active</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Personal Information</h3>
          </div>
          <div class="p-6">
            <form @submit.prevent="updateProfile">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Full Name
                  </label>
                  <input
                    v-model="profileForm.name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Email Address
                  </label>
                  <input
                    v-model="profileForm.email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Phone Number
                  </label>
                  <input
                    v-model="profileForm.phone"
                    type="tel"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Department
                  </label>
                  <input
                    v-model="profileForm.department"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                  />
                </div>
              </div>
              <div class="mt-6">
                <button
                  type="submit"
                  :disabled="isUpdating"
                  class="w-full md:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ isUpdating ? 'Updating...' : 'Update Profile' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Role-Specific Information -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
              {{ roleSpecificTitle }}
            </h3>
          </div>
          <div class="p-6">
            <!-- Admin Section -->
            <div v-if="role === 'admin'" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">System Access</h4>
                  <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• Full system administration</li>
                    <li>• User management</li>
                    <li>• System configuration</li>
                    <li>• Data analytics</li>
                  </ul>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Recent Activity</h4>
                  <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <div>• Created 5 new users</div>
                    <div>• Updated system settings</div>
                    <div>• Generated monthly reports</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Clinic Section -->
            <div v-if="role === 'clinic'" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Clinic Information</h4>
                  <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <div><strong>Clinic Name:</strong> Medical Center ABC</div>
                    <div><strong>License:</strong> MC-2024-001</div>
                    <div><strong>Address:</strong> 123 Healthcare Ave</div>
                    <div><strong>Contact:</strong> (555) 123-4567</div>
                  </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Recent Orders</h4>
                  <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <div>• Order #ORD-001 - Pending</div>
                    <div>• Order #ORD-002 - Delivered</div>
                    <div>• Order #ORD-003 - Shipped</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Sales Section -->
            <div v-if="role === 'sales'" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Sales Information</h4>
                  <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <div><strong>Sales Region:</strong> North America</div>
                    <div><strong>Employee ID:</strong> SAL-2024-001</div>
                    <div><strong>Manager:</strong> John Smith</div>
                    <div><strong>Department:</strong> Sales & Marketing</div>
                  </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Performance Metrics</h4>
                  <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <div>• Monthly Target: $50,000</div>
                    <div>• Current Sales: $42,500</div>
                    <div>• Conversion Rate: 85%</div>
                    <div>• Active Clients: 25</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Security Settings</h3>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Two-Factor Authentication</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Add an extra layer of security to your account</p>
                </div>
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none"
                >
                  Enable
                </button>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Change Password</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">Update your password regularly</p>
                </div>
                <button
                  type="button"
                  @click="showChangePassword = true"
                  class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none"
                >
                  Change
                </button>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Session Management</h4>
                  <p class="text-sm text-gray-500 dark:text-gray-400">View and manage active sessions</p>
                </div>
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none"
                >
                  Manage
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Change Password Modal -->
    <BaseModal
      :isOpen="showChangePassword"
      @close="showChangePassword = false"
      title="Change Password"
      size="md"
    >
      <form @submit.prevent="changePassword" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Current Password
          </label>
          <input
            v-model="passwordForm.currentPassword"
            type="password"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            New Password
          </label>
          <input
            v-model="passwordForm.newPassword"
            type="password"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Confirm New Password
          </label>
          <input
            v-model="passwordForm.confirmPassword"
            type="password"
            required
            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-gray-100"
          />
        </div>
        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="showChangePassword = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-400"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isChangingPassword"
            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isChangingPassword ? 'Changing...' : 'Change Password' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useUser } from '@/composables/auth/useUser'
import BaseModal from '@/components/ui/BaseModal.vue'

// Store and composables
const authStore = useAuthStore()
const { userInitials } = useUser()

// State
const isUpdating = ref(false)
const isChangingPassword = ref(false)
const showChangePassword = ref(false)

const profileForm = ref({
  name: '',
  email: '',
  phone: '',
  department: ''
})

const passwordForm = ref({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Computed properties
const currentUser = computed(() => authStore.currentUser)
const role = computed(() => currentUser.value?.role || localStorage.getItem('mock-role'))

const memberSince = computed(() => {
  return 'January 2024'
})

const lastLogin = computed(() => {
  return new Date().toLocaleDateString()
})

const roleSpecificTitle = computed(() => {
  switch (role.value) {
    case 'admin':
      return 'Administrator Information'
    case 'clinic':
      return 'Clinic Information'
    case 'sales':
      return 'Sales Information'
    default:
      return 'User Information'
  }
})

// Methods
const updateProfile = async () => {
  isUpdating.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Profile updated:', profileForm.value)
    // Show success message
  } catch (error) {
    console.error('Failed to update profile:', error)
  } finally {
    isUpdating.value = false
  }
}

const changePassword = async () => {
  if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
    alert('New passwords do not match')
    return
  }

  isChangingPassword.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    console.log('Password changed')
    showChangePassword.value = false
    passwordForm.value = {
      currentPassword: '',
      newPassword: '',
      confirmPassword: ''
    }
    // Show success message
  } catch (error) {
    console.error('Failed to change password:', error)
  } finally {
    isChangingPassword.value = false
  }
}

// Initialize form data
onMounted(() => {
  if (currentUser.value) {
    profileForm.value = {
      name: currentUser.value.name || '',
      email: currentUser.value.email || '',
      phone: '+1 (555) 123-4567',
      department: role.value === 'admin' ? 'Administration' :
                 role.value === 'clinic' ? 'Medical' :
                 role.value === 'sales' ? 'Sales & Marketing' : 'General'
    }
  }
})
</script> 
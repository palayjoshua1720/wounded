<template>
  <div class="space-y-6">
    <!-- Header and Create Order -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order Management</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage and track all orders</p>
      </div>
      <button
        @click="showCreateForm = true"
        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
      >
        <PlusIcon class="w-4 h-4 mr-2" />
        Create Order
      </button>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
      <div class="flex-1">
        <div class="relative">
          <MagnifyingGlassIcon class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Search orders..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          />
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <FunnelIcon class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        <select
          v-model="statusFilter"
          class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
        >
          <option value="all">All Status</option>
          <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}</option>
        </select>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Clinic / Patient</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.id }}</div>
                <div v-if="order.trackingNumber" class="text-sm text-gray-500 dark:text-gray-400">Track: {{ order.trackingNumber }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.clinicName }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ order.patientName }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ order.productName }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ order.brand }} - {{ order.size }} (x{{ order.quantity }})</div>
                <div v-if="order.serials && order.serials.length > 0" class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  Serials: {{ order.serials.join(', ') }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.status)]">
                  <component :is="getStatusIcon(order.status)" class="w-4 h-4" />
                  <span class="ml-1 capitalize">{{ order.status }}</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                {{ formatDate(order.createdAt) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button @click="showOrderDetails(order)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                  <EyeIcon class="w-4 h-4" />
                </button>
                <div class="inline-flex space-x-1">
                  <button
                    v-if="order.status === 'pending'"
                    @click="updateOrderStatus(order.id, 'acknowledged')"
                    class="px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-900/30"
                  >
                    Acknowledge
                  </button>
                  <button
                    v-if="order.status === 'acknowledged'"
                    @click="updateOrderStatus(order.id, 'shipped')"
                    class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/30"
                  >
                    Ship
                  </button>
                  <button
                    v-if="order.status === 'shipped'"
                    @click="updateOrderStatus(order.id, 'delivered')"
                    class="px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/30"
                  >
                    Deliver
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredOrders.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-400 dark:text-gray-500">No orders found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- View Order Modal -->
    <BaseModal v-model="showOrderModal" title="Order Details">
      <template v-if="selectedOrder">
        <div class="space-y-4">
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Order ID:</span>
            <span class="text-base font-semibold text-gray-900 dark:text-white">{{ selectedOrder.id }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Clinic:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ selectedOrder.clinicName }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Patient:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ selectedOrder.patientName }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Product:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ selectedOrder.productName }} ({{ selectedOrder.brand }}, {{ selectedOrder.size }})</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ selectedOrder.quantity }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status:</span>
            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(selectedOrder.status)]">
              <component :is="getStatusIcon(selectedOrder.status)" class="w-4 h-4" />
              <span class="ml-1 capitalize">{{ selectedOrder.status }}</span>
            </span>
          </div>
          <div class="flex items-center space-x-4" v-if="selectedOrder.trackingNumber">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Tracking #:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ selectedOrder.trackingNumber }}</span>
          </div>
          <div class="flex items-center space-x-4" v-if="selectedOrder.serials && selectedOrder.serials.length">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Serials:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ selectedOrder.serials.join(', ') }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Created:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ formatDate(selectedOrder.createdAt) }}</span>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated:</span>
            <span class="text-base text-gray-900 dark:text-white">{{ formatDate(selectedOrder.updatedAt) }}</span>
          </div>
        </div>
      </template>
    </BaseModal>

    <!-- Create Order Modal -->
    <BaseModal v-model="showCreateForm" title="Create Order">
      <form @submit.prevent="handleCreateOrder" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Clinic Name<span class="text-red-500">*</span></label>
            <input v-model="newOrder.clinicName" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patient Name<span class="text-red-500">*</span></label>
            <input v-model="newOrder.patientName" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name<span class="text-red-500">*</span></label>
            <input v-model="newOrder.productName" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
            <input v-model="newOrder.brand" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Size</label>
            <input v-model="newOrder.size" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity<span class="text-red-500">*</span></label>
            <input v-model.number="newOrder.quantity" type="number" min="1" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status<span class="text-red-500">*</span></label>
            <select v-model="newOrder.status" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
              <option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</label>
            <input v-model="newOrder.trackingNumber" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Serials (comma separated)</label>
            <input v-model="newOrder.serialsInput" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="e.g. GM001,GM002" />
          </div>
        </div>
        <div class="flex justify-end space-x-2 pt-2">
          <button type="button" @click="showCreateForm = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create</button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  EyeIcon,
  CheckCircleIcon,
  TruckIcon,
  CubeIcon
} from '@heroicons/vue/24/outline'

const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'acknowledged', label: 'Acknowledged' },
  { value: 'shipped', label: 'Shipped' },
  { value: 'delivered', label: 'Delivered' },
  { value: 'cancelled', label: 'Cancelled' },
]

const orders = ref<Order[]>([
  {
    id: 'ORD-001',
    clinicId: '1',
    clinicName: "St. Mary's Hospital",
    patientId: '1',
    patientName: 'John Doe',
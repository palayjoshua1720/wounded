<template>
	<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
		<div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
			<div class="flex items-center justify-between">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Inventory Management</h2>
				<div class="flex items-center space-x-4">
					<div class="relative">
						<MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" />
						<input
						type="text"
						placeholder="Search by serial or brand..."
						v-model="searchTerm"
						class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<select
						v-model="statusFilter"
						class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
					>
						<option value="all">All Status</option>
						<option value="expected">Expected</option>
						<option value="delivered">Delivered</option>
						<option value="used">Used</option>
						<option value="unused">Unused</option>
						<option value="expired">Expired</option>
						<option value="returned">Returned</option>
					</select>
					<select
						v-model="clinicFilter"
						class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
					>
						<option value="all">All Clinics</option>
						<option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="overflow-x-auto">
			<table class="w-full">
				<thead class="bg-gray-50 dark:bg-gray-700">
					<tr>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Serial Number
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Brand & Size
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Clinic
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Status
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Expiry Date
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Usage Count
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
						Actions
						</th>
					</tr>
				</thead>
				<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
					<tr v-for="item in filteredInventory" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm font-medium text-gray-900 dark:text-white">
								{{ item.serialNumber }}
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</div>
							<div class="text-sm text-gray-500 dark:text-gray-400">{{ getSizeName(item.brandId, item.sizeId) }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900 dark:text-white">{{ getClinicName(item.clinicId) }}</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<span :class="`inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(item.status)}`">
								<component :is="getStatusIcon(item.status)" class="w-4 h-4" />
								<span class="capitalize">{{ item.status.replace('_', ' ') }}</span>
							</span>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div :class="`text-sm ${isExpiringSoon(item.expiryDate) ? 'text-red-600 font-medium' : 'text-gray-900 dark:text-white'}`">
								{{ item.expiryDate ? formatDate(item.expiryDate) : 'N/A' }}
								<div v-if="isExpiringSoon(item.expiryDate)" class="text-xs text-red-500">Expiring Soon</div>
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap">
							<div class="text-sm text-gray-900 dark:text-white">
								{{ item.usageLogs.length }} use{{ item.usageLogs.length !== 1 ? 's' : '' }}
							</div>
						</td>
						<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
							<div class="flex items-center space-x-2">
								<button
								@click="$emit('view-item', item)"
								class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center space-x-1"
								>
									<EyeIcon class="w-4 h-4" />
									<span>View</span>
								</button>
								<button
								@click="$emit('edit-item', item)"
								class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 flex items-center space-x-1"
								>
									<PencilSquareIcon class="w-4 h-4" />
									<span>Edit</span>
								</button>
								<button
								@click="$emit('delete-item', item)"
								class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 flex items-center space-x-1"
								>
									<TrashIcon class="w-4 h-4" />
									<span>Delete</span>
								</button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div v-if="filteredInventory.length === 0" class="text-center py-12">
			<CubeIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
			<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No inventory items found</h3>
			<p class="text-gray-600 dark:text-gray-400">No items match your current filters.</p>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import {
  MagnifyingGlassIcon,
  CubeIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  ClockIcon,
  EyeIcon,
  PencilSquareIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

interface InventoryItem {
  id: string
  serialNumber: string
  brandId: string
  sizeId: string
  clinicId: string
  status: string
  expiryDate?: string
  usageLogs: any[]
}

interface Brand {
  id: string
  name: string
  sizes: Array<{ id: string; size: string }>
}

interface Clinic {
  id: string
  name: string
}

interface Props {
  inventory: InventoryItem[]
  brands: Brand[]
  clinics: Clinic[]
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'view-item': [item: InventoryItem]
  'edit-item': [item: InventoryItem]
  'delete-item': [item: InventoryItem]
}>()

const searchTerm = ref('')
const statusFilter = ref('all')
const clinicFilter = ref('all')

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'delivered':
      return CheckCircleIcon
    case 'used':
      return CheckCircleIcon
    case 'expired':
      return ExclamationTriangleIcon
    case 'expected':
      return ClockIcon
    default:
      return CubeIcon
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'delivered':
      return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    case 'used':
      return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    case 'expired':
      return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    case 'expected':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
    case 'partially_used':
      return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
    case 'reassigned':
      return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400'
    case 'returned':
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
  }
}

const getBrandName = (brandId: string) => {
  return props.brands.find(b => b.id === brandId)?.name || 'Unknown Brand'
}

const getClinicName = (clinicId: string) => {
  return props.clinics.find(c => c.id === clinicId)?.name || 'Unknown Clinic'
}

const getSizeName = (brandId: string, sizeId: string) => {
  const brand = props.brands.find(b => b.id === brandId)
  const size = brand?.sizes.find(s => s.id === sizeId)
  return size?.size || 'Unknown Size'
}

const filteredInventory = computed(() => {
  return props.inventory.filter(item => {
    const matchesSearch = item.serialNumber.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
    getBrandName(item.brandId).toLowerCase().includes(searchTerm.value.toLowerCase())
    const matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value
    const matchesClinic = clinicFilter.value === 'all' || item.clinicId === clinicFilter.value
    
    return matchesSearch && matchesStatus && matchesClinic
  })
})

const isExpiringSoon = (expiryDate?: string) => {
  if (!expiryDate) return false
  const expiry = new Date(expiryDate)
  const thirtyDaysFromNow = new Date()
  thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30)
  return expiry <= thirtyDaysFromNow
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleDateString()
}
</script> 
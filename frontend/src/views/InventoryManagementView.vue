<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inventory & Serial Tracking</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage inventory and track serial numbers</p>
			</div>
			<div class="flex items-center space-x-3">
				<button
				@click="showUsageLogForm = !showUsageLogForm"
				class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
				>
				<PlusIcon class="w-4 h-4 mr-2" />
				Log Usage
				</button>
				<button
					class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
					@click="showUploadModal = true"
				>
					<ArrowUpTrayIcon class="w-4 h-4 mr-2" />
					Upload Delivery File
				</button>
			</div>
		</div>

		<!-- Inline Usage Log Form (toggle) -->
		<div v-if="showUsageLogForm">
			<UsageLogForm
				v-if="showUsageLogForm"
				:inventory-items="inventory"
				:clinicians="clinicians"
				:brands="brands"
				@submit="handleUsageLogSubmitAndHide"
				@bulk-upload="handleBulkUpload"
				@cancel="handleUsageLogCancelAndHide"
			/>
		</div>

		<!-- Inventory List Component -->
		<InventoryList 
		:inventory="inventory" 
		:brands="brands" 
		:clinics="clinics"
		@view-item="handleViewItem"
		@edit-item="handleEditItem"
		@delete-item="handleDeleteItem"
		/>

		<!-- Upload Modal -->
		<BaseModal v-model="showUploadModal" title="Upload Delivery File">
			<div class="space-y-4">
				<div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center">
					<DocumentTextIcon class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4" />
					<p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Upload manufacturer delivery file</p>
					<p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
						Supports PDF, CSV, and Excel files for serial extraction
					</p>
					<input
					type="file"
					accept=".pdf,.csv,.xlsx,.xls"
					class="hidden"
					id="file-upload"
					/>
					<label
					for="file-upload"
					class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer"
					>
					<ArrowUpTrayIcon class="w-4 h-4 mr-2" />
					Choose File
					</label>
				</div>
			</div>
			<template #actions>
			<button
				@click="showUploadModal = false"
				class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
			>
				Cancel
			</button>
			<button
				class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
			>
				Process File
			</button>
			</template>
		</BaseModal>

		<!-- Item Details Modal -->
		<BaseModal v-model="showItemModal" title="Inventory Item Details">
			<div v-if="selectedItem" class="space-y-6 p-6">
				<!-- Item Information (always shown) -->
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="space-y-4">
						<div class="flex items-center space-x-3">
							<CubeIcon class="w-5 h-5 text-blue-600" />
							<div>
								<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Brand & Size</p>
								<p class="text-gray-900 dark:text-white">{{ getBrandName(selectedItem.brandId) }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-400">{{ getSizeName(selectedItem.brandId, selectedItem.sizeId) }}</p>
							</div>
						</div>
						<div class="flex items-center space-x-3">
							<MapPinIcon class="w-5 h-5 text-green-600" />
							<div>
								<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</p>
								<p class="text-gray-900 dark:text-white">{{ getClinicName(selectedItem.clinicId) }}</p>
							</div>
						</div>
						<div class="flex items-center space-x-3">
							<CheckCircleIcon class="w-5 h-5 text-purple-600" />
							<div>
								<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</p>
								<span :class="`inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(selectedItem.status)}`">
									{{ selectedItem.status.replace('_', ' ').charAt(0).toUpperCase() + selectedItem.status.replace('_', ' ').slice(1) }}
								</span>
							</div>
						</div>
					</div>
					<div class="space-y-4">
						<div class="flex items-center space-x-3">
							<DocumentTextIcon class="w-5 h-5 text-orange-600" />
							<div>
								<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order ID</p>
								<p class="text-gray-900 dark:text-white font-mono">#{{ selectedItem.orderId ? selectedItem.orderId.slice(-6).toUpperCase() : 'N/A' }}</p>
							</div>
						</div>
						<div v-if="selectedItem.expiryDate" class="flex items-center space-x-3">
							<CalendarIcon class="w-5 h-5 text-red-600" />
							<div>
								<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Expiry Date</p>
								<p :class="`text-gray-900 dark:text-white ${isExpiringSoon(selectedItem.expiryDate) ? 'text-red-600 font-medium' : ''}`">
									{{ formatDate(selectedItem.expiryDate) }}
									<span v-if="isExpiringSoon(selectedItem.expiryDate)" class="text-xs text-red-500 block">Expiring Soon</span>
								</p>
							</div>
						</div>
						<div v-if="selectedItem.deliveryDate" class="flex items-center space-x-3">
							<ClockIcon class="w-5 h-5 text-gray-600" />
							<div>
								<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date</p>
								<p class="text-gray-900 dark:text-white">{{ formatDate(selectedItem.deliveryDate) }}</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Status-specific content -->
				<div v-if="selectedItem.status === 'expected'">
					<div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg text-yellow-800 dark:text-yellow-200">
						<p>This item is expected but not yet delivered. Please update the status when received.</p>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'delivered'">
					<div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-blue-800 dark:text-blue-200">
						<p>This item has been delivered and is ready for use or further status update.</p>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'used'">
					<div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg text-green-800 dark:text-green-200 mb-4">
						<p>This item has been fully used. See usage history below.</p>
					</div>
					<div v-if="selectedItem.usageLogs && selectedItem.usageLogs.length > 0">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage History</h3>
						<div class="overflow-x-auto">
							<table class="w-full">
								<thead class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Patient</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date of Service</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Wound Site</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clinician</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
									</tr>
								</thead>
								<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
									<tr v-for="log in selectedItem.usageLogs" :key="log.id">
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.patientName }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ formatDate(log.dateOfService) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.woundSite }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getClinicianName(log.clinicianId) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.notes || 'N/A' }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'partially_used'">
					<div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg text-purple-800 dark:text-purple-200 mb-4">
						<p>This item has been partially used. See partial usage history below.</p>
					</div>
					<div v-if="selectedItem.usageLogs && selectedItem.usageLogs.length > 0">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Partial Usage History</h3>
						<div class="overflow-x-auto">
							<table class="w-full">
								<thead class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Patient</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date of Service</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Wound Site</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clinician</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
									</tr>
								</thead>
								<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
									<tr v-for="log in selectedItem.usageLogs" :key="log.id">
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.patientName }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ formatDate(log.dateOfService) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.woundSite }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getClinicianName(log.clinicianId) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ log.notes || 'N/A' }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'returned'">
					<div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg text-gray-800 dark:text-gray-200">
						<p>This item has been returned to the manufacturer or supplier. No further actions are available.</p>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'reassigned'">
					<div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg text-orange-800 dark:text-orange-200">
						<p>This item has been reassigned to another patient or clinic. Please check reassignment records for details.</p>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'expired'">
					<div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg text-red-800 dark:text-red-200">
						<p>This item has expired and should not be used.</p>
					</div>
				</div>
				<div v-else-if="selectedItem.status === 'unused'">
					<div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-blue-800 dark:text-blue-200">
						<p>This item is unused and available for assignment.</p>
					</div>
				</div>
			</div>
			<template #actions>
				<div class="border-t border-gray-200 pt-6 flex flex-col space-y-2 w-full">
					<h3 v-if="selectedItem && selectedItem.status !== 'used' && selectedItem.status !== 'expired' && selectedItem.status !== 'returned' && selectedItem.status !== 'reassigned' && selectedItem.status !== 'unused'" class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Status</h3>
					<div class="flex space-x-3">
						<button
							v-if="selectedItem && selectedItem.status === 'expected'"
							@click="handleStatusUpdate(selectedItem.id, 'delivered')"
							class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
						>
							Mark Delivered
						</button>
						<button
							v-if="selectedItem && ['delivered', 'partially_used'].includes(selectedItem.status)"
							@click="handleStatusUpdate(selectedItem.id, 'used')"
							class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
						>
							<CheckCircleIcon class="w-4 h-4 mr-2" />
							Mark Used
						</button>
						<button
							v-if="selectedItem && ['delivered', 'partially_used'].includes(selectedItem.status)"
							@click="handleStatusUpdate(selectedItem.id, 'reassigned')"
							class="flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
						>
							<ArrowPathIcon class="w-4 h-4 mr-2" />
							Mark Reassigned
						</button>
						<button
							v-if="selectedItem && ['delivered', 'partially_used'].includes(selectedItem.status)"
							@click="handleStatusUpdate(selectedItem.id, 'returned')"
							class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
						>
							<ArrowUturnLeftIcon class="w-4 h-4 mr-2" />
							Mark Returned
						</button>
					</div>
				</div>
			</template>
		</BaseModal>

		<!-- Edit Item Modal -->
		<BaseModal v-model="showEditModal" title="Edit Inventory Item">
			<div v-if="editingItem" class="space-y-6">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Serial Number
						</label>
						<input
						v-model="editingItem.serialNumber"
						type="text"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						readonly
						/>
						<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Serial number cannot be changed</p>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Status
						</label>
						<select
						v-model="editingItem.status"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="expected">Expected</option>
							<option value="delivered">Delivered</option>
							<option value="used">Used</option>
							<option value="returned">Returned</option>
							<option value="expired">Expired</option>
						</select>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Brand
						</label>
						<select
						v-model="editingItem.brandId"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
						<option v-for="brand in brands" :key="brand.id" :value="brand.id">
							{{ brand.name }}
						</option>
						</select>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Size
						</label>
						<select
						v-model="editingItem.sizeId"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
						<option v-for="size in getAvailableSizes(editingItem.brandId)" :key="size.id" :value="size.id">
							{{ size.size }}
						</option>
						</select>
					</div>
					
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Delivery Date
						</label>
						<input
						v-model="editingItem.deliveryDate"
						type="date"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Clinic
						</label>
						<select
						v-model="editingItem.clinicId"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
						<option value="">No Clinic Assigned</option>
						<option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
							{{ clinic.name }}
						</option>
						</select>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Expiry Date
						</label>
						<input
						v-model="editingItem.expiryDate"
						type="date"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Order ID
						</label>
						<input
						v-model="editingItem.orderId"
						type="text"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						placeholder="Enter order ID"
						/>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Received Date
						</label>
						<input
						v-model="editingItem.receivedDate"
						type="date"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>

					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Used Date
						</label>
						<input
						v-model="editingItem.usedDate"
						type="date"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>

					<div class="md:col-span-2">
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Product Name
						</label>
						<input
						v-model="editingItem.productName"
						type="text"
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						placeholder="Enter product name"
						/>
					</div>
				</div>
			</div>

			<template #actions>
				<button
				@click="showEditModal = false"
				class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
				>
					Cancel
				</button>
				<button
				@click="handleSaveEdit"
				class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
				>
					Save Changes
				</button>
			</template>
		</BaseModal>

		<!-- Delete Item Modal -->
		<BaseModal v-model="showDeleteModal" title="Delete Inventory Item">
			<div class="p-4">
				<p>Are you sure you want to delete inventory item <span class="font-mono">{{ itemToDelete?.serialNumber }}</span>? This action cannot be undone.</p>
			</div>
			<template #actions>
				<button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
				<button @click="confirmDeleteItem" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import InventoryList from '@/components/Inventory/InventoryList.vue'
import UsageLogForm from '@/components/Inventory/UsageLogForm.vue'
import { ArrowUpTrayIcon, PlusIcon, ClockIcon, CubeIcon, CheckCircleIcon, ExclamationTriangleIcon, DocumentTextIcon, ArrowPathIcon, ArrowUturnLeftIcon, MapPinIcon, CalendarIcon } from '@heroicons/vue/24/outline'

interface InventoryItem {
	id: string
	serialNumber: string
	brandId: string
	sizeId: string
	clinicId: string
	status: string
	expiryDate?: string
	usageLogs: any[]
	productName?: string
	orderId?: string
	receivedDate?: string
	usedDate?: string
	deliveryDate?: string
}

// Replace inventory data with React sample data
const inventory = ref<InventoryItem[]>([
  {
    id: 'inv-1',
    serialNumber: 'SN001234',
    brandId: 'brand-1',
    sizeId: 'size-1',
    orderId: 'order-1',
    clinicId: 'clinic-1',
    status: 'expected',
    expiryDate: '2024-12-31',
    deliveryDate: '',
    usageLogs: []
  },
  {
    id: 'inv-2',
    serialNumber: 'SN001235',
    brandId: 'brand-1',
    sizeId: 'size-1',
    orderId: 'order-1',
    clinicId: 'clinic-1',
    status: 'delivered',
    expiryDate: '2024-12-31',
    deliveryDate: '2024-01-18',
    usageLogs: []
  },
  {
    id: 'inv-3',
    serialNumber: 'SN001236',
    brandId: 'brand-2',
    sizeId: 'size-5',
    orderId: 'order-2',
    clinicId: 'clinic-2',
    status: 'used',
    expiryDate: '2024-11-15',
    deliveryDate: '2024-02-10',
    usageLogs: [
      {
        id: 'usage-1',
        serialNumber: 'SN001236',
        patientName: 'Jane Doe',
        dateOfService: '2024-03-01',
        woundSite: 'Right leg',
        clinicianId: '1',
        notes: 'Healed well'
      },
      {
        id: 'usage-2',
        serialNumber: 'SN001236',
        patientName: 'John Smith',
        dateOfService: '2024-03-15',
        woundSite: 'Left arm',
        clinicianId: '2',
        notes: 'Follow-up required'
      }
    ]
  },
  {
    id: 'inv-4',
    serialNumber: 'SN001237',
    brandId: 'brand-2',
    sizeId: 'size-6',
    orderId: 'order-3',
    clinicId: 'clinic-2',
    status: 'returned',
    expiryDate: '2024-10-10',
    deliveryDate: '2024-03-01',
    usageLogs: []
  },
  {
    id: 'inv-5',
    serialNumber: 'SN001238',
    brandId: 'brand-3',
    sizeId: 'size-8',
    orderId: 'order-4',
    clinicId: 'clinic-1',
    status: 'expired',
    expiryDate: '2024-04-01',
    deliveryDate: '2023-04-01',
    usageLogs: []
  },
  {
    id: 'inv-6',
    serialNumber: 'SN001239',
    brandId: 'brand-3',
    sizeId: 'size-9',
    orderId: 'order-5',
    clinicId: 'clinic-2',
    status: 'partially_used',
    expiryDate: '2024-09-01',
    deliveryDate: '2024-05-01',
    usageLogs: [
      {
        id: 'usage-3',
        serialNumber: 'SN001239',
        patientName: 'Alice Brown',
        dateOfService: '2024-06-01',
        woundSite: 'Back',
        clinicianId: '3',
        notes: 'Partial use'
      }
    ]
  },
  {
    id: 'inv-7',
    serialNumber: 'SN001240',
    brandId: 'brand-1',
    sizeId: 'size-2',
    orderId: 'order-6',
    clinicId: 'clinic-1',
    status: 'reassigned',
    expiryDate: '2024-08-01',
    deliveryDate: '2024-06-01',
    usageLogs: []
  },
  {
    id: 'inv-8',
    serialNumber: 'SN001241',
    brandId: 'brand-2',
    sizeId: 'size-7',
    orderId: 'order-7',
    clinicId: 'clinic-2',
    status: 'unused',
    expiryDate: '2024-12-01',
    deliveryDate: '',
    usageLogs: []
  }
])

const searchTerm = ref('')
const statusFilter = ref('all')
const brandFilter = ref('all')
const showUploadModal = ref(false)
const showItemModal = ref(false)
const selectedItem = ref<InventoryItem | null>(null)
const showUsageLogForm = ref(false)
const showEditModal = ref(false)
const editingItem = ref<InventoryItem | null>(null)
const showDeleteModal = ref(false)
const itemToDelete = ref<InventoryItem | null>(null)

// Mock data for brands, clinics, and clinicians
const brands = ref([
  {
    id: 'brand-1',
    name: 'DermaGraft Pro',
    manufacturerId: 'mfg-1',
    productType: 'graft',
    asp: 1250.00,
    mue: 4,
    description: 'Advanced dermal graft for wound healing',
    isActive: true,
    sizes: [
      { id: 'size-1', size: '2cm x 2cm', area: 4, price: 1250.00 },
      { id: 'size-2', size: '2cm x 3cm', area: 6, price: 1875.00 },
      { id: 'size-3', size: '3cm x 3cm', area: 9, price: 2812.50 },
      { id: 'size-4', size: '4cm x 4cm', area: 16, price: 5000.00 }
    ]
  },
  {
    id: 'brand-2',
    name: 'HealMatrix Advanced',
    manufacturerId: 'mfg-2',
    productType: 'graft',
    asp: 980.00,
    mue: 6,
    description: 'Matrix-based healing solution',
    isActive: true,
    sizes: [
      { id: 'size-5', size: '2cm x 2cm', area: 4, price: 980.00 },
      { id: 'size-6', size: '2cm x 3cm', area: 6, price: 1470.00 },
      { id: 'size-7', size: '3cm x 4cm', area: 12, price: 2940.00 }
    ]
  },
  {
    id: 'brand-3',
    name: 'Biolab Skin Graft',
    manufacturerId: 'biolab',
    productType: 'graft',
    asp: 850.00,
    mue: 5,
    description: 'Bioengineered skin graft technology',
    isActive: true,
    sizes: [
      { id: 'size-8', size: '2cm x 2cm', area: 4, price: 850.00 },
      { id: 'size-9', size: '3cm x 3cm', area: 9, price: 1912.50 },
      { id: 'size-10', size: '4cm x 3cm', area: 12, price: 2550.00 }
    ]
  }
])

const clinics = ref([
  {
    id: 'clinic-1',
    name: 'Metro Wound Care Center',
    address: '123 Medical Plaza, City, ST 12345',
    phone: '(555) 123-4567',
    email: 'contact@metrowound.com',
    contactPerson: 'Dr. Sarah Johnson',
    licenseNumber: 'WC-12345',
    taxId: '12-3456789',
    isActive: true
  },
  {
    id: 'clinic-2',
    name: 'Advanced Healing Institute',
    address: '456 Healthcare Blvd, City, ST 12345',
    phone: '(555) 987-6543',
    email: 'info@advancedhealing.com',
    contactPerson: 'Dr. Michael Chen',
    licenseNumber: 'WC-67890',
    taxId: '98-7654321',
    isActive: true
  }
])

const clinicians = ref([
	{ id: '1', name: 'Dr. Sarah Johnson' },
	{ id: '2', name: 'Dr. Michael Chen' },
	{ id: '3', name: 'Dr. Emily Rodriguez' },
	{ id: '4', name: 'Dr. David Thompson' }
])

const uniqueBrands = computed(() => [...new Set(inventory.value.map(item => getBrandName(item.brandId)))])

const filteredInventory = computed(() => {
	return inventory.value.filter(item => {
		const matchesSearch = item.serialNumber.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
		(item.productName?.toLowerCase() || '').includes(searchTerm.value.toLowerCase()) ||
		getBrandName(item.brandId).toLowerCase().includes(searchTerm.value.toLowerCase())
		const matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value
		const matchesBrand = brandFilter.value === 'all' || getBrandName(item.brandId) === brandFilter.value
		return matchesSearch && matchesStatus && matchesBrand
	})
})

function getBrandName(brandId: string) {
	const brand = brands.value.find(b => b.id === brandId)
	return brand?.name || 'Unknown Brand'
}

function getSizeName(brandId: string, sizeId: string) {
	const brand = brands.value.find(b => b.id === brandId)
	const size = brand?.sizes.find(s => s.id === sizeId)
	return size?.size || 'Unknown Size'
}

function getAvailableSizes(brandId: string) {
	const brand = brands.value.find(b => b.id === brandId)
	return brand?.sizes || []
}

function getStatusColor(status: string) {
	switch (status) {
		case 'expected': return 'bg-yellow-100 text-yellow-800'
		case 'delivered': return 'bg-blue-100 text-blue-800'
		case 'used': return 'bg-green-100 text-green-800'
		case 'returned': return 'bg-purple-100 text-purple-800'
		case 'expired': return 'bg-red-100 text-red-800'
		default: return 'bg-gray-100 text-gray-800'
	}
}

function getStatusIcon(status: string) {
	switch (status) {
		case 'expected': return ClockIcon
		case 'delivered': return CubeIcon
		case 'used': return CheckCircleIcon
		case 'expired': return ExclamationTriangleIcon
		default: return null
	}
}

function handleStatusUpdate(itemId: string, newStatus: InventoryItem['status']) {
	inventory.value = inventory.value.map(item => 
		item.id === itemId 
		? { ...item, status: newStatus }
		: item
	)
}

function formatDate(dateString?: string) {
	if (!dateString) return 'N/A'
	return new Date(dateString).toLocaleDateString()
}

function isExpiringSoon(expiryDateString?: string) {
	if (!expiryDateString) return false
	const expiryDate = new Date(expiryDateString)
	const today = new Date()
	const diffTime = Math.abs(expiryDate.getTime() - today.getTime())
	const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
	return diffDays <= 30 && diffDays > 0
}

function getClinicName(clinicId: string) {
	const clinic = clinics.value.find(c => c.id === clinicId)
	return clinic?.name || 'Unknown Clinic'
}

function getClinicianName(clinicianId: string) {
	const clinician = clinicians.value.find(c => c.id === clinicianId)
	return clinician?.name || 'Unknown Clinician'
}

// Event handlers
function handleViewItem(item: InventoryItem) {
	selectedItem.value = item
	showItemModal.value = true
}

function handleEditItem(item: InventoryItem) {
	editingItem.value = { ...item }
	showEditModal.value = true
}

function handleSaveEdit() {
	if (editingItem.value) {
		// Find and update the item in the inventory
		const itemIndex = inventory.value.findIndex(item => item.id === editingItem.value!.id)
		if (itemIndex !== -1) {
		inventory.value[itemIndex] = { ...editingItem.value }
		console.log('Item updated:', inventory.value[itemIndex])
		}
	}
	showEditModal.value = false
	editingItem.value = null
}

function handleUsageLogSubmit(usageLog: any) {
	console.log('Usage log submitted:', usageLog)

	// Find the inventory item by serial number
	const itemIndex = inventory.value.findIndex(item => item.serialNumber === usageLog.serialNumber)

	if (itemIndex !== -1) {
		// Create a new usage log entry
		const newUsageLog = {
			id: Date.now().toString(),
			serialNumber: usageLog.serialNumber,
			patientName: usageLog.patientName,
			dateOfService: usageLog.dateOfService,
			woundSite: usageLog.woundSite,
			clinicianId: usageLog.clinicianId,
			notes: usageLog.notes,
			createdAt: new Date().toISOString()
		}
		
		// Add the usage log to the item
		inventory.value[itemIndex].usageLogs.push(newUsageLog)
		
		// Do NOT update the status to 'used' after logging usage
		// inventory.value[itemIndex].status = 'used'
		// inventory.value[itemIndex].usedDate = usageLog.dateOfService
		
		console.log('Inventory updated:', inventory.value[itemIndex])
	}

	showUsageLogForm.value = false
}

function handleUsageLogCancel() {
	showUsageLogForm.value = false
}

function handleBulkUpload(file: File) {
	console.log('Bulk upload file:', file)
	// Implement bulk upload functionality
}

function handleUsageLogSubmitAndHide(log: any) {
	handleUsageLogSubmit(log)
	showUsageLogForm.value = false
}
function handleUsageLogCancelAndHide() {
	handleUsageLogCancel()
	showUsageLogForm.value = false
}

function handleDeleteItem(item: InventoryItem) {
	itemToDelete.value = item
	showDeleteModal.value = true
}

function confirmDeleteItem() {
	if (itemToDelete.value) {
		inventory.value = inventory.value.filter(i => i.id !== itemToDelete.value!.id)
		showDeleteModal.value = false
		itemToDelete.value = null
	}
}

// Watch for selectedItem changes to show modal
import { watch } from 'vue'
	watch(selectedItem, (newItem) => {
	showItemModal.value = !!newItem
})
</script>
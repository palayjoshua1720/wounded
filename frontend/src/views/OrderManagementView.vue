<template>
	<div class="space-y-6">
		<!-- Header and Create Order -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order Management</h1>
				<p class="text-gray-600 dark:text-gray-400">Manage and track all orders</p>
			</div>
			<button
				@click="showCreateForm = !showCreateForm"
				class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
			>
				<PlusIcon class="w-4 h-4 mr-2" />
				{{ showCreateForm ? 'Cancel Order' : 'Create Order' }}
			</button>
		</div>

		<!-- Inline Create Order Form (toggle) -->
		<div v-if="showCreateForm" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
			<form @submit.prevent="handleCreateOrder" class="space-y-6">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<!-- Clinic Selection -->
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ordering Clinic <span class="text-red-500">*</span></label>
						<select v-model="newOrder.clinicId" @change="onClinicChange" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
							<option value="">Select Clinic</option>
							<option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">{{ clinic.name }}</option>
						</select>
					</div>
					<!-- Clinician Selection -->
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ordering Clinician <span class="text-red-500">*</span></label>
						<select v-model="newOrder.clinicianId" :disabled="!newOrder.clinicId" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
							<option value="">Select Clinician</option>
							<option v-for="clinician in filteredClinicians" :key="clinician.id" :value="clinician.id">{{ clinician.name }}</option>
						</select>
					</div>
				</div>

				<!-- Patient Selection -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Intended Patient</label>
					<select v-model="newOrder.patientId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
						<option value="">Select Patient (Optional)</option>
						<option v-for="patient in filteredPatients" :key="patient.id" :value="patient.id">{{ patient.name }}</option>
					</select>
					<div v-if="selectedPatient">
						<div v-if="isPatientEligible" class="mt-2 p-3 rounded-lg flex items-center space-x-2 bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-400">
							<CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
							<span class="text-sm font-medium text-green-800 dark:text-green-400">IVR Status: {{ selectedPatient.ivrStatus.replace('_', ' ').toUpperCase() }}</span>
						</div>
						<div v-else class="mt-2 p-3 rounded-lg flex items-center space-x-2 bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-400">
							<ExclamationTriangleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
							<span class="text-sm font-medium text-red-800 dark:text-red-400">IVR Status: {{ selectedPatient.ivrStatus.replace('_', ' ').toUpperCase() }}</span>
						</div>
					</div>
				</div>
				
				<!-- Order Items -->
				<div>
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white">Order Items</h3>
						<button type="button" @click="addOrderItem" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
							<PlusIcon class="w-4 h-4" />
							<span>Add Item</span>
						</button>
					</div>

					<p class="text-xs text-yellow-600 dark:text-yellow-400 mb-2">Note: You may exceed the MUE (Maximum Units per Episode) for a product, but this will be flagged for review. You can still submit the order.</p>
					<div v-for="(item, idx) in newOrder.items" :key="item.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
						<div class="grid grid-cols-1 md:grid-cols-5 gap-4">
							<!-- Brand -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand <span class="text-red-500">*</span></label>
								<select v-model="item.brandId" @change="onBrandChange(idx)" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option value="">Select Brand</option>
									<option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
								</select>
							</div>

							<!-- Size -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Size <span class="text-red-500">*</span></label>
								<select v-model="item.sizeId" :disabled="!selectedBrand(idx)" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option value="">Select Size</option>
									<option v-for="size in selectedBrand(idx)?.sizes || []" :key="size.id" :value="size.id">{{ size.size }}</option>
								</select>
							</div>

							<!-- Quantity -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity <span class="text-red-500">*</span></label>
								<input v-model.number="item.quantity" type="number" min="1" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="!isQuantityValid(idx) ? 'border-yellow-300 bg-yellow-50 dark:border-yellow-400 dark:bg-yellow-900/10' : 'border-gray-300 dark:border-gray-600'" />
								<p v-if="!isQuantityValid(idx)" class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Exceeds MUE limit of {{ selectedBrand(idx)?.mue }} for this brand. You may still submit, but this will be flagged for review.</p>
							</div>

							<!-- ASP -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ASP</label>
								<input type="text" :value="item.asp && item.quantity ? `$${(item.asp * item.quantity).toFixed(2)}` : ''" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900/10" readonly />
							</div>

							<!-- Remove -->
							<div class="flex items-end">
								<button type="button" @click="removeOrderItem(idx)" class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 rounded-lg transition-colors">
									<MinusIcon class="w-4 h-4" />
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Internal Notes -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Notes</label>
					<textarea v-model="newOrder.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Add any internal notes..." />
				</div>

				<!-- Status, Date, Tracking -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
						<select v-model="newOrder.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
							<option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}</option>
						</select>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Order</label>
						<input v-model="newOrder.dateOfOrder" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</label>
						<input v-model="newOrder.trackingNumber" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
					</div>
				</div>
				<!-- Submit/Cancel -->

				<div class="flex justify-end space-x-4">
					<button type="button" @click="resetCreateForm" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
					<button type="submit" :disabled="!canSubmit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Submit Order</button>
				</div>
			</form>
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
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Clinic</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Patient</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
						<tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm font-medium text-gray-900 dark:text-white">#{{ order.id.slice(-6).toUpperCase() }}</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900 dark:text-white">{{ getClinicName(order.clinicId) }}</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900 dark:text-white">{{ order.patientName || 'Not specified' }}</div>
							</td>
							<td class="px-6 py-4">
								<div class="text-sm text-gray-900 dark:text-white">
								<div v-for="(item, idx) in order.items" :key="idx" class="mb-1">
									{{ getBrandName(item.brandId) }} × {{ item.quantity }}
								</div>
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="text-sm text-gray-900 dark:text-white">{{ formatDate(order.dateOfOrder) }}</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<span :class="['inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.status)]">
								<component :is="getStatusIcon(order.status)" class="w-4 h-4" />
								<span class="capitalize">{{ order.status }}</span>
								</span>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
								<button @click="showOrderDetails(order)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 flex items-center space-x-1">
									<EyeIcon class="w-4 h-4" />
									<span>View</span>
									<PencilSquareIcon class="w-4 h-4 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" />
									<span class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">View</span>
									<div class="inline-flex space-x-1">
										<button v-if="order.status === 'submitted'" @click="updateOrderStatus(order.id, 'acknowledged')" class="px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-900/30">Acknowledge</button>
										<button v-if="order.status === 'acknowledged'" @click="updateOrderStatus(order.id, 'shipped')" class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/30">Ship</button>
										<button v-if="order.status === 'shipped'" @click="updateOrderStatus(order.id, 'delivered')" class="px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/30">Deliver</button>
									</div>
								</button>
							</td>
						</tr>
						<tr v-if="filteredOrders.length === 0">
							<td colspan="7" class="text-center py-8 text-gray-400 dark:text-gray-500">No orders found.</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- View Order Modal -->
		<BaseModal v-model="showOrderModal" title="Order Details">
			<template v-if="selectedOrder">
				<div>
					{{ selectedOrder.id }}
				</div>
				<div class="space-y-6 p-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<CubeIcon class="w-5 h-5 text-blue-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</p>
									<p class="text-gray-900 dark:text-white">{{ getClinicName(selectedOrder.clinicId) }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<UserIcon class="w-5 h-5 text-green-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinician</p>
									<p class="text-gray-900 dark:text-white">{{ getClinicianName(selectedOrder.clinicianId) }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<UserIcon class="w-5 h-5 text-purple-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.patientName || 'Not specified' }}</p>
								</div>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<CalendarIcon class="w-5 h-5 text-orange-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Date</p>
									<p class="text-gray-900 dark:text-white">{{ formatDate(selectedOrder.dateOfOrder) }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<CheckCircleIcon class="w-5 h-5 text-indigo-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</p>
									<span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(selectedOrder.status)]">
										{{ selectedOrder.status.charAt(0).toUpperCase() + selectedOrder.status.slice(1) }}
									</span>
								</div>
							</div>
							<div v-if="selectedOrder.trackingNumber" class="flex items-center space-x-3">
								<TruckIcon class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</p>
									<p class="text-gray-900 dark:text-white font-mono">{{ selectedOrder.trackingNumber }}</p>
								</div>
							</div>
						</div>
					</div>
					<div>
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Order Items</h3>
						<div class="overflow-x-auto">
							<table class="w-full">
								<thead class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Brand</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Size</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ASP</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
									</tr>
								</thead>
								<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
									<tr v-for="(item, idx) in selectedOrder.items" :key="idx">
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getSizeName(item.brandId, item.sizeId) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.quantity }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${{ item.asp.toFixed(2) }}</td>
										<td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">${{ (item.asp * item.quantity).toFixed(2) }}</td>
									</tr>
								</tbody>
								<tfoot class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white text-right">Total Amount:</td>
										<td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-white">${{ selectedOrder.items.reduce((sum, item) => sum + (item.asp * item.quantity), 0).toFixed(2) }}</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div v-if="selectedOrder.notes">
						<div class="flex items-center space-x-2 mb-2">
							<DocumentTextIcon class="w-5 h-5 text-gray-600" />
							<h3 class="text-lg font-medium text-gray-900 dark:text-white">Notes</h3>
						</div>
						<div class="bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4">
							<p class="text-gray-700 dark:text-gray-200">{{ selectedOrder.notes }}</p>
						</div>
					</div>
					<div class="border-t border-gray-200 pt-6">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Status</h3>
						<div class="flex space-x-3">
							<button v-if="selectedOrder.status === 'submitted'" @click="updateOrderStatus(selectedOrder.id, 'acknowledged')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Mark Acknowledged</button>
							<button v-if="selectedOrder.status === 'acknowledged'" @click="updateOrderStatus(selectedOrder.id, 'shipped')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Mark Shipped</button>
							<button v-if="selectedOrder.status === 'shipped'" @click="updateOrderStatus(selectedOrder.id, 'delivered')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Mark Delivered</button>
						</div>
					</div>
				</div>
			</template>
		</BaseModal>

		<BaseModal v-model="showMueModal" title="Exceeding MUE Limit" width="sm">
			<div class="space-y-4">
				<p class="text-yellow-700 dark:text-yellow-300">One or more items exceed the MUE (Maximum Units per Episode) limit for their brand. This will be flagged for review. Do you wish to continue and submit the order?</p>
				<div class="flex justify-end space-x-2">
					<button @click="showMueModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
					<button @click="confirmMueSubmit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Confirm</button>
				</div>
			</div>
		</BaseModal>
		<!-- Delete Order Modal -->
		<BaseModal v-model="showDeleteModal" title="Delete Order">
			<div class="p-4">
				<p>Are you sure you want to delete order <span class="font-mono">#{{ orderToDelete?.id.slice(-6).toUpperCase() }}</span>? This action cannot be undone.</p>
			</div>
			<template #actions>
				<button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
				<button @click="confirmDeleteOrderModal" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
			</template>
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
	CubeIcon,
	MinusIcon,
	ExclamationTriangleIcon,
	UserIcon,
	CalendarIcon,
	DocumentTextIcon,
	TrashIcon,
	PencilSquareIcon
} from '@heroicons/vue/24/outline'

const statusOptions = [
	{ value: 'submitted', label: 'Submitted' },
	{ value: 'acknowledged', label: 'Acknowledged' },
	{ value: 'shipped', label: 'Shipped' },
	{ value: 'delivered', label: 'Delivered' },
	{ value: 'cancelled', label: 'Cancelled' },
]

const orders = ref<Order[]>([
	{
		id: 'ORD-001',
		clinicId: '1',
		clinicianId: '1',
		patientName: 'John Doe',
		dateOfOrder: '2025-01-27T10:00:00Z',
		status: 'delivered',
		notes: 'Urgent delivery',
		items: [
			{ id: '1', brandId: '1', productType: 'graft', sizeId: '1', quantity: 2, asp: 1000 },
			{ id: '2', brandId: '2', productType: 'device', sizeId: '2', quantity: 1, asp: 500, deviceType: 'monitor' }
		],
		manufacturerId: '1',
		trackingNumber: 'TRK123456',
	},
	{
		id: 'ORD-002',
		clinicId: '2',
		clinicianId: '2',
		patientName: 'Jane Smith',
		dateOfOrder: '2025-01-26T14:00:00Z',
		status: 'shipped',
		items: [
			{ id: '3', brandId: '2', productType: 'graft', sizeId: '2', quantity: 1, asp: 1200 }
		],
		manufacturerId: '2',
		trackingNumber: 'TRK789012',
	},
	{
		id: 'ORD-003',
		clinicId: '3',
		clinicianId: '3',
		patientName: 'Bob Johnson',
		dateOfOrder: '2025-01-25T16:00:00Z',
		status: 'acknowledged',
		items: [
			{ id: '4', brandId: '1', productType: 'graft', sizeId: '3', quantity: 1, asp: 900 }
		],
		manufacturerId: '1',
	},
	{
		id: 'ORD-004',
		clinicId: '1',
		clinicianId: '1',
		patientName: 'Sarah Davis',
		dateOfOrder: '2025-01-27T08:00:00Z',
		status: 'submitted',
		items: [
			{ id: '5', brandId: '1', productType: 'graft', sizeId: '1', quantity: 3, asp: 1000 }
		],
		manufacturerId: '1',
	},
	{
		id: 'ORD-005',
		clinicId: '2',
		clinicianId: '2',
		patientName: 'Eve Adams',
		dateOfOrder: '2025-01-24T09:00:00Z',
		status: 'cancelled',
		notes: 'Order cancelled by clinic',
		items: [
			{ id: '6', brandId: '2', productType: 'device', sizeId: '4', quantity: 2, asp: 800, deviceType: 'pump' }
		],
		manufacturerId: '2',
	}
])

const searchTerm = ref('')
const statusFilter = ref('all')
// Fix type to allow 'cancelled'
type OrderStatus = 'submitted' | 'acknowledged' | 'shipped' | 'delivered' | 'cancelled'
type OrderItem = {
	id: string;
	brandId: string;
	productType: 'graft' | 'device';
	sizeId: string;
	quantity: number;
	asp: number;
	deviceType?: string;
}
interface Order {
	id: string;
	clinicId: string;
	clinicianId: string;
	patientName?: string;
	dateOfOrder: string;
	status: OrderStatus;
	notes?: string;
	items: OrderItem[];
	manufacturerId: string;
	trackingNumber?: string;
}

const selectedOrder = ref<Order | null>(null)
const showOrderModal = ref(false)
const showCreateForm = ref(false)
const newOrder = ref({
	clinicId: '',
	clinicianId: '',
	patientId: '',
	dateOfOrder: '',
	status: 'submitted' as const,
	notes: '',
	items: [
		{ id: Date.now().toString(), brandId: '', productType: 'graft' as const, sizeId: '', quantity: 1, asp: 0, deviceType: '' }
	],
	manufacturerId: '',
	trackingNumber: ''
})

const clinics = [
	{ id: '1', name: "St. Mary's Hospital" },
	{ id: '2', name: 'General Health Center' },
	{ id: '3', name: 'City Medical Center' }
]

function getClinicName(clinicId: string) {
	const clinic = clinics.find(c => c.id === clinicId)
	return clinic ? clinic.name : 'Unknown Clinic'
}

// Add mock clinicians with clinicId
const clinicians = [
	{ id: '1', name: 'Dr. Alice', clinicId: '1' },
	{ id: '2', name: 'Dr. Bob', clinicId: '2' },
	{ id: '3', name: 'Dr. Carol', clinicId: '3' },
]
// Add sizes and MUE to brands
const brands = [
	{ id: '1', name: 'MedTech', asp: 1000, mue: 3, sizes: [ { id: '1', size: '2x2' }, { id: '2', size: '4x4' } ] },
	{ id: '2', name: 'BioMed', asp: 1200, mue: 2, sizes: [ { id: '3', size: '3x3' }, { id: '4', size: '5x5' } ] }
]
// Add mock patients with ivrStatus
const patients = [
	{ id: '1', name: 'John Doe', clinicId: '1', ivrStatus: 'eligible' },
	{ id: '2', name: 'Jane Smith', clinicId: '2', ivrStatus: 'not_eligible' },
	{ id: '3', name: 'Bob Johnson', clinicId: '3', ivrStatus: 'pending' },
	{ id: '4', name: 'Sarah Davis', clinicId: '1', ivrStatus: 'eligible' }
]
function getPatientName(patientId: string) {
	const patient = patients.find(p => p.id === patientId)
	return patient ? patient.name : ''
}
const filteredPatients = computed(() => patients.filter(p => !newOrder.value.clinicId || p.clinicId === newOrder.value.clinicId))
const selectedPatient = computed(() => patients.find(p => p.id === newOrder.value.patientId))
const isPatientEligible = computed(() => !selectedPatient.value || selectedPatient.value.ivrStatus === 'eligible')
const filteredClinicians = computed(() => clinicians.filter(c => c.clinicId === newOrder.value.clinicId))
function selectedBrand(idx: number) {
	const brandId = newOrder.value.items[idx].brandId
	return brands.find(b => b.id === brandId)
}
function onBrandChange(idx: number) {
	const brand = selectedBrand(idx)
	if (brand) {
		newOrder.value.items[idx].asp = brand.asp
		// Reset size if not in new brand
		if (!brand.sizes.find(s => s.id === newOrder.value.items[idx].sizeId)) {
			newOrder.value.items[idx].sizeId = ''
		}
	} else {
		newOrder.value.items[idx].asp = 0
		newOrder.value.items[idx].sizeId = ''
	}
}
function isQuantityValid(idx: number) {
	const brand = selectedBrand(idx)
	const qty = newOrder.value.items[idx].quantity
	return !brand || qty <= brand.mue
}
function onClinicChange() {
	newOrder.value.clinicianId = ''
	newOrder.value.patientId = ''
}
function addOrderItem() {
	newOrder.value.items.push({ id: Date.now().toString(), brandId: '', productType: 'graft', sizeId: '', quantity: 1, asp: 0, deviceType: '' })
}
function removeOrderItem(idx: number) {
	if (newOrder.value.items.length > 1) newOrder.value.items.splice(idx, 1)
}
function resetCreateForm() {
	showCreateForm.value = false
	newOrder.value = {
		clinicId: '',
		clinicianId: '',
		patientId: '',
		dateOfOrder: '',
		status: 'submitted',
		notes: '',
		items: [ { id: Date.now().toString(), brandId: '', productType: 'graft', sizeId: '', quantity: 1, asp: 0, deviceType: '' } ],
		manufacturerId: '',
		trackingNumber: ''
	}
}
const canSubmit = computed(() => {
	if (!newOrder.value.clinicId || !newOrder.value.clinicianId) return false
	if (!isPatientEligible.value) return false
	if (!newOrder.value.items.length) return false
	for (let i = 0; i < newOrder.value.items.length; i++) {
		const item = newOrder.value.items[i]
		if (!item.brandId || !item.sizeId || !item.quantity) return false
	}
	return true
})
const showMueModal = ref(false)
let pendingOrderSubmit = false
function handleCreateOrder() {
	if (!canSubmit.value) return
	// Check for any items exceeding MUE
	const exceedsMUE = newOrder.value.items.some((item, idx) => !isQuantityValid(idx))
	if (exceedsMUE) {
		showMueModal.value = true
		pendingOrderSubmit = true
		return
	}
	submitOrder()
}
function confirmMueSubmit() {
	showMueModal.value = false
	if (pendingOrderSubmit) {
		submitOrder()
		pendingOrderSubmit = false
	}
}
function submitOrder() {
	const now = new Date().toISOString()
	orders.value.unshift({
		id: `ORD-${Math.floor(Math.random() * 100000)}`,
		clinicId: newOrder.value.clinicId,
		clinicianId: newOrder.value.clinicianId,
		patientName: selectedPatient.value ? selectedPatient.value.name : '',
		dateOfOrder: newOrder.value.dateOfOrder || now,
		status: newOrder.value.status,
		notes: newOrder.value.notes,
		items: newOrder.value.items.map(i => ({
			id: i.id,
			brandId: i.brandId,
			productType: i.productType,
			sizeId: i.sizeId,
			quantity: i.quantity,
			asp: i.asp,
			deviceType: i.deviceType
		})),
		manufacturerId: newOrder.value.manufacturerId,
		trackingNumber: newOrder.value.trackingNumber
	})
	resetCreateForm()
}

function showOrderDetails(order: Order) {
	selectedOrder.value = order
	showOrderModal.value = true
}

function updateOrderStatus(orderId: string, newStatus: 'submitted' | 'acknowledged' | 'shipped' | 'delivered') {
	const orderIndex = orders.value.findIndex(order => order.id === orderId)
	if (orderIndex !== -1) {
		orders.value[orderIndex].status = newStatus
	}
}

const filteredOrders = computed(() => {
	return orders.value.filter(order => {
		const matchesSearch = order.id.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
		order.clinicId.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
		order.patientName?.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
		order.patientName === null || order.patientName === undefined
		const matchesStatus = statusFilter.value === 'all' || order.status === statusFilter.value
		return matchesSearch && matchesStatus
	})
})

const getStatusColor = (status: string) => {
	switch (status) {
		case 'delivered': return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
		case 'shipped': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
		case 'acknowledged': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
		case 'submitted': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
		case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
		default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
	}
}
const getStatusIcon = (status: string) => {
	switch (status) {
		case 'delivered': return CheckCircleIcon
		case 'shipped': return TruckIcon
		case 'acknowledged': return CubeIcon
		default: return null
	}
}
const formatDate = (dateStr: string) => {
	const date = new Date(dateStr)
	return date.toLocaleDateString()
}
function getBrandName(brandId: string) {
	const brand = brands.find(b => b.id === brandId)
	return brand ? brand.name : 'Unknown Brand'
}
const showDeleteModal = ref(false)
const orderToDelete = ref<Order | null>(null)
function openDeleteModal(order: Order) {
	orderToDelete.value = order
	showDeleteModal.value = true
}
function confirmDeleteOrderModal() {
	if (orderToDelete.value) {
		orders.value = orders.value.filter(o => o.id !== orderToDelete.value!.id)
		showDeleteModal.value = false
		orderToDelete.value = null
	}
}
function getClinicianName(clinicianId: string) {
	const clinician = clinicians.find(c => c.id === clinicianId)
	return clinician ? clinician.name : 'Unknown Clinician'
}
function getSizeName(brandId: string, sizeId: string) {
	const brand = brands.find(b => b.id === brandId)
	const size = brand?.sizes.find(s => s.id === sizeId)
	return size ? size.size : 'Unknown Size'
}
// Remove duplicate orders.value assignment, keep only one sample data block
// orders.value = [
// 	{
// 		id: 'ORD-001',
// 		clinicId: '1',
// 		clinicianId: '1',
// 		patientName: 'John Doe',
// 		dateOfOrder: '2025-01-27T10:00:00Z',
// 		status: 'delivered',
// 		notes: 'Urgent delivery',
// 		items: [
// 			{ id: '1', brandId: '1', productType: 'graft', sizeId: '1', quantity: 2, asp: 1000 },
// 			{ id: '2', brandId: '2', productType: 'device', sizeId: '2', quantity: 1, asp: 500, deviceType: 'monitor' }
// 		],
// 		manufacturerId: '1',
// 		trackingNumber: 'TRK123456',
// 	},
// 	{
// 		id: 'ORD-002',
// 		clinicId: '2',
// 		clinicianId: '2',
// 		patientName: 'Jane Smith',
// 		dateOfOrder: '2025-01-26T14:00:00Z',
// 		status: 'shipped',
// 		items: [
// 			{ id: '3', brandId: '2', productType: 'graft', sizeId: '2', quantity: 1, asp: 1200 }
// 		],
// 		manufacturerId: '2',
// 		trackingNumber: 'TRK789012',
// 	},
// 	{
// 		id: 'ORD-003',
// 		clinicId: '3',
// 		clinicianId: '3',
// 		patientName: 'Bob Johnson',
// 		dateOfOrder: '2025-01-25T16:00:00Z',
// 		status: 'acknowledged',
// 		items: [
// 			{ id: '4', brandId: '1', productType: 'graft', sizeId: '3', quantity: 1, asp: 900 }
// 		],
// 		manufacturerId: '1',
// 	},
// 	{
// 		id: 'ORD-004',
// 		clinicId: '1',
// 		clinicianId: '1',
// 		patientName: 'Sarah Davis',
// 		dateOfOrder: '2025-01-27T08:00:00Z',
// 		status: 'submitted',
// 		items: [
// 			{ id: '5', brandId: '1', productType: 'graft', sizeId: '1', quantity: 3, asp: 1000 }
// 		],
// 		manufacturerId: '1',
// 	},
// 	{
// 		id: 'ORD-005',
// 		clinicId: '2',
// 		clinicianId: '2',
// 		patientName: 'Eve Adams',
// 		dateOfOrder: '2025-01-24T09:00:00Z',
// 		status: 'cancelled',
// 		notes: 'Order cancelled by clinic',
// 		items: [
// 			{ id: '6', brandId: '2', productType: 'device', sizeId: '4', quantity: 2, asp: 800, deviceType: 'pump' }
// 		],
// 		manufacturerId: '2',
// 	}
// ]
</script>
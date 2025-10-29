<template>
	<div class="space-y-6">
		<!-- Header and Create Order -->
		<div class="flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order Management</h1>
				<p class="text-gray-600 dark:text-gray-400">View, organize, and track every order in one place.</p>
			</div>
			<button
				@click="openCreateForm"
				class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
			>
				<PackagePlus class="w-4 h-4 mr-2" />
				Create Order
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white px-6 py-4 border-b border-gray-200 dark:border-gray-600 mb-2 shadow-sm">
			<div class="flex items-center justify-between">
				<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Order Records</h2>
				<div class="flex items-center space-x-4">
					<div class="relative">
						<Search class="absolute left-3 top-3 h-4 w-4 text-gray-400 dark:text-gray-500" />
						<input
							v-model="searchTerm"
							type="text"
							placeholder="Search Clinics..."
							class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						/>
					</div>
					<div class="flex items-center space-x-2">
						<Funnel class="w-4 h-4 text-gray-500 dark:text-gray-400" />
						<select
						v-model="statusFilter"
						class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
						</select>
					</div>
					<div class="flex items-center space-x-2">
						<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
						<select
							id="per-page"
							v-model="itemsPerPage"
							class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded 
								focus:ring-2 focus:ring-blue-500 focus:border-transparent 
								bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
						>
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
						</select>
					</div>
				</div>
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
						<TableLoader v-if="tableLoader" :colspan="7" />
						<template v-else>
							<tr
							v-for="order in filteredOrders"
							:key="order.order_id"
							class="hover:bg-gray-50 dark:hover:bg-gray-700">
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm font-medium text-gray-900 dark:text-white">#{{ order.order_code }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900 dark:text-white">{{ order.clinic?.clinic_name }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900 dark:text-white">{{ order.patient?.patient_name || 'Not specified' }}</div>
								</td>
								<td class="px-6 py-4">
									<div class="text-sm text-gray-900 dark:text-white">
										<div v-for="(item, idx) in order.items" :key="idx" class="mb-1">
											{{ getBrandName(item.brandId) }} - {{ getSizeName(item.graft_id) }} × {{ item.quantity }}
										</div>
									</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-gray-900 dark:text-white">{{ formatDate(order.ordered_at) }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span :class="['inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.order_status)]">
										<component :is="getStatusIcon(order.order_status)" class="w-4 h-4" />
										<span class="capitalize">{{ order.order_status }}</span>
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
									<div class="flex items-center space-x-2">
										<button @click="showOrderDetails(order)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
											<Eye class="w-4 h-4" />
										</button>
										<button @click="editOrder(order)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
											<SquarePen class="w-4 h-4" />
										</button>
										<button @click="confirmDelete(order)" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
											<Trash2 class="w-4 h-4" />
										</button>
										<div class="inline-flex space-x-1">
											<button v-if="order.order_status === 'submitted'" @click="updateOrderStatusNew(order, 'acknowledged')" class="px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-900/30">Acknowledge</button>
											<button v-if="order.order_status === 'acknowledged'" @click="updateOrderStatusNew(order, 'shipped')" class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/30">Ship</button>
											<button v-if="order.order_status === 'shipped'" @click="updateOrderStatusNew(order, 'delivered')" class="px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/30">Deliver</button>
										</div>
									</div>
								</td>
							</tr>
							<tr v-if="filteredOrders.length === 0">
								<td colspan="7" class="text-center py-8 text-gray-400 dark:text-gray-500">
									<div class="flex flex-col items-center justify-center gap-2">
										<ShoppingCart class="w-10 h-10 mb-1" />
										<span>No orders found.</span>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>
		</div>

		<template v-if="!tableLoader">
			<Pagination :pagination="pagination" @update:page="getAllOrders" />
		</template>

		<!-- View Order Modal -->
		<BaseModal v-model="showOrderModal" title="Order Details">
			<template v-if="selectedOrder">
				<div>
					{{ selectedOrder.order_code }}
				</div>
				<div class="space-y-6 p-6">
					<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<Box class="w-5 h-5 text-blue-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinic</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.clinic?.clinic_name }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<CircleUser class="w-5 h-5 text-green-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clinician</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.clinician?.first_name }} {{ selectedOrder.clinician?.last_name }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<CircleUser class="w-5 h-5 text-purple-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Patient</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.patient?.patient_name || 'Not specified' }}</p>
								</div>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<Calendar1 class="w-5 h-5 text-orange-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Date</p>
									<p class="text-gray-900 dark:text-white">{{ formatDate(selectedOrder.ordered_at) }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3">
								<CircleCheck class="w-5 h-5 text-indigo-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</p>
									<span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(selectedOrder.order_status)]">
										{{ selectedOrder.order_status.charAt(0).toUpperCase() + selectedOrder.order_status.slice(1) }}
									</span>
								</div>
							</div>
							<div v-if="selectedOrder.tracking_num" class="flex items-center space-x-3">
								<Truck class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</p>
									<p class="text-gray-900 dark:text-white font-mono">{{ selectedOrder.tracking_num }}</p>
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
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getSizeName(item.graft_id) }}</td>
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
							<FileTextIcon class="w-5 h-5 text-gray-600" />
							<h3 class="text-lg font-medium text-gray-900 dark:text-white">Notes</h3>
						</div>
						<div class="bg-gray-50 dark:bg-gray-900/20 rounded-lg p-4">
							<p class="text-gray-700 dark:text-gray-200">{{ selectedOrder.notes }}</p>
						</div>
					</div>
					<div class="border-t border-gray-200 pt-6">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Update Status</h3>
						<div class="flex space-x-3">
							<button v-if="selectedOrder.order_status === 'submitted'" @click="updateOrderStatusNew(selectedOrder.order_id, 'acknowledged')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Mark Acknowledged</button>
							<button v-if="selectedOrder.order_status === 'acknowledged'" @click="updateOrderStatusNew(selectedOrder.order_id, 'shipped')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Mark Shipped</button>
							<button v-if="selectedOrder.order_status === 'shipped'" @click="updateOrderStatusNew(selectedOrder.order_id, 'delivered')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Mark Delivered</button>
							<span v-else class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">Delivered</span>
						</div>
					</div>
				</div>
			</template>
		</BaseModal>

		<!-- Create/Edit Order Modal -->
		<BaseModal v-model="showFormModal" :title="showCreateForm ? 'Create New Order' : 'Edit Order Details'">
			<form @submit.prevent="handleCreateOrder" class="space-y-6">
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<!-- Clinic Selection -->
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ordering Clinic <span class="text-red-500">*</span></label>
						<select v-model="formData.clinicId" @change="onClinicChange" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
							<option value="">Select Clinic</option>
							<option v-for="clinic in clinics" :key="clinic.clinic_id" :value="clinic.clinic_id">{{ clinic.clinic_name }} [{{ clinic.clinic_code }}]</option>
						</select>
					</div>
					<!-- Clinician Selection -->
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ordering Clinician <span class="text-red-500">*</span></label>
						<select v-model="formData.clinicianId" :disabled="!formData.clinicId" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
							<option value="">Select Clinician</option>
							<option v-for="clinician in filteredClinicians" :key="clinician.id" :value="clinician.id">{{ clinician.first_name }}</option>
						</select>
					</div>
				</div>

				<!-- Patient Selection -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Intended Patient</label>
					<select v-model="formData.patientId" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
						<option value="">Select Patient (Optional)</option>
						<option v-for="patient in filteredPatients" :key="patient.patient_id" :value="patient.patient_id">{{ patient.patient_name }}</option>
					</select>
					<div v-if="selectedPatient">
						<div v-if="isPatientEligible" class="mt-2 p-3 rounded-lg flex items-center space-x-2 bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-400">
							<CircleCheck class="w-5 h-5 text-green-600 dark:text-green-400" />
							<span class="text-sm font-medium text-green-800 dark:text-green-400">IVR Status: {{ getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) }}</span>
						</div>
						<div
							v-else
							class="mt-2 p-3 rounded-lg flex items-center space-x-2 border dark:border-opacity-40"
							:class="{
								'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Eligible',
								'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Not Eligible',
								'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Pending',
							}"
							>
							<TriangleAlert
								class="w-5 h-5"
								:class="{
								'text-green-600 dark:text-green-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Eligible',
								'text-red-600 dark:text-red-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Not Eligible',
								'text-yellow-600 dark:text-yellow-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Pending',
								}"
							/>
							<span
								class="text-sm font-medium"
								:class="{
								'text-green-800 dark:text-green-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Eligible',
								'text-red-800 dark:text-red-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Not Eligible',
								'text-yellow-800 dark:text-yellow-400': getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) === 'Pending',
								}"
							>
								IVR Status: {{ getEligibilityLabel(selectedPatient.ivrs?.[0]?.eligibility_status) }}
							</span>
						</div>
					</div>
				</div>
				
				<!-- Order Items -->
				<div>
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white">Order Items</h3>
						<button type="button" @click="addOrderItem" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
							<PackagePlus class="w-4 h-4" />
							<span>Add Item</span>
						</button>
					</div>

					<p class="text-xs text-yellow-600 dark:text-yellow-400 mb-2">Note: You may exceed the MUE (Maximum Units per Episode) for a product, but this will be flagged for review. You can still submit the order.</p>
					<div v-for="(item, idx) in formData.items" :key="item.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-[2fr_2fr_1fr_1fr_auto] gap-4 items-end">
							<!-- Brand -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand <span class="text-red-500">*</span></label>
								<select v-model="item.brandId" @change="onBrandChange(idx)" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
									<option value="">Select Brand</option>
									<option v-for="brand in brands" :key="brand.brand_id" :value="brand.brand_id">{{ brand.brand_name }}</option>
								</select>
							</div>

							<!-- Size -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									Size <span class="text-red-500">*</span>
								</label>
								<select
									v-model="item.sizeId"
									@change="onSizeChange(idx)"
									:disabled="!selectedBrand(idx)"
									required
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option value="">Select Size</option>
									<option
										v-for="size in getSizesByBrand(item.brandId)"
										:key="size.graft_size_id"
										:value="size.graft_size_id.toString()"
									>
										{{ size.size }}
									</option>
								</select>
							</div>

							<!-- Quantity -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity <span class="text-red-500">*</span></label>
								<input v-model.number="item.quantity" type="number" min="1" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="!isQuantityValid(idx) ? 'border-red-300 bg-red-50 dark:border-red-400 dark:bg-red-900/10' : 'border-gray-300 dark:border-gray-600'" />
							</div>

							<!-- ASP -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									ASP
								</label>
								<input
									type="text"
									:value="item.asp && item.quantity ? `$${(item.asp * item.quantity).toFixed(2)}` : ''"
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900/10"
									placeholder="-----"
									readonly
								/>
							</div>

							<!-- Remove -->
							<div class="flex justify-end md:justify-center items-end">
								<button type="button" @click="removeOrderItem(idx)" class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 rounded-lg transition-colors">
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
						</div>
						<p v-if="!isQuantityValid(idx)" class="text-xs text-red-600 dark:text-red-400 mt-1">Exceeds MUE limit of {{ selectedBrand(idx)?.mue }} for this brand. You may still submit, but this will be flagged for review.</p>
					</div>
				</div>

				<!-- Internal Notes -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Notes</label>
					<textarea v-model="formData.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="Add any internal notes..." />
				</div>

				<!-- Status, Date, Tracking -->
				<!-- <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
						<select v-model="formData.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
							<option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}</option>
						</select>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Order</label>
						<input v-model="formData.dateOfOrder" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</label>
						<input v-model="formData.trackingNumber" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white" />
					</div>
				</div> -->
			</form>
			<template #actions>
				<div class="p-4 flex items-center gap-2">
					<button
					type="button"
					@click="closeForm"
					class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
						Cancel
					</button>
					<button
					type="button"
					@click="handleCreateOrder"
					class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
					>
						{{ showCreateForm ? 'Submit Order' : 'Update Order' }}
					</button>
				</div>
			</template>
		</BaseModal>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import BaseModal from '@/components/common/BaseModal.vue'
import Pagination from '../components/ui/Pagination.vue'
import TableLoader from '../components/ui/TableLoader.vue'
import {
    Search,
    Funnel,
    Eye,
	CircleCheck,
	Truck,
	Box,
	TriangleAlert,
	CircleUser,
	Calendar1,
	FileTextIcon,
	SquarePen,
	PackagePlus,
	ShoppingCart,
	Trash2,
} from 'lucide-vue-next';
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface Order {
	order_id: number;
	order_code: string
	ordered_at: string;
	order_status: OrderStatus;
	notes?: string;
	items: OrderItem[];
	tracking_num?: string;

	clinic: Clinic
	clinician: Clinician
	patient: PatientInfo
	// brand: Brand
}

interface Clinic {
	clinic_id: number
	clinic_code: string
	clinic_name: string
	clinicians: Clinician[]
}

interface Clinician {
	id: number
	first_name: string
	middle_name: string
	last_name: string
}

interface PatientInfo {
	patient_id: number
	patient_name: string
	clinic_id?: number
	ivrs: IVR[]
	user_id: User[]
}

interface Brand {
	brand_id: string
	description: string
	brand_name: string
	mue: number
	manufacturer?: {
		manufacturer_id: string
		manufacturer_name: string
	}
}

interface GraftSize {
	graft_size_id: number
	size: string
	area: string
	price: number
	graft_status: number
	brand: Brand
}

interface IVR {
	ivr_id: number
	eligibility_status: number
}

interface User {
	id: number
	first_name: string
	last_name: string
}

type OrderItem = {
	id: string;
	brandId: string;
	productType: 0 | 1
	sizeId: string;
	graft_id: number;
	quantity: number;
	asp: number;
	totalAsp?: number;
	deviceType?: string;
}

// 0-submitted | 1-acknowledged | 2-shipped | 3-delivered | 4-cancelled
type OrderStatus = 'submitted' | 'acknowledged' | 'shipped' | 'delivered' | 'cancelled'

const orderStatusMap: Record<OrderStatus, number> = {
	submitted: 0,
	acknowledged: 1,
	shipped: 2,
	delivered: 3,
	cancelled: 4
}

const orderStatusReverseMap: Record<number, OrderStatus> = {
	0: 'submitted',
	1: 'acknowledged',
	2: 'shipped',
	3: 'delivered',
	4: 'cancelled'
}

const tableLoader = ref(false);
const orders = ref<Order[]>([])
const clinics = ref<Clinic[]>([])
const patients = ref<PatientInfo[]>([])
const brands = ref<Brand[]>([])
const graftSizes = ref<GraftSize[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})


const searchTerm = ref('')
const statusFilter = ref('all')

const selectedOrder = ref<Order | null>(null)
const showOrderModal = ref(false)
const showCreateForm = ref(false)
const showEditForm = ref(false)
const showFormModal = ref(false)
const selectedOrderForEdit = ref<Order | null>(null)

const formData = ref({
	clinicId: '',
	clinicianId: '',
	patientId: '',
	brandId: '',
	sizeId: '',
	dateOfOrder: '',
	status: 'submitted' as const,
	notes: '',
	items: [
		{ 
			id: Date.now().toString(), 
			brandId: '', 
			productType: 0 as const,
			sizeId: '',
			graft_id: 0,
			quantity: 1, 
			asp: 0, 
			totalAsp: 0,
			deviceType: '' 
		}
	],
	manufacturerId: '',
	trackingNumber: ''
})

const filteredPatients = computed(() => {
	if (!formData.value.clinicId) return patients.value

	return patients.value.filter(
		(p) => p.clinic_id === Number(formData.value.clinicId)
	)
})

const selectedPatient = computed(() => {
	const patient = patients.value.find(
		(p) => p.patient_id === Number(formData.value.patientId)
	)
	return patient
})

function getEligibilityLabel(status?: number) {
	switch (status) {
		case 1:
			return 'Eligible'
		case 2:
			return 'Not Eligible'
		case 0:
		default:
			return 'Pending'
	}
}

const isPatientEligible = computed(() => {
	const patient = selectedPatient.value	
	
	if (!patient) {
		console.log('No patient selected')
		return false
	}
	
	if (!Array.isArray(patient.ivrs)) {
		console.log('Patient IVR is not an array:', patient.ivrs)
		return false
	}
	
	if (patient.ivrs.length === 0) {
		console.log('Patient has no IVR records')
		return false
	}
	
	const hasEligibleIVR = patient.ivrs.some((i) => i.eligibility_status === 1)	
	return hasEligibleIVR
})

const filteredClinicians = computed(() => {
	const selectedClinic = clinics.value.find(
		(clinic) => clinic.clinic_id === Number(formData.value.clinicId)
	)
	return selectedClinic ? selectedClinic.clinicians : []
})

function selectedBrand(idx: number) {
	const brandId = formData.value.items[idx].brandId
	const brand = brands.value.find(b => b.brand_id == brandId)
	return brand
}

function getBrandName(brandId: string) {
	const brand = brands.value.find(b => b.brand_id == brandId)
	return brand ? brand.brand_name : `Brand ${brandId}`
}

function getSizeName(graft_size: string | number) {
	if (!graft_size) return "Unknown size"
	const id = Number(graft_size)

	const graftSize = graftSizes.value.find(g => g.graft_size_id == id)
	return graftSize?.size ?? `Size ${graft_size}`
}

function onBrandChange(idx: number) {
	const brand = selectedBrand(idx)
	if (brand) {
		formData.value.items[idx].asp = 0
		formData.value.items[idx].sizeId = ''
		formData.value.items[idx].graft_id = 0
	} else {
		formData.value.items[idx].asp = 0
		formData.value.items[idx].sizeId = ''
		formData.value.items[idx].graft_id = 0
	}
}

function isQuantityValid(idx: number) {
	const brand = selectedBrand(idx)
	const qty = formData.value.items[idx].quantity
	return !brand || qty <= brand.mue
}

function onClinicChange() {
	formData.value.clinicianId = ''
	formData.value.patientId = ''
}

function addOrderItem() {
	formData.value.items.push({ 
		id: Date.now().toString(), 
		brandId: '', 
		productType: 0 as const, 
		sizeId: '', 
		graft_id: 0,
		quantity: 1, 
		asp: 0, 
		totalAsp: 0, 
		deviceType: '' 
	})
}

function removeOrderItem(idx: number) {
	if (formData.value.items.length > 1) formData.value.items.splice(idx, 1)
}

function resetCreateForm() {
	formData.value = {
		clinicId: '',
		clinicianId: '',
		patientId: '',
		brandId: '',
		sizeId: '',
		dateOfOrder: '',
		status: 'submitted',
		notes: '',
		items: [{ 
			id: Date.now().toString(), 
			brandId: '', 
			productType: 0 as const, 
			sizeId: '', 
			graft_id: 0,
			quantity: 1, 
			asp: 0, 
			totalAsp: 0, 
			deviceType: '' 
		}],
		manufacturerId: '',
		trackingNumber: ''
	}
}

async function handleCreateOrder() {
	if (!formData.value.clinicId) {
		toast.error('Please select a clinic')
		return
	}
	if (!formData.value.clinicianId) {
		toast.error('Please select a clinician')
		return
	}
	if (!formData.value.patientId) {
		toast.error('Please select a patient')
		return
	}
	if (!isPatientEligible.value) {
		toast.error('Selected patient is not eligible for this order')
		return
	}
	if (!formData.value.items.length) {
		toast.error('Please add at least one item to the order')
		return
	}
	
	for (let i = 0; i < formData.value.items.length; i++) {
		const item = formData.value.items[i]
		if (!item.brandId) {
			toast.error(`Please select a brand for item ${i + 1}`)
			return
		}
		if (!item.sizeId) {
			toast.error(`Please select a size for item ${i + 1}`)
			return
		}
		if (!item.quantity || item.quantity <= 0) {
			toast.error(`Please enter a valid quantity for item ${i + 1}`)
			return
		}
	}
	
	// Check MUE limits
	const exceedsMUE = formData.value.items.some((item, idx) => !isQuantityValid(idx))
	if (exceedsMUE) {
		const result = await Swal.fire({
			title: '⚠️ MUE Limit Exceeded',
			html: `
				<p class="text-sm text-gray-700">
					One or more items exceed their MUE (Maximum Units per Episode) limit.<br>
					You may still submit this order, but it will be flagged for review.
				</p>
			`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Submit Anyway',
			cancelButtonText: 'Cancel',
			confirmButtonColor: '#facc15',
			cancelButtonColor: '#6b7280',
			reverseButtons: true,
		})

		if (!result.isConfirmed) {
			toast.info('Order submission cancelled due to MUE limit')
			return
		}
	}

	addNewOrder()
}

function openCreateForm() {
	showCreateForm.value = true
	showEditForm.value = false
	selectedOrderForEdit.value = null
	resetCreateForm()
	showFormModal.value = true
}

function editOrder(order: Order) {
	showCreateForm.value = false
	showEditForm.value = true
	selectedOrderForEdit.value = order
	
	// Populate form with existing order data
	formData.value = {
		clinicId: order.clinic?.clinic_id?.toString() || '',
		clinicianId: order.clinician?.id?.toString() || '',
		patientId: order.patient?.patient_id?.toString() || '',
		brandId: '',
		sizeId: '',
		dateOfOrder: order.ordered_at ? new Date(order.ordered_at).toISOString().split('T')[0] : '',
		status: 'submitted' as const,
		notes: order.notes || '',
		items: order.items.length > 0 ? order.items.map(item => {
			return {
				id: item.id,
				brandId: item.brandId,
				productType: 0 as const,
				sizeId: item.graft_id?.toString() || '',
				graft_id: item.graft_id || 0,
				quantity: item.quantity,
				asp: item.asp,
				totalAsp: item.asp * item.quantity,
				deviceType: item.deviceType || ''
			}
		}) : [{ 
			id: Date.now().toString(), 
			brandId: '', 
			productType: 0 as const,
			sizeId: '', 
			graft_id: 0,
			quantity: 1, 
			asp: 0, 
			totalAsp: 0,
			deviceType: '' 
		}],
		manufacturerId: '',
		trackingNumber: order.tracking_num || ''
	}
	
	showFormModal.value = true
	
	nextTick(() => {
		//debugger
	})
}

function closeForm() {
	showFormModal.value = false
	showCreateForm.value = false
	showEditForm.value = false
	selectedOrderForEdit.value = null
	resetCreateForm()
}

function showOrderDetails(order: Order) {
	selectedOrder.value = order
	showOrderModal.value = true
}

const filteredOrders = computed(() => {
	const term = searchTerm.value.trim().toLowerCase();

	return (orders.value ?? []).filter(order => {
		const orderCode = order.order_code?.toLowerCase() || '';
		const clinicCode = order.clinic?.clinic_code?.toLowerCase() || '';
		const clinicName = order.clinic?.clinic_name?.toLowerCase() || '';
		const patientName = order.patient?.patient_name?.toLowerCase() || '';
		const status = order.order_status?.toLowerCase() || '';

		const matchesSearch =
			term === '' ||
			orderCode.includes(term) ||
			clinicCode.includes(term) ||
			clinicName.includes(term) ||
			patientName.includes(term);

		const matchesStatus =
			statusFilter.value === 'all' ||
			order.order_status === statusFilter.value;

		return matchesSearch && matchesStatus;
	});
});

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
		case 'delivered': return CircleCheck
		case 'shipped': return Truck
		case 'acknowledged': return Box
		default: return null
	}
}
const formatDate = (dateStr: string) => {
	if (!dateStr) return null;
	const date = new Date(dateStr)
	return date.toLocaleDateString('en-US', {
		year: 'numeric',
		month: 'long',
		day: 'numeric'
	})
}

function getSizesByBrand(brandId: string) {
	const sizes = graftSizes.value.filter(gs => gs.brand.brand_id == brandId)
	return sizes
}

function onSizeChange(idx: number) {
	const item = formData.value.items[idx]
	
	const graft = graftSizes.value.find(
		g => g.graft_size_id.toString() === item.sizeId
	)
	
	if (graft) {
		item.asp = graft.price
		item.graft_id = graft.graft_size_id
	} else {
		item.asp = 0
		item.graft_id = 0
	}
}

async function getAllOrders(page = 1)
{
	tableLoader.value = true;
    try {
        const { data } = await api.get(`/management/order/getorders?page=${page}&per_page=${itemsPerPage.value}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })

		const rows = Array.isArray(data?.order_data) ? data.order_data : []

		orders.value = rows.map((o: any) => {
			let rawItems = o.items
			if (!Array.isArray(rawItems) && typeof rawItems === 'string') {
				try { rawItems = JSON.parse(rawItems) } catch { rawItems = [] }
			}
			const items: OrderItem[] = Array.isArray(rawItems) ? rawItems.map((it: any, idx: number) => ({
				id: String(it.id ?? idx),
				brandId: String(it.brandId ?? it.brand_id ?? ''),
				productType: (it.productType ?? it.product_type ?? 'graft'),
				sizeId: String(it.sizeId ?? it.size_id ?? it.graft_id ?? ''),
				graft_id: Number(it.graft_id ?? 0),
				quantity: Number(it.quantity ?? 0),
				asp: Number(it.asp ?? 0),
				deviceType: it.deviceType ?? it.device_type ?? ''
			})) : []

			return {
				order_id: Number(o.order_id ?? 0),
				order_code: String(o.order_code ?? ''),
				ordered_at: String(o.ordered_at ?? o.created_at ?? ''),
				order_status: (['submitted','acknowledged','shipped','delivered','cancelled'][Number(o.order_status ?? 0)] ?? 'submitted') as OrderStatus,
				notes: o.notes ?? '',
				items,
				tracking_num: o.tracking_num ?? '',
				clinic: o.clinic,
				clinician: o.clinician,
				patient: o.patient,
				brand: o.brand
			} as Order
		})

		pagination.value = {
            current_page: Number(data?.current_page ?? 1),
            last_page: Number(data?.last_page ?? 1),
            per_page: Number(data?.per_page ?? itemsPerPage.value),
            total: Number(data?.total ?? rows.length),
        }
    } catch (error) {
		orders.value = []
    } finally {
        tableLoader.value = false
    }
}

async function getAllClinics()
{
	try {
		const { data } = await api.get(`/management/order/getclinics`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
		clinics.value = data.clinic_data || []
	} catch (error) {
		clinics.value = []
    } finally {
        tableLoader.value = false
    }
}

async function getAllPatients()
{
	try {
		const { data } = await api.get(`/management/order/users/getpatients`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('auth_token')}`
            }
        })
		patients.value = data.patient_data || []
	} catch (error) {
		patients.value = []
    } finally {
        tableLoader.value = false
    }
}

async function getAllGraftSizes() {
	try {
		const { data } = await api.get(`/management/order/getgraftsizes`, {
			headers: {
				Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			}
		})
		graftSizes.value = data.graft_size_data || []

		const brandMap = new Map()
		graftSizes.value.forEach(gs => {
			const brand = gs.brand
			if (brand?.brand_id && !brandMap.has(brand.brand_id)) {
				brandMap.set(brand.brand_id, brand)
			}
		})
		brands.value = Array.from(brandMap.values())
	} catch (error) {
		console.error('Error loading graft sizes:', error)
		graftSizes.value = []
	} finally {
		tableLoader.value = false
	}
}

async function addNewOrder(){
	const payload = {
		clinic_id: formData.value.clinicId,
		clinician_id: formData.value.clinicianId,
		patient_id: formData.value.patientId,
		notes: formData.value.notes,
		items: formData.value.items.map(item => ({
			brand_id: item.brandId,
			graft_id: item.sizeId,
			ivr_id: item.sizeId,
			quantity: item.quantity,
			asp: item.asp,
			product_type: item.productType,
			device_type: item.deviceType
		}))
	}

	try {
		if (showCreateForm.value) {
			const { data } = await api.post(
				'/management/order/add/neworder',
				payload,
				{
					headers: {
						Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
						'Content-Type': 'application/json'
					}
				}
			)

			toast.success(data.message || 'Order added successfully!')
			await getAllOrders(1)
			closeForm()
		} else if (showEditForm.value) {
			
			const { data } = await api.put(
                `/management/order/update/${selectedOrderForEdit.value?.order_id}/updateorder`,
                payload,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                }
         	)

			toast.success(data.message || 'Order details updated Successfully!')
			await getAllOrders(1)
			closeForm()
		}
		
	} catch (error: any) {
		if (error.response) {
			const errorMessage = error.response.data?.message || error.response.data?.error || 'Server error occurred'
			toast.error(`Failed to submit order: ${errorMessage}`)
		} else if (error.request) {
			toast.error('Network error: Unable to connect to server')
		} else {
			toast.error(`Error: ${error.message || 'Unknown error occurred'}`)
		}
	}
}

async function confirmDelete(order: Order){
	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This action cannot be undone.",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Yes, delete it!"
	});
	
	try {
		if (result.isConfirmed && order) {
			try {
				await api.put(`/management/order/delete/${order.order_id}/deleteorder`)
				toast.success('Order details deleted successfully!')
				await getAllOrders()
			} catch (error) {
				toast.error('Failed to delete order details.')
			}
		}
	} catch (error: any) {
		if (error.response) {
			const errorMessage = error.response.data?.message || error.response.data?.error || 'Server error occurred'
			toast.error(`Failed to delete order: ${errorMessage}`)
		} else if (error.request) {
			toast.error('Network error: Unable to connect to server')
		} else {
			toast.error(`Error: ${error.message || 'Unknown error occurred'}`)
		}
	}
}

async function updateOrderStatusNew(orderOrId: Order | number, newStatus: OrderStatus) {
	const order_id = typeof orderOrId === 'number' ? orderOrId : orderOrId.order_id;

	const result = await Swal.fire({
		title: "Are you sure?",
		text: "This action cannot be undone.",
		icon: "warning",
		showCancelButton: true,
		confirmButtonColor: "#3085d6",
		cancelButtonColor: "#d33",
		confirmButtonText: "Yes, confirm!"
	});

	if (!result.isConfirmed) return;

	try {
		const statusNumber = orderStatusMap[newStatus];

		await api.put(`/management/order/update/${order_id}/updateorderstatus`, {
			order_status: statusNumber
		});

		if (typeof orderOrId !== 'number') {
			const orderIndex = orders.value.findIndex(o => o.order_id === order_id);
			if (orderIndex !== -1) {
				orders.value[orderIndex].order_status = newStatus;
			}
			if (selectedOrder.value && selectedOrder.value.order_id === order_id) {
				selectedOrder.value.order_status = newStatus;
			}
		}

		toast.success('Order details Updated Successfully!')
		await getAllOrders(1)
		closeForm()
	} catch (error) {
		console.error(error);
		toast.error('Failed to update order status.')
	}
}

onMounted(async () => {
    getAllOrders(1)
	getAllClinics()
	getAllPatients()
	await getAllGraftSizes()
})

watch(itemsPerPage, () => {
    getAllOrders(1)
})

watch(() => formData.value.items, (items) => {
	items.forEach(item => {
		item.totalAsp = item.asp * item.quantity
	})
}, { deep: true })
</script>
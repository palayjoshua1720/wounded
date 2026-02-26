<template>
	<div class="space-y-6">
		<!-- Header and Create Order -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
			<div class="space-y-2">
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Order Management</h1>
				<p class="text-gray-600 dark:text-gray-400 max-w-2xl">View, organize, and track every order in one place.</p>
			</div>
			<button
				@click="
					openCreateForm
				"
				class="flex items-center px-5 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group"
			>
				<PackagePlus class="w-4 h-4 mr-2" />
				Create Order
			</button>
		</div>

		<!-- Filters -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex flex-col lg:flex-row gap-6">
				<div class="flex-1">
					<div class="relative">
						<Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" />
						<input v-model="searchTerm" type="text" placeholder="Search Order..."
							class="w-full pl-12 pr-4 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200" />
					</div>
				</div>
				<div class="flex flex-col sm:flex-row gap-4">
					<div class="relative">
						<Funnel class="absolute left-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400" />
						<select
						v-model="statusFilter"
						class="pl-10 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
						>
							<option value="all">All Status</option>
							<option value="active">Active</option>
							<option value="inactive">Inactive</option>
						</select>
						<ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
					</div>
				</div>
				<div class="relative">
					<label for="per-page" class="text-sm text-gray-700 dark:text-gray-300">Rows:</label>
					<select
						id="per-page"
						v-model="itemsPerPage"
						class="pl-4 pr-8 py-3.5 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white appearance-none transition-all duration-200"
					>
						<option value="10">10</option>
						<option value="25">25</option>
						<option value="50">50</option>
					</select>
					<ChevronDown
                            class="absolute right-3 top-3.5 h-4 w-4 text-gray-500 dark:text-gray-400 pointer-events-none" />
				</div>
			</div>
		</div>

		<!-- Orders Table -->
		<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
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
								<td class="px-6 py-3 whitespace-nowrap">
									<div class="flex items-center">
										<div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
											<BaggageClaim class="w-5 h-5 text-green-600" />
										</div>
										<div class="ml-4">
											<div class="text-sm text-gray-900 dark:text-white">{{ order.order_code }}</div>
										</div>
									</div>
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
									<div class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(order.ordered_at) }}</div>
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
											<button v-if="order.order_status === 'submitted'" @click="updateOrderStatusNew(order, 'acknowledged')" class="px-2 py-1 text-xs bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400 rounded hover:bg-purple-200 dark:hover:bg-purple-900/30">Acknowledge?</button>
											<button v-if="order.order_status === 'acknowledged'" @click="updateOrderStatusNew(order, 'shipped')" class="px-2 py-1 text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/30">Ready to Ship?</button>
											<button v-if="order.order_status === 'shipped'" @click="updateOrderStatusNew(order, 'delivered')" class="px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/30">Ready to Deliver?</button>
										</div>
									</div>
								</td>
							</tr>
						</template>
					</tbody>
				</table>
			</div>

			<div v-if="filteredOrders.length === 0 && !tableLoader" class="text-center py-12">
				<div
                    class="mx-auto h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <ShoppingCart class="h-8 w-8 text-gray-400 dark:text-gray-500" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No orders found</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Try adjusting your search or filter to find
                    what you're looking for.</p>
			</div>

			<template v-if="!tableLoader">
				<Pagination :pagination="pagination" @update:page="getAllOrders" />
			</template>
		</div>

		<!-- View Order Modal -->
		<BaseModal v-model="showOrderModal" title="Order Details">
			<template v-if="selectedOrder">
				<div
					class="flex items-center bg-gradient-to-r from-blue-50 to-indigo-50 
					dark:from-blue-900/20 dark:to-indigo-900/20 
					p-4 rounded-xl border border-blue-100 dark:border-blue-800 shadow-sm"
				>
					<div class="p-3 bg-blue-600 text-white rounded-lg shadow-md mr-3">
						<Package class="w-6 h-6" />
					</div>

					<div class="flex-1 flex flex-col">
						<p class="text-sm text-gray-700 dark:text-gray-300">
							Code:
							<span class="font-semibold text-blue-700 dark:text-blue-300">
								{{ selectedOrder.order_code }}
							</span>
						</p>

						<span
							:class="[
								'mt-1 w-fit inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap',
								getStatusColor(selectedOrder.order_status),
							]"
						>
							<CircleCheck class="w-4 h-4" />
							{{ selectedOrder.order_status.charAt(0).toUpperCase() + selectedOrder.order_status.slice(1) }}
						</span>
					</div>
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
							<div v-if="selectedOrder.manufacturer_name" class="flex items-center space-x-3">
								<Factory class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Manufacturer</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.manufacturer_name }}</p>
								</div>
							</div>
						</div>
						<div class="space-y-4">
							<div class="flex items-center space-x-3">
								<Calendar1 class="w-5 h-5 text-orange-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Date</p>
									<p class="text-gray-900 dark:text-white">{{ formatDateTime(selectedOrder.ordered_at) }}</p>
								</div>
							</div>
							<div v-if="selectedOrder.tracking_num" class="flex items-center space-x-3">
								<Truck class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Number</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.tracking_num }}</p>
								</div>
							</div>
							<div v-if="selectedOrder.ivr_num" class="flex items-center space-x-3">
								<ShieldCheck class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">IVR</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.ivr_num }}</p>
								</div>
							</div>
							<div v-if="selectedOrder.order_number" class="flex items-center space-x-3">
								<FileText class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Number</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.order_number }}</p>
								</div>
							</div>
							<div v-if="selectedOrder.tracking_code" class="flex items-center space-x-3">
								<TruckElectric class="w-5 h-5 text-gray-600" />
								<div>
									<p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tracking Code</p>
									<p class="text-gray-900 dark:text-white">{{ selectedOrder.tracking_code }}</p>
								</div>
							</div>
						</div>
					</div>
					<div>
						<h3 class="text-lg font-medium border-t text-gray-900 dark:text-white mb-4">Order Items</h3>
						<div class="overflow-x-auto">
							<table class="w-full">
								<thead class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Brand</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Size</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
										<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ASP</th>
										<th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
									</tr>
								</thead>
								<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
									<tr v-for="(item, idx) in selectedOrder.items" :key="idx">
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getBrandName(item.brandId) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getSizeName(item.graft_id) }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.quantity }}</td>
										<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">${{ item.asp.toFixed(2) }}</td>
										<td class="px-4 py-3 text-right text-sm font-medium text-gray-900 dark:text-white">${{ (item.asp * item.quantity).toFixed(2) }}</td>
									</tr>
								</tbody>
								<tfoot class="bg-gray-50 dark:bg-gray-700">
									<tr>
										<td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white text-right">Total Amount:</td>
										<td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">${{ selectedOrder.items.reduce((sum, item) => sum + (item.asp * item.quantity), 0).toFixed(2) }}</td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div>
						<h3 class="text-lg font-medium border-t text-gray-900 dark:text-white mb-4">Product Items</h3>
						<div class="overflow-x-auto">
							<!-- Other Products Table -->
							<div v-if="selectedOrder.other_product_items && selectedOrder.other_product_items.length > 0">
								<table class="w-full">
									<thead class="bg-gray-50 dark:bg-gray-700">
										<tr>
											<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product Name</th>
											<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
											<th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Price</th>
										</tr>
									</thead>
									<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
										<tr v-for="(item, idx) in selectedOrder.other_product_items" :key="`other-${idx}`">
											<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ getOtherProductName(item.other_product_id) }}</td>
											<td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ item.quantity }}</td>
											<td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white">${{ item.price.toFixed(2) }}</td>
										</tr>
									</tbody>
									<tfoot class="bg-gray-50 dark:bg-gray-700">
										<tr>
											<td colspan="2" class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white text-right">Other Products Total:</td>
											<td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">${{ selectedOrder.other_product_items.reduce((sum, item) => sum + (item.price * item.quantity), 0).toFixed(2) }}</td>
										</tr>
									</tfoot>
								</table>
							</div>

							<!-- Overall Total -->
							<div v-if="(selectedOrder.items && selectedOrder.items.length > 0) || (selectedOrder.other_product_items && selectedOrder.other_product_items.length > 0)" 
								class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
								<div class="text-right">
									<span class="text-lg font-bold text-gray-900 dark:text-white">
										Overall Total: ${{ 
											(
												(selectedOrder.items?.reduce((sum, item) => sum + (item.asp * item.quantity), 0) || 0) + 
												(selectedOrder.other_product_items?.reduce((sum, item) => sum + (item.price * item.quantity), 0) || 0)
											).toFixed(2) }}
									</span>
								</div>
							</div>
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
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
							Update Status
						</h3>
						<div class="flex items-center justify-between w-full">
							<div class="flex space-x-3">
								<button
									v-if="selectedOrder.order_status === 'submitted'"
									@click="updateOrderStatusNew(selectedOrder.order_id, 'acknowledged')"
									class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
								>
									Mark Acknowledged
								</button>
								<button
									v-if="selectedOrder.order_status === 'acknowledged'"
									@click="updateOrderStatusNew(selectedOrder.order_id, 'shipped')"
									class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
								>
									Mark Shipped
								</button>
								<button
									v-if="selectedOrder.order_status === 'shipped'"
									@click="updateOrderStatusNew(selectedOrder.order_id, 'delivered')"
									class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
								>
									Mark Delivered
								</button>
								<span
									v-if="selectedOrder.order_status === 'delivered'"
									class="bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 px-3 py-1 rounded-full text-sm"
								>
									Delivered
								</span>
							</div>

							<!-- RIGHT: FOLLOW-UP BUTTON + COOLDOWN -->
							<div class="flex items-center space-x-3">
								<div v-if="isFollowUpCooldown && selectedOrder.order_status === 'submitted'" class="flex items-center space-x-2">
									<div class="relative group">
										<CircleQuestionMark class="w-5 h-5 text-gray-500 dark:text-gray-400 cursor-help hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" />
										<div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 dark:bg-gray-950 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity whitespace-nowrap z-10">
											Last Follow-Up: {{ selectedOrder.followup_last_sent_at ? formatDateTime(selectedOrder.followup_last_sent_at) : 'No previous follow-up' }}
										</div>
									</div>
									<p class="text-sm text-gray-600 dark:text-gray-300">Follow-up available in {{ followUpHoursLeft }}h</p>
								</div>
								<button
									v-if="selectedOrder.order_status === 'submitted'"
									:disabled="isFollowUpCooldown"
									@click="sendFollowUp(selectedOrder)"
									class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center gap-1"
								>
									<Send class="w-5 h-5" />
									Send Follow-Up Email?
								</button>
							</div>
						</div>
					</div>

					<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 border-t pt-5">
						Order File
					</h3>
					<div v-if="filePreviewUrl" class="border rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 p-3" style="margin-top: unset !important;">
						<div v-if="isImageFile(filePreviewUrl)" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
							<img 
								:src="`${API_URL}/private-file/${selectedOrder.order_file}`"
								:alt="selectedOrder.order_file"
								class="max-w-full h-auto rounded-lg shadow-md"
								
							/>
						</div>
						<div v-else-if="isPDFFile(filePreviewUrl)" class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
							<iframe 
								:src="`${API_URL}/private-file/${selectedOrder.order_file}`"
								class="w-full h-96 rounded-lg"
								frameborder="0"
							></iframe>
						</div>
						<div v-else class="text-center py-8 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700/50">
							<File class="w-16 h-16 text-gray-400 mx-auto mb-3" />
							<p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Preview not available for this file type</p>
							<p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Download to view the file</p>
						</div>
					</div>

					<div class="border-t border-gray-200 pt-6">
						<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
							Override Status
						</h3>

						<div class="flex items-center justify-between w-full">
							<div class="flex space-x-3 items-center">
								<select
									v-model="overrideStatus"
									class="w-56 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 
									bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 
									shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
									transition-all duration-150 cursor-pointer
									disabled:opacity-50 disabled:cursor-not-allowed"
								>
									<option disabled value="">Select Status</option>

									<option 
										value="submitted"
										:disabled="selectedOrder.order_status === 'submitted'"
									>
										Submitted
									</option>

									<option 
										value="acknowledged"
										:disabled="selectedOrder.order_status === 'acknowledged'"
									>
										Acknowledged
									</option>

									<option 
										value="shipped"
										:disabled="selectedOrder.order_status === 'shipped'"
									>
										Shipped
									</option>

									<option 
										value="delivered"
										:disabled="selectedOrder.order_status === 'delivered'"
									>
										Delivered
									</option>
								</select>

								<button
									:disabled="!overrideStatus || overrideStatus === selectedOrder.order_status"
									@click="applyOverride"
									class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors 
										disabled:opacity-50 disabled:cursor-not-allowed"
								>
									Apply Override
								</button>
							</div>
						</div>
					</div>
				</div>
			</template>
			<template #actions>
				<div class="p-4 flex items-center gap-2">
					<button
					type="button"
					@click="closeForm"
					class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
						Cancel
					</button>
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
						<select v-model="formData.clinicianId" :disabled="!formData.clinicId" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
				</div>

				<!-- IVR Section -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">List of Requested IVR's <span class="text-red-500">*</span></label>
					<select
						v-model="formData.ivrId"
						:disabled="!formData.patientId"
						required
						class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
					>
						<option value="">Select requested IVR</option>

						<option 
							v-for="ivr in selectedPatientIVRs" 
							:key="ivr.ivr_id" 
							:value="String(ivr.ivr_id)"
						>
							{{ ivr.ivr_number }} [{{ ivr.manufacturer?.manufacturer_name }}]
						</option>
					</select>
					
					<div
					v-if="selectedIVR"
					class="flex items-center gap-2 text-sm text-gray-700 mt-2 mb-2"
					>
						<span class="font-semibold text-gray-600 dark:text-white">
							Eligibility Notification Email:
						</span>
						<span class="text-gray-900 dark:text-white">
							{{ selectedIVR.manufacturer?.order_email || '—' }}
						</span>
					</div>

					<div v-if="selectedIVR" class="mt-2">
						<div
							class="p-3 rounded-lg flex items-center space-x-2 border dark:border-opacity-40"
							:class="{
								'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Eligible',
								'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Not Eligible',
								'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Pending',
							}"
						>
							<!-- ICON -->
							<TriangleAlert
								class="w-5 h-5"
								:class="{
									'text-green-600 dark:text-green-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Eligible',
									'text-red-600 dark:text-red-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Not Eligible',
									'text-yellow-600 dark:text-yellow-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Pending',
								}"
							/>

							<!-- TEXT -->
							<span
								class="text-sm font-medium"
								:class="{
									'text-green-800 dark:text-green-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Eligible',
									'text-red-800 dark:text-red-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Not Eligible',
									'text-yellow-800 dark:text-yellow-400': getEligibilityLabel(selectedIVR.eligibility_status) === 'Pending',
								}"
							>
								IVR Status: {{ getEligibilityLabel(selectedIVR.eligibility_status) }}
							</span>
						</div>
					</div>
				</div>
				
				<!-- Order Items -->
				<div :class="{ 'opacity-40 pointer-events-none': !isSelectedIVREligible }">
					<div class="flex items-center justify-between mb-4">
						 <div class="flex items-center gap-2 mb-2">
							<Blocks class="w-5 h-5 text-green-500" />
							<h3 class="text-lg font-medium text-gray-900 dark:text-white">Order Items</h3>
						</div>
						<button type="button" @click="addOrderItem" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
							<PackagePlus class="w-4 h-4" />
							<span>Add Item</span>
						</button>
					</div>

					<p class="text-xs text-yellow-600 dark:text-yellow-400 mb-2">Note: You may exceed the MUE (Medically Unlikely Edits) for a product, but this will be flagged for review. You can still submit the order.</p>
					<div v-for="(item, idx) in formData.items" :key="item.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-[2fr_2fr_1fr_1fr_auto] gap-4 items-end">
							<!-- Brand -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand <span class="text-red-500">*</span></label>
								<select 
									v-model="item.brandId"
									@change="onBrandChange(idx)"
									required
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option value="">Select Brand</option>
									<option 
										v-for="brand in ivrBrands" 
										:key="brand.brand_id" 
										:value="brand.brand_id"
									>
										{{ brand.brand_name }}
									</option>
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
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg 
										bg-white dark:bg-gray-700 text-gray-900 dark:text-white
										disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed"
								>
									<option value="">Select Size</option>
									<option
										v-for="size in getAvailableSizesByBrand(item.brandId, idx)"
										:key="size.graft_size_id"
										:value="size.graft_size_id.toString()"
										:disabled="size.stock <= 0"
										:class="size.stock <= 0 ? 'text-gray-400' : ''"
									>
										{{ size.size }} (stock: {{ size.stock }})
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
						<div class="mt-2 space-y-1 text-xs">
							<!-- MUE warning -->
							<p v-if="!isQuantityValid(idx)" class="text-xs text-red-600 dark:text-red-400 mt-1">Exceeds MUE limit of {{ selectedBrand(idx)?.mue }} for this brand. You may still submit, but this will be flagged for review.</p>

							<!-- Stock warning -->
							<p v-if="!isStockValid(idx) && item.sizeId" class="text-orange-600 dark:text-orange-400">
								• Quantity ({{ item.quantity }}) > available stock ({{ getAvailableStock(idx) }})
							</p>

							<!-- Optional summary text -->
							<p v-if="!isQuantityValid(idx) || !isStockValid(idx)" class="text-gray-500 dark:text-gray-400 italic">
								Order can still be submitted, but may be flagged / rejected / partially filled.
							</p>
						</div>
					</div>
				</div>

				<!-- Product Items -->
				<div :class="{ 'opacity-40 pointer-events-none': !isSelectedIVREligible }">
					<div class="flex items-center justify-between mb-4">
						 <div class="flex items-center gap-2 mb-2">
							<Layers class="w-5 h-5 text-green-500" />
							<h3 class="text-lg font-medium text-gray-900 dark:text-white">Product Items</h3>
						</div>
						<button type="button" @click="addProductItem" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
							<CopyPlus class="w-4 h-4" />
							<span>Add Product</span>
						</button>
					</div>

					<div v-for="(product, idx) in formData.products" :key="product.id" class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
						<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-[2fr_1fr_1fr_auto] gap-4 items-end">
							<!-- Product Selection -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product <span class="text-red-500">*</span></label>
								<select 
									v-model="product.otherProductId"
									@change="onOtherProductChange(idx)"
									required
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								>
									<option value="">Select Product</option>
									<option 
										v-for="prod in getAvailableProducts(idx)" 
										:key="prod.other_product_id" 
										:value="prod.other_product_id"
									>
										{{ prod.product_name }} ({{ otherProductTypeMap[prod.product_type] }}) ({{ prod.stock }})
									</option>
								</select>
							</div>

							<!-- Quantity -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity <span class="text-red-500">*</span></label>
								<input v-model.number="product.quantity" type="number" min="1" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
							</div>

							<!-- Price -->
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									Price
								</label>
								<input
									type="text"
									:value="getProductPrice(idx)"
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-900/10"
									placeholder="-----"
									readonly
								/>
							</div>

							<!-- Remove -->
							<div class="flex justify-end md:justify-center items-end">
								<button type="button" @click="removeProductItem(idx)" class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10 rounded-lg transition-colors">
									<Trash2 class="w-4 h-4" />
								</button>
							</div>
						</div>
						<div class="mt-2 space-y-1 text-xs">
							<!-- Stock warning -->
							<p v-if="!isProductStockValid(idx) && product.otherProductId" class="text-orange-600 dark:text-orange-400">
								• Quantity ({{ product.quantity }}) > available stock ({{ getAvailableStockOtherProduct(idx) }})
							</p>
							<!-- Optional summary text -->
							<p v-if="!isProductStockValid(idx)" class="text-gray-500 dark:text-gray-400 italic">
								Order can still be submitted, but may be flagged / rejected / partially filled.
							</p>
						</div>
					</div>
				</div>

				<transition name="fade-slide">
					<div v-if="selectedIVR" class="relative">

						<!-- Loader Overlay -->
						<transition name="fade"> 
							<div
								v-if="isLoadingFile"
								class="absolute inset-0 bg-white/70 dark:bg-gray-900/60 backdrop-blur-sm flex flex-col items-center justify-center rounded-lg z-10"
							>
								<div class="w-60 bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden mb-3">
									<div
										class="h-full bg-purple-500 transition-all duration-100"
										:style="{ width: loadProgress + '%' }"
									></div>
								</div>

								<p class="text-sm text-gray-700 dark:text-gray-300">
									Preparing file... {{ loadProgress }}%
								</p>
							</div>
						</transition>

						<!-- Upload Box -->
						<div :class="{ 'blur-sm': isLoadingFile }">
							<div class="flex items-center gap-2 mb-2">
								<Folder class="w-5 h-5 text-green-500" />
								<h3 class="text-md font-semibold text-gray-900 dark:text-gray-100">Order File</h3>
							</div>

							<div v-if="isCreateMode ? !selectedFile : (!existingFile && !selectedFile)"
								class="mt-1 flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer 
									bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
								@drop="handleDrop"
								@dragover="allowDrop"
							>
								<input
									id="order-upload"
									type="file"
									accept=".pdf,.doc,.docx"
									class="hidden"
									@change="handleFileChange"
								/>

								<label for="order-upload" class="text-center cursor-pointer">
									<div class="mx-auto w-16 h-16 bg-purple-100 dark:bg-purple-900/40 rounded-full flex items-center justify-center mb-3">
										<CloudUpload class="w-8 h-8 text-purple-500" />
									</div>

									<p class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
										<span class="text-purple-600 dark:text-purple-400">Click to upload</span> or drag and drop
									</p>
									<p class="text-xs text-gray-500 dark:text-gray-400">PDF or DOCX (max. 10MB)</p>
								</label>
							</div>

							<!-- File Preview -->
							<div
								v-if="selectedFile" 
								class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg"
							>
								<div class="flex items-center gap-2">
									<FileText class="w-4 h-4 text-gray-400" />
									<div>
										<p class="font-medium">{{ selectedFile.name }}</p>
										<p class="text-xs text-gray-500">
											Size: {{ (selectedFile.size / 1024 / 1024).toFixed(2) }} MB • Type: {{ selectedFile.type || 'N/A' }}
										</p>
									</div>
								</div>

								<!-- Remove Button -->
								<button 
									@click="removeFile"
									class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition"
									title="Remove file"
								>
									<X class="w-5 h-5" />
								</button>
							</div>
							
							<!-- Existing file (Edit mode only) -->
							<div
								v-if="!showCreateForm && existingFile"
								class="mt-3 flex items-center justify-between gap-3 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-lg"
							>
								<div class="flex items-center gap-2">
									<FileText class="w-4 h-4 text-gray-400" />
									<div>
										<p class="font-medium">{{ existingFile.name }}</p>
										<p class="text-xs text-gray-500">Current uploaded file</p>
									</div>
								</div>

								<div class="inline-flex items-center gap-1">
								<a 
									:href="existingFile.url" 
									target="_blank"
									class="text-blue-600 hover:underline"
								>
									<Eye class="w-5 h-4" />
								</a>
								<button 
									@click="removeExistingFile"
									class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 transition"
									title="Remove file"
								>
									<X class="w-5 h-5" />
								</button>
								</div>
							</div>
						</div>
					</div>
				</transition>

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
    Search, Funnel, Eye, CircleCheck,
	Truck, Box, TriangleAlert, CircleUser,
	Calendar1, FileTextIcon, SquarePen,
	PackagePlus, ShoppingCart, Trash2,
	ChevronDown, Package, Send, ShieldCheck,
	Factory, TruckElectric, Folder, CloudUpload,
	FileText, BaggageClaim, X, Blocks,
	Layers, CopyPlus, CircleQuestionMark
} from 'lucide-vue-next';
import api from '@/services/api'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import Swal from 'sweetalert2'

interface Order {
	order_id: number;
	order_code: string
	order_number: string
	ordered_at: string;
	followup_last_sent_at: string
	order_status: OrderStatus;
	notes?: string;
	items: OrderItem[];
	other_product_items?: OtherProductItem[];
	tracking_num?: string;
	tracking_code?: string;
	ivr_num: string
	manufacturer_name: string
	order_file: string

	clinic: Clinic
	clinician: Clinician
	patient: PatientInfo
	ivr?: IVR
	// brand: Brand
}

interface OtherProductItem {
	other_product_id: number;
	quantity: number;
	price: number;
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
		order_email: string | string[]
	}
}

interface OtherProduct {
	other_product_id: number
	product_type: number
	product_name: string
	price: number
	stock: number
	description: string
	status: number
}

interface GraftSize {
	graft_size_id: number
	size: string
	area: string
	price: number
	stock: number
	graft_status: number
	brand: Brand
}

interface IVR {
	ivr_id: number
	ivr_number: string
	eligibility_status: number
	manufacturer: Manufacturer
}

interface Manufacturer {
	manufacturer_id: string
	manufacturer_name: string
	order_email: string
	brands: Brand[]
}

interface User {
	id: number
	first_name: string
	last_name: string
	brand: Brand[]
}

interface OrderIssue {
	type: 'mue' | 'stock';
	sourceType?: 'item' | 'product';
	itemIndex: number;
	brandName: string;
	mue?: number;
	requested: number;
	size?: string;
	available?: number;
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
	graftStock?: number
}

const API_URL = process.env.VUE_APP_API_URL;

// 0-submitted | 1-acknowledged | 2-shipped | 3-delivered | 4-cancelled
type OrderStatus = 'submitted' | 'acknowledged' | 'shipped' | 'delivered' | 'cancelled'

const overrideStatus = ref<string>('');

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

type otherProductType = 'Wound Supplies' | 'Devices'

const otherProductTypeMap: Record<number, string> = {
	0: 'Wound Supplies',
	1: 'Devices',
}

const isRevertingIVR = ref(false)
const tableLoader = ref(false);
const orders = ref<Order[]>([])
const clinics = ref<Clinic[]>([])
const patients = ref<PatientInfo[]>([])
const brands = ref<Brand[]>([])
const graftSizes = ref<GraftSize[]>([])
const otherProducts = ref<OtherProduct[]>([])
const itemsPerPage = ref(10)
const pagination = ref({
	current_page: 1,
	last_page: 1,
	per_page: 10,
	total: 0,
})

// file handling
const isLoadingFile = ref(false)
const loadProgress = ref(0)
const selectedFile = ref<File | null>(null)

const searchTerm = ref('')
const statusFilter = ref('all')

const selectedOrder = ref<Order | null>(null)
const showOrderModal = ref(false)
const showCreateForm = ref(false)
const isCreateMode = computed(() => showCreateForm.value);
const showEditForm = ref(false)
const showFormModal = ref(false)
const selectedOrderForEdit = ref<Order | null>(null)

const previousIvrId = ref<string | number | null>(null)

const formData = ref({
	clinicId: '',
	clinicianId: '',
	patientId: '',
	ivrId: '',
	brandId: '',
	order_email: '',
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
			deviceType: '',
			graftStock: 0
		}
	],
	products: [
		{
			id: Date.now().toString(),
			otherProductId: '',
			brandId: '',
			manufacturerId: '',
			otherProductType: 0,
			otherProductName: '',
			quantity: 1,
			otherProductStock: 0,
			price: 0
		}
	],
	manufacturerId: '',
	trackingNumber: '',
	order_file: ''
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

const selectedIVR = computed(() => {
	if (!formData.value.ivrId || !selectedPatient.value) return null

	return selectedPatient.value.ivrs.find(
		ivr => ivr.ivr_id === Number(formData.value.ivrId)
	) || null
})

const ivrBrands = computed(() => {
	return selectedIVR.value?.manufacturer?.brands ?? []
})

const selectedPatientIVRs = computed(() => {
	return selectedPatient.value?.ivrs ?? []
})

const isSelectedIVREligible = computed(() => {
	return selectedIVR.value?.eligibility_status === 1
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

function getOtherProductName(otherProductId: string | number) {
	const id = Number(otherProductId)
	const p = otherProducts.value.find(op => Number(op.other_product_id) === id)
	return p?.product_name ?? p?.product_name ?? `Product ${otherProductId}`
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

const isStockValid = (idx: number) => {
    const item = formData.value.items[idx]
    if (!item.sizeId) return true

    const graft = graftSizes.value.find(g => g.graft_size_id.toString() === item.sizeId)
    if (!graft) return true

    const availableStock = graft.stock ?? 0
    return item.quantity <= availableStock
}

const getAvailableStock = (idx: number) => {
    const item = formData.value.items[idx]
    if (!item.sizeId) return 0
    const graft = graftSizes.value.find(g => g.graft_size_id.toString() === item.sizeId)
    return graft?.stock ?? 0
}

const getAvailableStockOtherProduct = (idx: number) => {
	const product = formData.value.products[idx]
	if (!product.otherProductId) return 0
	const otherProduct = otherProducts.value.find(p => p.other_product_id === Number(product.otherProductId))
	return otherProduct?.stock ?? 0
}

const isProductStockValid = (idx: number) => {
	const product = formData.value.products[idx]
	if (!product.otherProductId) return true

	const otherProduct = otherProducts.value.find(p => p.other_product_id === Number(product.otherProductId))
	if (!otherProduct) return true

	const availableStock = otherProduct.stock ?? 0
	return product.quantity <= availableStock
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
		deviceType: '',
		graftStock: 0
	})
}

function removeOrderItem(idx: number) {
	if (formData.value.items.length > 1) formData.value.items.splice(idx, 1)
}

function addProductItem() {
	formData.value.products.push({
		id: Date.now().toString(),
		otherProductId: '',
		brandId: '',
		manufacturerId: '',
		otherProductType: 0,
		otherProductName: '',
		quantity: 1,
		otherProductStock: 0,
		price: 0
	})
}

function removeProductItem(idx: number) {
	if (formData.value.products.length > 1) formData.value.products.splice(idx, 1)
}

function getProductPrice(idx: number) {
	const product = formData.value.products[idx]
	if (!product || !product.otherProductId) return '-----'

	const otherProduct = otherProducts.value.find(p => p.other_product_id === Number(product.otherProductId))
	if (!otherProduct) return '-----'

	const qty = Number(product.quantity ?? 1) || 1
	const unit = Number(otherProduct.price ?? 0) || 0

	const totalPrice = (unit * qty).toFixed(2)
	return `$${totalPrice}`
}

function resetOrderItems() {
	formData.value.items = [
		{
			id: Date.now().toString(),
			brandId: '',
			productType: 0 as const,
			sizeId: '',
			graft_id: 0,
			quantity: 1,
			asp: 0,
			totalAsp: 0,
			deviceType: '',
			graftStock: 0
		}
	]
}

function resetCreateForm() {
	formData.value = {
		clinicId: '',
		clinicianId: '',
		patientId: '',
		brandId: '',
		ivrId: '',
		order_email: '',
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
			deviceType: '',
			graftStock: 0
		}],
		products: [
			{
				id: Date.now().toString(),
				otherProductId: '',
				brandId: '',
				manufacturerId: '',
				otherProductType: 0,
				otherProductName: '',
				quantity: 1,
				otherProductStock: 0,
				price: 0
			}
		],
		manufacturerId: '',
		trackingNumber: '',
		order_file: ''
	}
	previousIvrId.value = null
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
        toast.error('Selected patient is not eligible for ordering')
        return
    }
    if (!formData.value.items.length && !formData.value.products.length) {
        toast.error('Please add at least one item or product to the order')
        return
    }

    // ────────────────────────────────────────────────
    //  Collect validation issues
    // ────────────────────────────────────────────────
    const mueIssues: OrderIssue[] = []
    const stockIssues: OrderIssue[] = []

    // Check grafts / main items
    formData.value.items.forEach((item, idx) => {
        const brand = selectedBrand(idx)
        const graft = graftSizes.value.find(g => g.graft_size_id.toString() === item.sizeId)

        if (!brand || !item.brandId) {
            toast.error(`Item #${idx + 1}: Brand is required`)
            return
        }
        if (!item.sizeId) {
            toast.error(`Item #${idx + 1}: Size is required`)
            return
        }
        if (!item.quantity || item.quantity < 1) {
            toast.error(`Item #${idx + 1}: Quantity must be at least 1`)
            return
        }

        // MUE violation (still allowed with confirmation)
        if (brand && item.quantity > brand.mue) {
            mueIssues.push({
                type: 'mue',
                itemIndex: idx + 1,
                brandName: brand.brand_name,
                mue: brand.mue,
                requested: item.quantity
            })
        }

        // Stock violation → BLOCK submission
        if (graft && item.quantity > graft.stock) {
            stockIssues.push({
                type: 'stock',
                sourceType: 'item',
                itemIndex: idx + 1,
                brandName: brand?.brand_name || '—',
                size: graft.size,
                available: graft.stock,
                requested: item.quantity
            })
        }
    })

    // Check additional products
    formData.value.products.forEach((product, idx) => {
        if (!product.otherProductId) return

        const otherProduct = otherProducts.value.find(p => p.other_product_id === Number(product.otherProductId))

        if (!otherProduct) {
            toast.error(`Product #${idx + 1}: Selected product not found`)
            return
        }

        if (product.quantity < 1) {
            toast.error(`Product #${idx + 1}: Quantity must be at least 1`)
            return
        }

        if (product.quantity > otherProduct.stock) {
            stockIssues.push({
                type: 'stock',
                sourceType: 'product',
                itemIndex: idx + 1,
                brandName: otherProduct.product_name,
                size: `(${otherProductTypeMap[otherProduct.product_type]})`,
                available: otherProduct.stock,
                requested: product.quantity
            })
        }
    })

    // ────────────────────────────────────────────────
    //  1. BLOCK if any stock issue exists
    // ────────────────────────────────────────────────
    if (stockIssues.length > 0) {
        let message = '<div class="text-left space-y-3 text-sm text-red-700 dark:text-red-300">'
        message += '<p class="font-semibold text-base mb-2">Insufficient stock — order cannot be placed</p>'

        stockIssues.forEach(issue => {
            const label = issue.sourceType === 'product' ? 'Product' : 'Item'
            message += `
                <div class="border-l-4 border-red-500 pl-3">
                    <strong>${label} #${issue.itemIndex}</strong> – ${issue.brandName} ${issue.size || ''}<br>
                    Requested: <strong>${issue.requested}</strong><br>
                    Available: <strong>${issue.available}</strong>
                </div>
            `
        })

        message += '</div>'

        await Swal.fire({
            title: 'Cannot Submit Order',
            html: message,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc2626',
            allowOutsideClick: false
        })

        return   // ← STOP here — do NOT proceed to submission
    }

    // ────────────────────────────────────────────────
    //  2. If there are MUE violations → ask for confirmation
    // ────────────────────────────────────────────────
    if (mueIssues.length > 0) {
        let html = '<div class="text-left space-y-3 text-sm">'
        html += '<p class="font-semibold text-amber-700 dark:text-amber-300 mb-3">The following items exceed the MUE limit:</p>'

        mueIssues.forEach(issue => {
            html += `
                <p class="text-amber-700 dark:text-amber-300">
                    <strong>Item #${issue.itemIndex}</strong> – ${issue.brandName}<br>
                    Requested: <strong>${issue.requested}</strong>  
                    (MUE limit: <strong>${issue.mue}</strong>)
                </p>
            `
        })

        html += `
            <p class="text-gray-600 dark:text-gray-400 pt-4 italic border-t border-gray-200 dark:border-gray-700">
                You can still submit this order, but it <strong>will likely be flagged for review</strong>.
            </p>
        </div>`

        const result = await Swal.fire({
            title: 'MUE Limit Exceeded',
            html: html,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Submit Anyway',
            cancelButtonText: 'Review Order',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            reverseButtons: true
        })

        if (!result.isConfirmed) {
            toast.info('Order submission cancelled — please adjust quantities')
            return
        }
    }

    // ────────────────────────────────────────────────
    //  If we reached here → no blocking stock issues
    //  (MUE was either ok or user confirmed)
    // ────────────────────────────────────────────────
    addNewOrder()
}

function openCreateForm() {
	showCreateForm.value = true
	showEditForm.value = false
	selectedOrderForEdit.value = null
	resetCreateForm()
	showFormModal.value = true
	previousIvrId.value = null
}

async function editOrder(order: Order) {
	const { data } = await api.get(`/management/order/${order.order_id}/getorderbyid`);
	const fullOrder = data.order;

	selectedOrderForEdit.value = fullOrder;
	showCreateForm.value = false;
	showEditForm.value = true;

	formData.value = {
		clinicId: fullOrder.clinic?.clinic_id?.toString() || '',
		clinicianId: fullOrder.clinician?.id?.toString() || '',
		patientId: fullOrder.patient?.patient_id?.toString() || '',
		brandId: '',
		ivrId: fullOrder.ivr?.ivr_id?.toString() || '',
		order_email: '',
		sizeId: '',
		dateOfOrder: fullOrder.ordered_at
			? new Date(fullOrder.ordered_at).toISOString().split('T')[0]
			: '',
		status: 'submitted',
		notes: fullOrder.notes || '',
		items: [],
		products: [],
		manufacturerId: fullOrder.manufacturer_id?.toString() || '',
		trackingNumber: fullOrder.tracking_num || '',
		order_file: fullOrder.order_file || ''
	};

	showFormModal.value = true;

	await nextTick();
	await new Promise(resolve => setTimeout(resolve, 50));

	formData.value.items = fullOrder.items.map((item: any, idx: number) => ({
		id: String(item.id ?? idx),
		brandId: String(item.brandId ?? item.brand_id ?? ''),
		productType: Number(item.productType ?? item.product_type ?? 0) as 0 | 1,
		sizeId: String(item.sizeId ?? item.size_id ?? item.graft_id ?? ''),
		graft_id: Number(item.graft_id ?? 0),
		quantity: Number(item.quantity ?? 0),
		asp: Number(item.asp ?? 0),
		totalAsp: Number(item.asp ?? 0) * Number(item.quantity ?? 0),
		deviceType: item.deviceType ?? item.device_type ?? '',
		graftStock: Number(item.graftStock ?? 0)
	}));

	formData.value.products = (fullOrder.products ?? []).map((p: any, idx: number) => ({
		id: String(p.id ?? idx),
		otherProductId: String(p.other_product_id ?? ''),
		brandId: String(p.brandId ?? p.brand_id ?? ''),
		manufacturerId: String(p.manufacturer_id ?? ''),
		otherProductType: Number(p.product_type ?? p.otherProductType ?? 1) as 0 | 1,
		otherProductName: p.product_name ?? p.product_name ?? '',
		quantity: Number(p.quantity ?? 0),
		otherProductStock: Number(p.stock ?? 0),
		price: Number(p.price ?? 0)
	}));
}

function closeForm() {
	showFormModal.value = false
	showCreateForm.value = false
	showEditForm.value = false
	selectedOrderForEdit.value = null
	showOrderModal.value = false
	resetCreateForm()
}

function showOrderDetails(order: Order) {
	selectedOrder.value = order
	showOrderModal.value = true
	overrideStatus.value = order.order_status;
}

const filteredOrders = computed(() => {
	const term = searchTerm.value.trim().toLowerCase();

	return (orders.value ?? []).filter(order => {
		const orderCode = order.order_code?.toLowerCase() || '';
		const clinicCode = order.clinic?.clinic_code?.toLowerCase() || '';
		const clinicName = order.clinic?.clinic_name?.toLowerCase() || '';
		const patientName = order.patient?.patient_name?.toLowerCase() || '';
		const orderStatus = order.order_status?.toLowerCase() || '';

		const matchesSearch =
			term === '' ||
			orderCode.includes(term) ||
			clinicCode.includes(term) ||
			clinicName.includes(term) ||
			patientName.includes(term);

		const matchesStatus =
			statusFilter.value === 'all' ||
			orderStatus === statusFilter.value;

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

const formatDateTime = (dateStr: string) => {
	if (!dateStr) return '--';

	const date = new Date(dateStr);

	const month = date.toLocaleString('en-US', { month: 'short' }) + '.';
	const formattedDate = `${month} ${date.getDate()}, ${date.getFullYear()}`;

	const formattedTime = date.toLocaleTimeString('en-US', {
		hour: 'numeric',
		minute: '2-digit',
		hour12: true
	});

	return `${formattedDate} [${formattedTime}]`;
};

const isFollowUpCooldown = computed(() => {
	// Use last follow-up timestamp if present; otherwise fall back to the order date.
	const refDateStr = selectedOrder.value?.followup_last_sent_at || selectedOrder.value?.ordered_at;
	if (!refDateStr) return false;

	const parseTimestamp = (s: string) => {
		if (!s) return NaN;
		// Normalize common formats (space -> T) so Date.parse treats it as ISO when possible
		const normalized = typeof s === 'string' ? s.replace(' ', 'T') : s;
		let t = Date.parse(normalized);
		if (isNaN(t)) {
			try { t = new Date(s).getTime(); } catch { t = NaN }
		}
		return isNaN(t) ? NaN : t;
	};

	const last = parseTimestamp(refDateStr);
	if (isNaN(last)) return false;

	const now = Date.now();
	const hours = (now - last) / (1000 * 60 * 60);
	return hours < 24;
});

const followUpHoursLeft = computed(() => {
	const refDateStr = selectedOrder.value?.followup_last_sent_at || selectedOrder.value?.ordered_at;
	if (!refDateStr) return 0;

	const parseTimestamp = (s: string) => {
		if (!s) return NaN;
		const normalized = typeof s === 'string' ? s.replace(' ', 'T') : s;
		let t = Date.parse(normalized);
		if (isNaN(t)) {
			try { t = new Date(s).getTime(); } catch { t = NaN }
		}
		return isNaN(t) ? NaN : t;
	};

	const last = parseTimestamp(refDateStr);
	if (isNaN(last)) return 0;

	const now = Date.now();
	const hoursLeft = 24 - (now - last) / (1000 * 60 * 60);
	return Math.ceil(hoursLeft > 0 ? hoursLeft : 0);
});

function getSizesByBrand(brandId: string) {
	const sizes = graftSizes.value.filter(gs => gs.brand && gs.brand.brand_id == brandId)
	return sizes
}

function getAvailableSizesByBrand(brandId: string, currentItemIdx: number) {
	const sizes = graftSizes.value.filter(gs => gs.brand && gs.brand.brand_id == brandId)
	
	const selectedSizeIds = formData.value.items
		.map((item, idx) => idx !== currentItemIdx ? item.sizeId : null)
		.filter(id => id !== null && id !== '')
	
	return sizes.filter(size => !selectedSizeIds.includes(size.graft_size_id.toString()))
}

function getAvailableProducts(currentProductIdx: number) {
	const selectedProductIds = formData.value.products
		.map((product, idx) => idx !== currentProductIdx ? product.otherProductId?.toString() : null)
		.filter(id => id !== null && id !== '')
	
	return otherProducts.value.filter(product => !selectedProductIds.includes(product.other_product_id.toString()))
}

function onSizeChange(idx: number) {
	const item = formData.value.items[idx]
	
	const graft = graftSizes.value.find(
		g => g.graft_size_id.toString() === item.sizeId
	)
	
	if (!graft) {
		item.asp = 0
		item.graft_id = 0
		return
	}

	const stock = graftStockCheck(graft.graft_size_id)
	if (stock <= 0) {
		toast.error("This graft size is out of stock.")
		item.sizeId = ''
		item.graft_id = 0
		item.asp = 0
		return
	}

	// If IN STOCK -> proceed
	item.asp = graft.price
	item.graft_id = graft.graft_size_id
	item.graftStock = graft.stock
}

function onOtherProductChange(idx: number) {
	const product = formData.value.products[idx]
	if (!product || !product.otherProductId) {
		if (product) product.price = 0
		return
	}

	const otherProduct = otherProducts.value.find(p => p.other_product_id === Number(product.otherProductId))
	if (!otherProduct) {
		product.price = 0
		return
	}

	product.price = Number(otherProduct.price ?? 0)
	product.otherProductName = otherProduct.product_name ?? product.otherProductName
	product.otherProductStock = otherProduct.stock ?? product.otherProductStock
	product.otherProductType = Number(otherProduct.product_type ?? 0) as 0 | 1
}

function graftStockCheck(graftId: number) {
	const graft = graftSizes.value.find(g => g.graft_size_id === graftId)
	return graft?.stock ?? 0
}


// file upload
const simulateLoading = () => {
	isLoadingFile.value = true
	loadProgress.value = 0

	const interval = setInterval(() => {
		if (loadProgress.value >= 100) {
			clearInterval(interval)
			isLoadingFile.value = false
			return
		}
		loadProgress.value += 10
	}, 80)
}

const handleFileChange = (event: any) => {
	const file = event.target.files[0]
	if (!file) return

	selectedFile.value = file
	simulateLoading()
}

const handleDrop = (event: any) => {
	event.preventDefault()
	const file = event.dataTransfer.files[0]
	if (!file) return

	selectedFile.value = file
	simulateLoading()
}

const allowDrop = (event: any) => {
	event.preventDefault()
}

const removeFile = () => {
	selectedFile.value = null
	loadProgress.value = 0
	isLoadingFile.value = false
}

const removeExistingFile = () => {
	formData.value.order_file = '';
	selectedFile.value = null
	loadProgress.value = 0
	isLoadingFile.value = false
}

const filePreviewUrl = computed(() => {
    if (!selectedOrder.value?.order_file) return null;
    return `/storage/${selectedOrder.value.order_file}`;
});

function isImageFile(filename: string) {
    if (!filename) return false
    const ext = filename.split('.').pop()?.toLowerCase() || ''
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)
}

function isPDFFile(filename: string) {
    if (!filename) return false
    return filename.toLowerCase().endsWith('.pdf')
}

const existingFile = computed(() => {
    return formData.value.order_file ? {
        name: formData.value.order_file.split('/').pop(),
        url: formData.value.order_file
    } : null
})

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

			let rawOtherProductItems = o.other_product_items
			if (!Array.isArray(rawOtherProductItems) && typeof rawOtherProductItems === 'string') {
				try { rawOtherProductItems = JSON.parse(rawOtherProductItems) } catch { rawOtherProductItems = [] }
			}
			const otherProductItems: OtherProductItem[] = Array.isArray(rawOtherProductItems) ? rawOtherProductItems.map((it: any) => ({
				other_product_id: Number(it.other_product_id ?? 0),
				quantity: Number(it.quantity ?? 0),
				price: Number(it.price ?? 0)
			})) : []

			return {
				order_id: Number(o.order_id ?? 0),
				order_code: String(o.order_code ?? ''),
				ordered_at: String(o.ordered_at ?? o.created_at ?? ''),
				followup_last_sent_at: String(o.followup_last_sent_at ?? ''),
				order_status: (['submitted','acknowledged','shipped','delivered','cancelled'][Number(o.order_status ?? 0)] ?? 'submitted') as OrderStatus,
				notes: o.notes ?? '',
				items,
				other_product_items: otherProductItems,
				tracking_num: o.tracking_num ?? '',
				order_number: o.order_number ?? '',
				tracking_code: o.tracking_code ?? '',
				clinic: o.clinic,
				clinician: o.clinician,
				patient: o.patient,
				brand: o.brand,
				ivr_num: o?.ivr?.ivr_number ?? '',
				manufacturer_name: o?.ivr?.manufacturer.manufacturer_name ?? '',
				order_file: o.order_file ?? ''
			} as Order
		})

		pagination.value = {
            current_page: Number(data?.meta?.current_page ?? 1),
            last_page: Number(data?.meta?.last_page ?? 1),
            per_page: Number(data?.meta?.per_page ?? itemsPerPage.value),
            total: Number(data?.meta?.total ?? rows.length),
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

async function getAllGraftSizes()
{
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

async function getAllOtherProducts()
{
	try {
		const { data } = await api.get(`/management/order/getotherproducts`, {
			headers: {
				Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			}
		})
		otherProducts.value = data.other_product_data || []
	} catch (error) {
		console.error('Error loading other products:', error)
	} finally {
		tableLoader.value = false
	}
}

async function getAllOtherProductsById(otherProductId: string)
{
	try {
		const { data } = await api.get(`/management/order/getotherproducts/${otherProductId}`, {
			headers: {
				Authorization: `Bearer ${localStorage.getItem('auth_token')}`
			}
		})
		otherProducts.value = data.other_product_data || []
	} catch (error) {
		console.error('Error loading other products:', error)
	} finally {
		tableLoader.value = false
	}
}

async function addNewOrder(){
	const payload = new FormData()

	payload.append('clinic_id', formData.value.clinicId)
	payload.append('clinician_id', formData.value.clinicianId)
	payload.append('patient_id', formData.value.patientId)
	payload.append('order_email', formData.value.order_email)
	payload.append('notes', formData.value.notes)
	payload.append('ivr_id', formData.value.ivrId)
	payload.append('manufacturer_id', formData.value.manufacturerId)

	payload.append(
		'items',
		JSON.stringify(
			formData.value.items.map(item => ({
				brand_id: item.brandId,
				graft_id: item.sizeId,
				ivr_id: formData.value.ivrId,
				quantity: item.quantity,
				asp: item.asp,
				product_type: item.productType,
				device_type: item.deviceType,
				graftStock: item.graftStock
			}))
		)
	)

	payload.append(
		'products',
		JSON.stringify(
			formData.value.products.map(product => ({
				other_product_id: product.otherProductId,
				quantity: product.quantity,
				product_type: product.otherProductType,
				price: product.price
			}))
		)
	)

	console.log('order file:', selectedFile.value)

	if (selectedFile.value) {
		payload.append('order_file', selectedFile.value)
	}

	Swal.fire({
		title: 'Processing Order',
		text: 'Please wait while we submit your order…',
		allowOutsideClick: false,
		allowEscapeKey: false,
		showConfirmButton: false,
		didOpen: () => {
			Swal.showLoading()
		}
	})

	try {
		if (showCreateForm.value) {
			const { data } = await api.post(
				'/management/order/add/neworder',
				payload,
				{
					headers: {
						Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
						'Content-Type': 'multipart/form-data'
					}
				}
			)

			Swal.close()
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

			Swal.close()
			toast.success(data.message || 'Order details updated Successfully!')
			await getAllOrders(1)
			closeForm()
		}
		
	} catch (error: any) {
		Swal.close()
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

async function applyOverride() {	
	if (!overrideStatus.value) return;

	const result = await Swal.fire({
		title: "Override Status?",
		text: `Set status to "${overrideStatus.value}"`,
		icon: "warning",
		showCancelButton: true,
		confirmButtonText: "Yes, override",
		cancelButtonText: "Cancel",
		confirmButtonColor: "#d33"
	});

	if (!result.isConfirmed) return;

	try {
		const statusNumber = {
			submitted: 0,
			acknowledged: 1,
			shipped: 2,
			delivered: 3
		}[overrideStatus.value];

		await api.put(
			`/management/order/update/${selectedOrder.value?.order_id}/updateorderstatus`,
			{ order_status: statusNumber }
		);

		toast.success("Order status overridden successfully!");

		overrideStatus.value = "";
		await getAllOrders(1);
		closeForm();

	} catch (error) {
		console.error(error);
		toast.error("Failed to override status.");
	}
}

async function sendFollowUp(order: Order){
	console.log('order: ', order);
	
	try {
		const result = await Swal.fire({
			title: "Send Follow-Up Email?",
			text: `This will notify ${order?.ivr?.manufacturer?.order_email || order?.manufacturer_name} again about this order.`,
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#4f46e5",
			cancelButtonColor: "#d33",
			confirmButtonText: "Send Follow-Up"
		})

		if (!result.isConfirmed) return

		Swal.fire({
			title: 'Sending Follow-Up',
			text: 'Please wait while we send the follow-up notification to the manufacturer…',
			allowOutsideClick: false,
			allowEscapeKey: false,
			showConfirmButton: false,
			didOpen: () => {
				Swal.showLoading()
			}
		})

		const { data } = await api.post(`/management/order/update/${order.order_id}/followuporderstatus`, {})

		Swal.close()
		toast.success(data.message || "Follow-up email sent successfully!")
		getAllOrders(1)

	} catch (error: any) {
		const msg = error.response?.data?.message || "Failed to send follow-up."
		toast.error(msg)
	}
}

onMounted(async () => {
    getAllOrders(1)
	getAllClinics()
	getAllPatients()
	getAllOtherProducts()
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

watch(() => formData.value.ivrId, (newVal, oldVal) => {
	previousIvrId.value = oldVal
})

watch(
	() => formData.value.ivrId,
	async (newVal, oldVal) => {
		if (isRevertingIVR.value) {
			isRevertingIVR.value = false
			return
		}


		if (!formData.value.patientId) return
		if (!oldVal || oldVal === '') return
		if (newVal === oldVal) return

		if (newVal === oldVal) return

		const result = await Swal.fire({
			title: 'Change IVR?',
			text: 'Changing the IVR will reset all order items. Do you want to continue?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, reset items',
			cancelButtonText: 'Cancel'
		})

		if (result.isConfirmed) {
			resetOrderItems()
		} else {
			isRevertingIVR.value = true

			formData.value.ivrId = previousIvrId.value
				? String(previousIvrId.value)
				: ''
				
			Swal.close()
		}

		Swal.close()

		previousIvrId.value = formData.value.ivrId
	}
)

watch(() => formData.value.ivrId, () => {
	formData.value.items.forEach(item => {
		item.brandId = ''
		item.sizeId = ''
		item.quantity = 1
		item.asp = 0,
		item.graftStock = 0
	})
})

watch(() => formData.value.ivrId, () => {
	if (!selectedIVR.value) return

	formData.value.manufacturerId = selectedIVR.value.manufacturer?.manufacturer_id;
	formData.value.order_email = selectedIVR.value.manufacturer?.order_email;
	
	if (selectedIVR.value.eligibility_status !== 1) {
		toast.error(
			`This IVR is ${getEligibilityLabel(selectedIVR.value.eligibility_status)}. Ordering is disabled.`
		)
	}

})
</script>

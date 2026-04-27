<template>
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
			<div class="space-y-2">
				<h1 class="text-3xl font-bold text-gray-900 dark:text-white">Clinic Report</h1>
				<p class="text-gray-600 dark:text-gray-400 max-w-2xl">View comprehensive reports and analytics for your clinic</p>
			</div>
		</div>

		<!-- No Clinic Warning -->
		<div v-if="!isLoadingUser && !hasClinicAssigned" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6 text-center">
			<Building2 class="w-12 h-12 text-yellow-500 mx-auto mb-3" />
			<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Clinic Assigned</h3>
			<p class="text-gray-600 dark:text-gray-400">Your account is not assigned to any clinic. Please contact your administrator to get assigned to a clinic.</p>
		</div>
		
		<!-- Loading State -->
		<div v-if="isLoadingUser" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
			<svg class="animate-spin h-8 w-8 text-red-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
			<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Loading...</h3>
			<p class="text-gray-500 dark:text-gray-400">Please wait while we load your clinic data.</p>
		</div>
		
		<!-- Filters Card -->
		<div v-if="hasClinicAssigned && myClinic" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
			<div class="flex items-center justify-between mb-4">
				<div class="flex items-center space-x-3">
					<div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
						<Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
					</div>
					<div>
						<h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ myClinic.clinic_name }}</h3>
						<p class="text-xs text-gray-500 dark:text-gray-400">Clinic Code: {{ myClinic.clinic_code || 'N/A' }}</p>
					</div>
				</div>
				<button 
					@click="clearFilters"
					class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium flex items-center transition-colors"
				>
					<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
					</svg>
					Clear Filters
				</button>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<!-- Date Range -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
					<select
						v-model="dateRange"
						class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					>
						<option v-for="option in dateRangeOptions" :key="option.value" :value="option.value">
							{{ option.label }}
						</option>
					</select>
				</div>

				<!-- Brand Filter (Optional) -->
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Brand (Optional)</label>
					<select
						v-model="brandFilter"
						class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Brands</option>
						<option v-for="brand in brands" :key="brand.id" :value="brand.id">
							{{ brand.name }}
						</option>
					</select>
				</div>
			</div>

			<!-- Custom Date Range -->
			<div v-if="dateRange === 'custom'" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
					<input
						v-model="startDate"
						type="date"
						class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					/>
				</div>
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
					<input
						v-model="endDate"
						type="date"
						class="w-full px-3 py-2.5 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					/>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-0 sm:justify-between">
				<button
					@click="handleGenerateReport"
					:disabled="isLoadingData"
					class="flex items-center justify-center space-x-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md group disabled:opacity-50 disabled:cursor-not-allowed"
				>
					<ChartBarIcon v-if="!isLoadingData" class="w-4 h-4 text-white group-hover:scale-110 transition-transform" />
					<svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<span>{{ isLoadingData ? 'Loading...' : 'Generate Report' }}</span>
				</button>

				<div v-if="reportGenerated" class="contents sm:flex sm:flex-row sm:items-center sm:gap-3">
					<!-- Export Dropdown -->
					<div class="relative" ref="exportDropdownRef">
						<button
							@click="toggleExportDropdown"
							:disabled="isExporting"
							class="flex items-center justify-center space-x-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
						>
							<ArrowDownTrayIcon v-if="!isExporting" class="w-4 h-4" />
							<svg v-else class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
								<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
								<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
							</svg>
							<span>{{ isExporting ? 'Exporting...' : 'Export' }}</span>
							<svg class="w-3 h-3 ml-1.5 transition-transform" :class="showExportDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
							</svg>
						</button>
						
						<div
							v-if="showExportDropdown"
							class="absolute left-0 right-0 sm:right-auto sm:left-auto sm:w-44 mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
						>
							<div class="py-1">
								<button
									@click="exportAsPdf"
									class="w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2 transition-colors"
								>
									<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
									</svg>
									<span>Export as PDF</span>
								</button>
								<button
									@click="exportAsExcel"
									class="w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2 transition-colors"
								>
									<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
									</svg>
									<span>Export as Excel</span>
								</button>
							</div>
						</div>
					</div>

					<span class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center sm:justify-start whitespace-nowrap">
						<svg class="w-3 h-3 mr-1 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
						</svg>
						<span>Report generated</span>
					</span>
				</div>
			</div>
		</div>

		<!-- Report Preview -->
		<div v-if="hasClinicAssigned && myClinic && reportGenerated" class="space-y-6">
			<!-- Clinic Header -->
			<div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-lg p-6 text-white">
				<div class="flex items-center justify-between">
					<div>
						<h2 class="text-2xl font-bold">{{ myClinic.clinic_name }}</h2>
						<p class="text-blue-100 mt-1">Clinic Performance Report</p>
					</div>
					<div class="text-right">
						<span class="inline-flex items-center px-3 py-1.5 bg-white/20 backdrop-blur-sm text-white text-sm font-medium rounded-lg">
							{{ dateRangeLabel }}
						</span>
						<p class="text-blue-100 text-xs mt-2">Generated {{ currentTimestamp }}</p>
					</div>
				</div>
			</div>

			<!-- KPI Cards -->
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
				<!-- Total Orders -->
				<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Orders</p>
							<p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ clinicStats.totalOrders }}</p>
						</div>
						<div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl">
							<ClipboardDocumentListIcon class="w-6 h-6 text-blue-600 dark:text-blue-400" />
						</div>
					</div>
				</div>
			
				<!-- Total Patients -->
				<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Patients</p>
							<p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ clinicStats.totalPatients }}</p>
						</div>
						<div class="p-3 bg-green-50 dark:bg-green-900/30 rounded-xl">
							<svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
							</svg>
						</div>
					</div>
				</div>
			
				<!-- IVR Requests -->
				<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">IVR Requests</p>
							<p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ clinicStats.totalIVR }}</p>
						</div>
						<div class="p-3 bg-orange-50 dark:bg-orange-900/30 rounded-xl">
							<PhoneIcon class="w-6 h-6 text-orange-600 dark:text-orange-400" />
						</div>
					</div>
				</div>
			
				<!-- Total Invoices -->
				<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Invoices</p>
							<p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ clinicStats.totalInvoices }}</p>
						</div>
						<div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl">
							<CurrencyDollarIcon class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
						</div>
					</div>
				</div>
			</div>

			<!-- Order Status Breakdown -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
							<span class="w-1 h-5 bg-blue-500 rounded-full mr-2"></span>
							Order Status Breakdown
						</h3>
					</div>
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
						<div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 flex items-center justify-center">
							<canvas ref="orderStatusChartRef" style="max-width:100%;max-height:200px;"></canvas>
						</div>
						<div class="space-y-2">
							<div v-for="status in orderStatusBreakdown" :key="status.name" 
								class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${status.color}`"></div>
									<span class="text-sm font-medium text-gray-700 dark:text-gray-300 capitalize">{{ status.name }}</span>
								</div>
								<div class="flex items-center space-x-3">
									<span class="text-sm font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
									<span class="text-xs text-gray-500 dark:text-gray-400 w-10 text-right">{{ status.percentage }}%</span>
								</div>
							</div>
							<div v-if="orderStatusBreakdown.length === 0" class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
								No order data available
							</div>
						</div>
					</div>
				</div>
			<!-- IVR & Invoice Summary -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
				<!-- IVR Status -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
							<PhoneIcon class="w-4 h-4 text-green-600 dark:text-green-400" />
						</div>
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">IVR Eligibility Status</h3>
					</div>
					<div v-if="ivrStatusBreakdown.length === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No IVR data available
					</div>
					<template v-else>
						<div class="flex flex-col sm:flex-row sm:items-center gap-4">
							<!-- Chart - smaller on mobile -->
							<div class="flex-shrink-0 flex justify-center sm:justify-start">
								<canvas ref="ivrChartRef" style="max-width:140px;max-height:140px;"></canvas>
							</div>
							<!-- Legend items - full width on mobile -->
							<div class="flex-1 space-y-2">
								<div v-for="status in ivrStatusBreakdown" :key="status.name" 
									class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
									<div class="flex items-center space-x-2">
										<div :class="`w-3 h-3 rounded-full ${status.color}`"></div>
										<span class="text-sm text-gray-700 dark:text-gray-300 capitalize">{{ status.name }}</span>
									</div>
									<span class="text-sm font-semibold text-gray-900 dark:text-white">{{ status.count }}</span>
								</div>
							</div>
						</div>
					</template>
				</div>

				<!-- Invoice Summary -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
							<CurrencyDollarIcon class="w-4 h-4 text-blue-600 dark:text-blue-400" />
						</div>
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Invoice Summary</h3>
					</div>
					<div v-if="invoiceSummary.total === 0" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No invoice data available
					</div>
					<template v-else>
						<div class="grid grid-cols-2 gap-4 mb-4">
							<div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
								<p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Total Invoices</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white">{{ invoiceSummary.total }}</p>
							</div>
							<div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
								<p class="text-xs text-green-600 dark:text-green-400 mb-1">Total Amount</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white">${{ invoiceSummary.totalAmount.toLocaleString() }}</p>
							</div>
						</div>
						<div class="space-y-2">
							<div v-for="status in invoiceStatusBreakdown" :key="status.name" 
								class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${status.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300">{{ status.displayName }}</span>
								</div>
								<div class="flex items-center space-x-2">
									<span class="text-xs font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
									<span class="text-xs text-gray-500 dark:text-gray-400">${{ status.amount.toLocaleString() }}</span>
								</div>
							</div>
						</div>
					</template>
				</div>
			</div>

			<!-- Recent Activity Table -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
				<div class="flex items-center justify-between mb-4">
					<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
						<span class="w-1 h-5 bg-purple-500 rounded-full mr-2"></span>
						Recent Activity
					</h3>
				</div>
				<div class="overflow-x-auto">
					<table class="w-full text-sm">
						<thead>
							<tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-100 dark:border-gray-700">
								<th class="pb-3 font-medium">Date</th>
								<th class="pb-3 font-medium">Type</th>
								<th class="pb-3 font-medium">Description</th>
								<th class="pb-3 font-medium">Status</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="activity in recentActivity" :key="activity.id" class="border-b border-gray-50 dark:border-gray-700/50 last:border-0">
								<td class="py-3 text-gray-900 dark:text-white">{{ activity.date }}</td>
								<td class="py-3">
									<span :class="[
										'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
										activity.type === 'order' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
										activity.type === 'inventory' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' :
										'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
									]">
										{{ activity.type }}
									</span>
								</td>
								<td class="py-3 text-gray-700 dark:text-gray-300">{{ activity.description }}</td>
								<td class="py-3">
									<span :class="[
										'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium',
										activity.statusClass
									]">
										{{ activity.status }}
									</span>
								</td>
							</tr>
							<tr v-if="recentActivity.length === 0">
								<td colspan="4" class="py-8 text-center text-gray-500 dark:text-gray-400">No recent activity</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import {
	ChartBarIcon,
	ArrowDownTrayIcon,
	CubeIcon,
	ArrowTrendingUpIcon,
	CurrencyDollarIcon,
	ClipboardDocumentListIcon,
	PhoneIcon
} from '@heroicons/vue/24/outline'
import { Chart, registerables } from 'chart.js'
import { orderService, brandService, invoiceService, ivrService, patientService, api } from '@/services/api'
import { Package, Building2 } from 'lucide-vue-next'

Chart.register(...registerables)

// Current user with clinic data
const currentUser = ref<any>(null)
const isLoadingUser = ref(true)

// My Clinic - the clinic the logged-in user belongs to (from woundmed_users.clinic_id)
const myClinic = computed(() => {
	return currentUser.value?.clinic ?? null
})

// Check if user has clinic_id assigned
const hasClinicAssigned = computed(() => {
	return !!(currentUser.value?.clinic_id)
})

// State
const brands = ref<any[]>([])
const dateRange = ref('last_30_days')
const startDate = ref('')
const endDate = ref('')
const brandFilter = ref('all')
const isLoadingData = ref(false)
const isExporting = ref(false)
const reportGenerated = ref(false)
const showExportDropdown = ref(false)
const exportDropdownRef = ref<HTMLElement | null>(null)

// Data
const orders = ref<any[]>([])
const patients = ref<any[]>([])
const invoices = ref<any[]>([])
const ivrRequests = ref<any[]>([])

// Chart refs
const orderStatusChartRef = ref<HTMLCanvasElement | null>(null)
const inventoryStatusChartRef = ref<HTMLCanvasElement | null>(null)
const brandChartRef = ref<HTMLCanvasElement | null>(null)
const ivrChartRef = ref<HTMLCanvasElement | null>(null)

let orderStatusChart: Chart | null = null
let inventoryStatusChart: Chart | null = null
let brandChart: Chart | null = null
let ivrChart: Chart | null = null

// Options
const dateRangeOptions = [
	{ value: 'last_7_days', label: 'Last 7 Days' },
	{ value: 'last_30_days', label: 'Last 30 Days' },
	{ value: 'last_year', label: 'Last Year' },
	{ value: 'custom', label: 'Custom Range' }
]

// Computed
const dateRangeLabel = computed(() => {
	const option = dateRangeOptions.find(o => o.value === dateRange.value)
	if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		return `${startDate.value} to ${endDate.value}`
	}
	return option?.label || 'Last 30 Days'
})

const currentTimestamp = computed(() => {
	const now = new Date()
	return now.toLocaleString('en-US', { 
		year: 'numeric', 
		month: 'long', 
		day: 'numeric', 
		hour: '2-digit', 
		minute: '2-digit',
		hour12: true 
	})
})

// Date filtering helper
const getDateRange = () => {
	const today = new Date()
	let start: Date | null = null
	let end: Date | null = null

	if (dateRange.value === 'last_7_days') {
		start = new Date(today)
		start.setDate(today.getDate() - 7)
		end = today
	} else if (dateRange.value === 'last_30_days') {
		start = new Date(today)
		start.setDate(today.getDate() - 30)
		end = today
	} else if (dateRange.value === 'last_year') {
		start = new Date(today)
		start.setFullYear(today.getFullYear() - 1)
		end = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		start = new Date(startDate.value)
		end = new Date(endDate.value)
	}

	return { start, end }
}

const isInDateRange = (dateStr: string) => {
	const { start, end } = getDateRange()
	if (!start || !end) return true
	
	const date = new Date(dateStr)
	return date >= start && date <= end
}

// Filtered data computed properties
const filteredOrders = computed(() => {
	if (!myClinic.value) return []
	return orders.value.filter(order => {
		if (String(order.clinic_id || order.clinic?.clinic_id) !== String(myClinic.value.clinic_id)) return false
		if (!isInDateRange(order.ordered_at || order.created_at)) return false
		if (brandFilter.value !== 'all') {
			const hasBrand = order.items?.some((item: any) => 
				String(item.brand_id || item.brandId) === String(brandFilter.value)
			)
			if (!hasBrand) return false
		}
		return true
	})
})

const filteredPatients = computed(() => {
	if (!myClinic.value) return []
	return patients.value.filter(patient => {
		if (String(patient.clinic_id || patient.clinicId) !== String(myClinic.value.clinic_id)) return false
		if (!isInDateRange(patient.created_at || patient.createdAt)) return false
		return true
	})
})

const filteredIVR = computed(() => {
	if (!myClinic.value) return []
	return ivrRequests.value.filter(req => {
		if (String(req.clinicId || req.clinic_id) !== String(myClinic.value.clinic_id)) return false
		if (!isInDateRange(req.dateSubmitted || req.date_submitted || req.createdAt || req.created_at)) return false
		return true
	})
})

const filteredInvoices = computed(() => {
	if (!myClinic.value) return []
	return invoices.value.filter(inv => {
		if (String(inv.clinicId || inv.clinic_id) !== String(myClinic.value.clinic_id)) return false
		if (!isInDateRange(inv.createdAt || inv.created_at)) return false
		return true
	})
})

// Stats computed
const clinicStats = computed(() => ({
	totalOrders: filteredOrders.value.length,
	totalPatients: filteredPatients.value.length,
	totalIVR: filteredIVR.value.length,
	totalInvoices: filteredInvoices.value.length
}))

// Order status breakdown
const orderStatusBreakdown = computed(() => {
	const statuses: Record<string, { count: number; color: string }> = {
		submitted: { count: 0, color: 'bg-blue-500' },
		acknowledged: { count: 0, color: 'bg-purple-500' },
		shipped: { count: 0, color: 'bg-yellow-500' },
		delivered: { count: 0, color: 'bg-green-500' },
		cancelled: { count: 0, color: 'bg-red-500' }
	}

	filteredOrders.value.forEach((order: any) => {
		const status = order.order_status || 'submitted'
		if (statuses[status]) {
			statuses[status].count++
		}
	})

	const total = filteredOrders.value.length || 1
	return Object.entries(statuses).map(([name, data]) => ({
		name,
		count: data.count,
		percentage: ((data.count / total) * 100).toFixed(1),
		color: data.color
	}))
})

// IVR status breakdown
const ivrStatusBreakdown = computed(() => {
	const statusCounts: Record<string, number> = {}
	const colors: Record<string, string> = {
		'eligible': 'bg-green-500',
		'not eligible': 'bg-red-500',
		'pending': 'bg-yellow-500',
		'partial': 'bg-blue-500'
	}

	filteredIVR.value.forEach((req: any) => {
		const status = (req.eligibilityStatus || req.eligibility_status || 'pending').toLowerCase()
		statusCounts[status] = (statusCounts[status] || 0) + 1
	})

	return Object.entries(statusCounts)
		.map(([name, count]) => ({
			name,
			count,
			color: colors[name] || 'bg-gray-500'
		}))
		.sort((a, b) => b.count - a.count)
})

// Invoice summary
const invoiceSummary = computed(() => {
	const total = filteredInvoices.value.length
	const totalAmount = filteredInvoices.value.reduce((sum, inv) => sum + (inv.amount || inv.invoiceAmount || 0), 0)
	return { total, totalAmount }
})

const invoiceStatusBreakdown = computed(() => {
	const statusMap: Record<string, { displayName: string; color: string }> = {
		'pending': { displayName: 'Pending', color: 'bg-yellow-500' },
		'partial': { displayName: 'Partial', color: 'bg-blue-500' },
		'paid': { displayName: 'Paid', color: 'bg-green-500' },
		'overdue': { displayName: 'Overdue', color: 'bg-red-500' }
	}

	const statusCounts: Record<string, { count: number; amount: number; color: string; displayName: string }> = {}
	Object.entries(statusMap).forEach(([key, val]) => {
		statusCounts[key] = { count: 0, amount: 0, color: val.color, displayName: val.displayName }
	})

	filteredInvoices.value.forEach((inv: any) => {
		const status = (inv.status || inv.paymentStatus || 'pending').toLowerCase()
		if (statusCounts[status]) {
			statusCounts[status].count++
			statusCounts[status].amount += (inv.amount || inv.invoiceAmount || 0)
		}
	})

	return Object.entries(statusCounts).map(([name, data]) => ({
		name,
		displayName: data.displayName,
		count: data.count,
		amount: data.amount,
		color: data.color
	}))
})

// Recent activity
const recentActivity = computed(() => {
	const activities: any[] = []

	// Add orders
	filteredOrders.value.slice(0, 5).forEach(order => {
		activities.push({
			id: `order-${order.order_id}`,
			date: new Date(order.ordered_at || order.created_at).toLocaleDateString(),
			type: 'order',
			description: `Order #${order.order_code || order.order_id}`,
			status: order.order_status || 'submitted',
			statusClass: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
		})
	})

	// Add patients
	filteredPatients.value.slice(0, 5).forEach(patient => {
		activities.push({
			id: `patient-${patient.patient_id || patient.id}`,
			date: new Date(patient.created_at || patient.createdAt).toLocaleDateString(),
			type: 'patient',
			description: `Patient: ${patient.patient_name || patient.name || 'Unknown'}`,
			status: 'registered',
			statusClass: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
		})
	})

	// Add IVR
	filteredIVR.value.slice(0, 5).forEach(req => {
		activities.push({
			id: `ivr-${req.ivr_id || req.id}`,
			date: new Date(req.created_at || req.createdAt).toLocaleDateString(),
			type: 'ivr',
			description: `IVR Request`,
			status: req.eligibility_status || 'pending',
			statusClass: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
		})
	})

	return activities.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()).slice(0, 10)
})

// Methods
const clearFilters = () => {
	dateRange.value = 'last_30_days'
	startDate.value = ''
	endDate.value = ''
	brandFilter.value = 'all'
	reportGenerated.value = false
}

const handleGenerateReport = async () => {
	if (!myClinic.value) return

	isLoadingData.value = true
	reportGenerated.value = false

	try {
		const [ordersRes, patientsRes, invoicesRes, ivrRes] = await Promise.all([
			orderService.getAllOrders({ per_page: 10000 }),
			patientService.getAllPatients(),
			invoiceService.getAllInvoices({ per_page: 10000 }),
			ivrService.getAllIVRRequests({ per_page: 10000 })
		])

		// Parse orders
		const orderRows = Array.isArray(ordersRes.data?.order_data) ? ordersRes.data.order_data : []
		orders.value = orderRows.map((o: any) => {
			let rawItems = o.items
			if (!Array.isArray(rawItems) && typeof rawItems === 'string') {
				try { rawItems = JSON.parse(rawItems) } catch { rawItems = [] }
			}
			return {
				order_id: o.order_id,
				order_code: o.order_code,
				ordered_at: o.ordered_at || o.created_at,
				order_status: ['submitted','acknowledged','shipped','delivered','cancelled'][Number(o.order_status ?? 0)] || 'submitted',
				clinic_id: o.clinic?.clinic_id || o.clinic_id,
				items: Array.isArray(rawItems) ? rawItems : []
			}
		})

		// Parse patients
		const patientRows = Array.isArray(patientsRes.data?.patients) ? patientsRes.data.patients : 
							 Array.isArray(patientsRes.data?.data) ? patientsRes.data.data : 
							 Array.isArray(patientsRes.data) ? patientsRes.data : []
		patients.value = patientRows

		// Parse invoices
		const invoiceRows = Array.isArray(invoicesRes.data?.data) ? invoicesRes.data.data : 
						   Array.isArray(invoicesRes.data?.invoices) ? invoicesRes.data.invoices : 
						   Array.isArray(invoicesRes.data) ? invoicesRes.data : []
		invoices.value = invoiceRows

		// Parse IVR
		const ivrRows = Array.isArray(ivrRes.data?.ivrRequests) ? ivrRes.data.ivrRequests : 
					   Array.isArray(ivrRes.data?.data) ? ivrRes.data.data : 
					   Array.isArray(ivrRes.data) ? ivrRes.data : []
		ivrRequests.value = ivrRows

		reportGenerated.value = true
	} catch (error) {
		console.error('Error fetching report data:', error)
	} finally {
		isLoadingData.value = false
	}
}

const toggleExportDropdown = () => {
	showExportDropdown.value = !showExportDropdown.value
}

const exportAsPdf = async () => {
	if (!reportGenerated.value) return
	
	showExportDropdown.value = false
	isExporting.value = true

	try {
		const filters: any = {
			report_type: 'clinic',
			date_range: dateRange.value,
		}
		// Only include clinic_id if it exists
		if (myClinic.value?.clinic_id) {
			filters.clinic_id = String(myClinic.value.clinic_id)
		}
		// Only include brand_id if it's not 'all'
		if (brandFilter.value && brandFilter.value !== 'all') {
			filters.brand_id = String(brandFilter.value)
		}
		if (startDate.value) filters.start_date = startDate.value
		if (endDate.value) filters.end_date = endDate.value
		
		console.log('Export PDF filters:', filters)
	
		const response = await api.post('/reports/export/pdf', filters, {
			responseType: 'blob',
		})

		const blob = new Blob([response.data], { type: 'application/pdf' })
		const link = document.createElement('a')
		link.href = URL.createObjectURL(blob)
				
		const clinicName = myClinic.value?.clinic_name?.replace(/\s+/g, '_') || 'clinic'
		const timestamp = new Date().toISOString().split('T')[0]
		link.download = `clinic_report_${clinicName}_${timestamp}.pdf`
		
		link.click()
		URL.revokeObjectURL(link.href)
	} catch (error) {
		console.error('Error exporting PDF:', error)
		alert('Failed to export PDF. Please try again.')
	} finally {
		isExporting.value = false
	}
}

const exportAsExcel = async () => {
	if (!reportGenerated.value) return

	showExportDropdown.value = false
	isExporting.value = true

	try {
		const filters: any = {
			report_type: 'clinic',
			date_range: dateRange.value,
		}
		// Only include clinic_id if it exists
		if (myClinic.value?.clinic_id) {
			filters.clinic_id = String(myClinic.value.clinic_id)
		}
		// Only include brand_id if it's not 'all'
		if (brandFilter.value && brandFilter.value !== 'all') {
			filters.brand_id = String(brandFilter.value)
		}
		if (startDate.value) filters.start_date = startDate.value
		if (endDate.value) filters.end_date = endDate.value
		
		console.log('Export Excel filters:', filters)
	
		const response = await api.post('/reports/export/excel', filters, {
			responseType: 'blob',
		})

		const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
		const link = document.createElement('a')
		link.href = URL.createObjectURL(blob)
				
		const clinicName = myClinic.value?.clinic_name?.replace(/\s+/g, '_') || 'clinic'
		const timestamp = new Date().toISOString().split('T')[0]
		link.download = `clinic_report_${clinicName}_${timestamp}.xlsx`
		
		link.click()
		URL.revokeObjectURL(link.href)
	} catch (error) {
		console.error('Error exporting Excel:', error)
		alert('Failed to export Excel. Please try again.')
	} finally {
		isExporting.value = false
	}
}

// Chart rendering
const renderCharts = () => {
	// Order status chart
	if (orderStatusChartRef.value) {
		if (orderStatusChart) orderStatusChart.destroy()
		
		const data = orderStatusBreakdown.value.filter(s => s.count > 0)
		if (data.length > 0) {
			orderStatusChart = new Chart(orderStatusChartRef.value, {
				type: 'doughnut',
				data: {
					labels: data.map(s => s.name),
					datasets: [{
						data: data.map(s => s.count),
						backgroundColor: data.map(s => s.color.replace('bg-', '').replace('-500', '')),
						borderWidth: 0
					}]
				},
				options: {
					responsive: true,
					plugins: { legend: { display: false } },
					cutout: '60%'
				}
			})
		}
	}
	
	// IVR chart
	if (ivrChartRef.value) {
		if (ivrChart) ivrChart.destroy()
		
		const data = ivrStatusBreakdown.value
		if (data.length > 0) {
			ivrChart = new Chart(ivrChartRef.value, {
				type: 'doughnut',
				data: {
					labels: data.map(s => s.name),
					datasets: [{
						data: data.map(s => s.count),
						backgroundColor: data.map(s => s.color.replace('bg-', '').replace('-500', '')),
						borderWidth: 0
					}]
				},
				options: {
					responsive: true,
					plugins: { legend: { display: false } },
					cutout: '60%'
				}
			})
		}
	}
}

// Watch for report generation to re-render charts
watch(reportGenerated, (newVal) => {
	if (newVal) {
		setTimeout(renderCharts, 100)
	}
})

// Load user with clinic data from woundmed_users table
async function loadUser() {
	isLoadingUser.value = true
	try {
		const { data } = await api.get('/auth/me-with-clinic')
		currentUser.value = data.user_data
		console.log('User loaded with clinic:', currentUser.value)
	} catch (error) {
		console.error('Error loading user:', error)
		currentUser.value = null
	} finally {
		isLoadingUser.value = false
	}
}

// Fetch brands on mount
onMounted(async () => {
	// Load user with clinic data first
	await loadUser()

	try {
		const brandsRes = await brandService.getAllBrands({ per_page: 1000 })

		const brandRows = Array.isArray(brandsRes.data?.brandData) ? brandsRes.data.brandData : 
						  Array.isArray(brandsRes.data?.data) ? brandsRes.data.data : []
		brands.value = brandRows.map((b: any) => ({
			id: String(b.id || b.brand_id || b.brandId),
			name: b.brand_name || b.brandName || b.name || 'Unknown Brand'
		}))
	} catch (error) {
		console.error('Error fetching filter options:', error)
	}

	// Click outside handler for export dropdown
	document.addEventListener('click', (e) => {
		if (exportDropdownRef.value && !exportDropdownRef.value.contains(e.target as Node)) {
			showExportDropdown.value = false
		}
	})
})
</script>

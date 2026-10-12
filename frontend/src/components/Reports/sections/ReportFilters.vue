<template>
	<div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
		<div class="flex items-center justify-between mb-4">
			<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
				<svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
				</svg>
				Report Filters
			</h3>
			<button 
				@click="$emit('clear')"
				class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium flex items-center transition-colors"
			>
				<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
				</svg>
				Clear Filters
			</button>
		</div>
		
		<div class="flex flex-wrap items-end gap-3">
			<!-- Date Range Filter -->
			<div class="flex-1 min-w-[180px]">
				<label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
					<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
					</svg>
					Date Range
				</label>
				<select
					:value="dateRange"
					@change="$emit('update:dateRange', ($event.target as HTMLSelectElement).value)"
					class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
				>
					<option v-for="option in dateRangeOptions" :key="option.value" :value="option.value">
						{{ option.label }}
					</option>
				</select>
			</div>

			<!-- Custom Date Range -->
			<template v-if="dateRange === 'custom'">
				<div class="flex-1 min-w-[140px]">
					<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Start Date</label>
					<input
						:value="startDate"
						@input="$emit('update:startDate', ($event.target as HTMLInputElement).value)"
						type="date"
						class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					/>
				</div>
				<div class="flex-1 min-w-[140px]">
					<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">End Date</label>
					<input
						:value="endDate"
						@input="$emit('update:endDate', ($event.target as HTMLInputElement).value)"
						type="date"
						class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					/>
				</div>
			</template>

			<!-- Clinic Filter -->
			<div class="flex-1 min-w-[180px]">
				<label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
					<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
					</svg>
					Clinic
				</label>
				<select
					:value="clinicFilter"
					@change="$emit('update:clinicFilter', ($event.target as HTMLSelectElement).value)"
					class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
				>
					<option value="all">All Clinics</option>
					<option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
						{{ clinic.name }}
					</option>
				</select>
			</div>

			<!-- Brand Filter - Only show for Orders, Inventory, Usage reports -->
			<div v-if="['orders', 'inventory', 'usage'].includes(reportType)" class="flex-1 min-w-[180px]">
				<label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
					<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
					</svg>
					Brand
				</label>
				<select
					:value="brandFilter"
					@change="$emit('update:brandFilter', ($event.target as HTMLSelectElement).value)"
					class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
				>
					<option value="all">All Brands</option>
					<option v-for="brand in brands" :key="brand.id" :value="brand.id">
						{{ brand.name }}
					</option>
				</select>
			</div>

			<!-- Manufacturer Filter - Only show for IVR report -->
			<div v-if="reportType === 'ivr'" class="flex-1 min-w-[180px]">
				<label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
					<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
					</svg>
					Manufacturer
				</label>
				<select
					:value="manufacturerFilter"
					@change="$emit('update:manufacturerFilter', ($event.target as HTMLSelectElement).value)"
					class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
				>
					<option value="all">All Manufacturers</option>
					<option v-for="manufacturer in manufacturers" :key="manufacturer.id" :value="manufacturer.id">
						{{ manufacturer.name }}
					</option>
				</select>
			</div>

			<!-- Anonymize PHI Toggle -->
			<div class="flex-1 min-w-[180px]">
				<label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
					<svg class="w-3 h-3 mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
					</svg>
					HIPAA Compliance
				</label>
				<div class="flex items-center h-[38px] px-3 border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg">
					<label class="flex items-center cursor-pointer space-x-2 select-none text-xs font-medium text-gray-700 dark:text-gray-300 w-full">
						<input
							type="checkbox"
							:checked="anonymizePhi"
							@change="$emit('update:anonymizePhi', ($event.target as HTMLInputElement).checked)"
							class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700"
						/>
						<span>Anonymize Patient PHI</span>
					</label>
				</div>
			</div>
		</div>
		
		<p v-if="dateRange === 'custom' && dateRangeError" class="mt-3 text-xs text-red-600 dark:text-red-400 flex items-center">
			<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
			</svg>
			{{ dateRangeError }}
		</p>
		
		<!-- Action Buttons Bar -->
		<div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
			<div class="contents sm:flex sm:items-center sm:gap-3">
				<button
					@click="$emit('generate')"
					:disabled="isLoadingData || (dateRange === 'custom' && dateRangeError !== '')"
					class="flex items-center justify-center space-x-2 px-5 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 text-white text-sm font-medium rounded-lg hover:from-red-700 hover:to-rose-700 transition-all duration-200 shadow-sm hover:shadow-md group disabled:opacity-50 disabled:cursor-not-allowed"
				>
					<ChartBarIcon v-if="!isLoadingData" class="w-4 h-4 text-white group-hover:scale-110 transition-transform" />
					<svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<span>{{ isLoadingData ? 'Generating...' : 'Generate Report' }}</span>
				</button>
				
				<!-- Export Dropdown -->
				<div v-if="selectedReportType" class="relative" ref="exportDropdownRef">
					<button
						@click="$emit('toggleExport')"
						:disabled="isExporting || !reportGenerated"
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
					
					<!-- Export Options Dropdown -->
					<div
						v-if="showExportDropdown"
						class="absolute left-0 right-0 sm:right-auto sm:w-44 mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
					>
						<div class="py-1">
							<button
								@click="$emit('exportPdf')"
								class="w-full px-4 py-2.5 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-2 transition-colors"
							>
								<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
								</svg>
								<span>Export as PDF</span>
							</button>
							<button
								@click="$emit('exportExcel')"
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
			</div>
			
			<!-- Report Info -->
			<div v-if="reportGenerated" class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center sm:justify-start whitespace-nowrap">
				<svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<span>Report generated</span>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ChartBarIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

interface DateRangeOption {
	value: string
	label: string
}

defineProps<{
	dateRange: string
	dateRangeOptions: DateRangeOption[]
	startDate: string
	endDate: string
	dateRangeError: string
	clinicFilter: string
	clinics: any[]
	brandFilter: string
	brands: any[]
	manufacturerFilter: string
	manufacturers: any[]
	anonymizePhi: boolean
	reportType: string
	isLoadingData: boolean
	isExporting: boolean
	reportGenerated: boolean
	showExportDropdown: boolean
	selectedReportType: any
}>()

defineEmits<{
	(e: 'clear'): void
	(e: 'generate'): void
	(e: 'toggleExport'): void
	(e: 'exportPdf'): void
	(e: 'exportExcel'): void
	(e: 'update:dateRange', value: string): void
	(e: 'update:startDate', value: string): void
	(e: 'update:endDate', value: string): void
	(e: 'update:clinicFilter', value: string): void
	(e: 'update:brandFilter', value: string): void
	(e: 'update:manufacturerFilter', value: string): void
	(e: 'update:anonymizePhi', value: boolean): void
}>()
</script>

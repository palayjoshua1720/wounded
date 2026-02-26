<template>
	<div class="space-y-6">
		<!-- Report Type Selection Card -->
		<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
			<div class="flex items-center justify-between mb-6">
				<div>
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Select Report Type</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Choose the type of report you want to generate</p>
				</div>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
				<div
					v-for="type in reportTypes"
					:key="type.id"
					@click="reportType = type.id"
					:class="[
						'p-4 border-2 rounded-xl cursor-pointer transition-all duration-300 group relative overflow-hidden',
						reportType === type.id
							? 'border-blue-500 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/30 dark:to-indigo-900/20 shadow-md'
							: 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-700 hover:shadow-sm'
					]"
				>
					<!-- Selected Indicator -->
					<div v-if="reportType === type.id" class="absolute top-3 right-3">
						<div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
							<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
							</svg>
						</div>
					</div>
					
					<div class="flex items-start space-x-3">
						<div :class="[
							'p-2.5 rounded-xl transition-all duration-300 shadow-sm',
							reportType === type.id 
								? 'bg-gradient-to-br from-blue-500 to-indigo-600 shadow-blue-200 dark:shadow-blue-900/50' 
								: 'bg-gray-100 dark:bg-gray-700 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/30'
						]">
							<component 
								:is="type.icon" 
								:class="[
									'w-5 h-5 transition-all duration-300',
									reportType === type.id ? 'text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-blue-500'
								]" 
							/>
						</div>
						<div class="flex-1 pr-6">
							<h4 :class="[
								'font-semibold text-sm mb-0.5',
								reportType === type.id ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white'
							]">
								{{ type.name }}
							</h4>
							<p :class="[
								'text-xs leading-relaxed',
								reportType === type.id ? 'text-blue-700 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400'
							]">
								{{ type.description }}
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Filters Card -->
		<div class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
					<svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
					</svg>
					Report Filters
				</h3>
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
			
			<div class="flex flex-wrap items-end gap-3">
				<!-- Date Range Filter -->
				<div class="flex-1 min-w-[180px]">
					<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
						</svg>
						Date Range
					</label>
					<select
						v-model="dateRange"
						class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
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
							v-model="startDate"
							type="date"
							class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
						/>
					</div>
					<div class="flex-1 min-w-[140px]">
						<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">End Date</label>
						<input
							v-model="endDate"
							type="date"
							class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
						/>
					</div>
				</template>

				<!-- Clinic Filter -->
				<div class="flex-1 min-w-[180px]">
					<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
						</svg>
						Clinic
					</label>
					<select
						v-model="clinicFilter"
						class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Clinics</option>
						<option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
							{{ clinic.name }}
						</option>
					</select>
				</div>

				<!-- Brand Filter - Only show for Orders, Inventory, Usage reports -->
				<div v-if="['orders', 'inventory', 'usage'].includes(reportType)" class="flex-1 min-w-[180px]">
					<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
						</svg>
						Brand
					</label>
					<select
						v-model="brandFilter"
						class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Brands</option>
						<option v-for="brand in brands" :key="brand.id" :value="brand.id">
							{{ brand.name }}
						</option>
					</select>
				</div>

				<!-- Manufacturer Filter - Only show for IVR report -->
				<div v-if="reportType === 'ivr'" class="flex-1 min-w-[180px]">
					<label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
						</svg>
						Manufacturer
					</label>
					<select
						v-model="manufacturerFilter"
						class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Manufacturers</option>
						<option v-for="manufacturer in manufacturers" :key="manufacturer.id" :value="manufacturer.id">
							{{ manufacturer.name }}
						</option>
					</select>
				</div>
			</div>
			
			<p v-if="dateRange === 'custom' && dateRangeError" class="mt-3 text-xs text-red-600 dark:text-red-400 flex items-center">
				<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				{{ dateRangeError }}
			</p>
			
			<!-- Action Buttons Bar -->
			<div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
				<div class="flex items-center gap-3">
					<button
						@click="handleGenerateReport"
						:disabled="isLoadingData || (dateRange === 'custom' && dateRangeError !== '')"
						class="flex items-center justify-center space-x-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md group disabled:opacity-50 disabled:cursor-not-allowed"
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
							@click="toggleExportDropdown"
							:disabled="isExporting || !reportGenerated"
							class="flex items-center space-x-2 px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
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
							class="absolute left-0 mt-2 w-44 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
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
				</div>
				
				<!-- Report Info -->
				<div v-if="reportGenerated" class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
					<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
					</svg>
					<span>Report generated</span>
				</div>
			</div>
		</div>

		<!-- Report Preview -->
		<div v-if="selectedReportType && reportGenerated" class="report-preview-container space-y-6">
			<!-- Report Header - Simplified -->
			<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
				<!-- Title Row -->
				<div class="flex items-center justify-between mb-4">
					<div>
						<h2 class="text-xl font-bold text-gray-900 dark:text-white">
							{{ selectedReportType.name }}
						</h2>
						<p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
							{{ dateRange === 'custom' ? `${startDate} to ${endDate}` : dateRangeOptions.find(opt => opt.value === dateRange)?.label }}
						</p>
					</div>
					<div class="text-right">
						<span class="inline-flex items-center px-2.5 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-xs font-medium rounded-md">
							Generated {{ currentTimestamp }}
						</span>
					</div>
				</div>

				<!-- Filter Badges -->
				<div class="flex flex-wrap gap-2">
					<span class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
						</svg>
						{{ dateRange === 'custom' ? `${startDate} to ${endDate}` : dateRangeOptions.find(opt => opt.value === dateRange)?.label }}
					</span>
					<span class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
						</svg>
						{{ clinicFilter === 'all' ? 'All Clinics' : clinics.find(c => c.id === clinicFilter)?.name || 'Unknown' }}
					</span>
					<span v-if="['orders', 'inventory', 'usage'].includes(reportType)" class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
						</svg>
						{{ brandFilter === 'all' ? 'All Brands' : brands.find(b => b.id === brandFilter)?.name || 'Unknown' }}
					</span>
					<span v-else-if="reportType === 'ivr'" class="inline-flex items-center px-2.5 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-md">
						<svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
						</svg>
						{{ manufacturerFilter === 'all' ? 'All Manufacturers' : (manufacturers.find(m => String(m.id) === String(manufacturerFilter))?.name || 'Unknown') }}
					</span>
				</div>
			</div>
			<!-- Orders Report -->
			<div v-if="reportType === 'orders'" class="space-y-5">
			<!-- KPI Cards Row -->
			<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
				<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Orders</p>
							<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalOrders }}</p>
						</div>
						<div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
							<TrendingUp class="w-5 h-5 text-blue-600 dark:text-blue-400" />
						</div>
					</div>
				</div>
				<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Items</p>
							<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalOrderItems }}</p>
						</div>
						<div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
							<CubeIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
						</div>
					</div>
				</div>
				<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Value</p>
							<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ totalOrderValue.toLocaleString() }}</p>
						</div>
						<div class="p-2.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
							<CurrencyDollarIcon class="w-5 h-5 text-purple-600 dark:text-purple-400" />
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
					<!-- Pie Chart -->
					<div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 flex items-center justify-center">
						<canvas ref="statusChartRef" style="max-width:100%;max-height:250px;"></canvas>
					</div>
					<!-- Status List -->
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
					</div>
				</div>
			</div>

			<!-- Top Clinics/Products & Brand/Size Distribution -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
				<!-- Top Clinics OR Top Products -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
							<component :is="clinicFilter !== 'all' ? CubeIcon : Building2" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
						</div>
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ clinicFilter !== 'all' ? 'Top Products' : 'Top Clinics' }}</h3>
					</div>
					<div class="space-y-2">
						<template v-if="clinicFilter !== 'all'">
							<div v-for="(product, index) in topProducts.slice(0, 5)" :key="product.product_id" 
								class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2.5 flex-1 min-w-0">
									<div :class="[
										'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
										index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">{{ index + 1 }}</div>
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3 flex-shrink-0">
									<span class="text-xs text-gray-500 dark:text-gray-400">{{ product.count }}</span>
									<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
										<div class="bg-blue-500 h-1.5 rounded-full" :style="{ width: `${product.percentage}%` }"></div>
									</div>
								</div>
							</div>
						</template>
						<template v-else>
							<div v-for="(clinic, index) in topClinics.slice(0, 5)" :key="clinic.clinic_id" 
								class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2.5 flex-1 min-w-0">
									<div :class="[
										'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
										index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">{{ index + 1 }}</div>
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3 flex-shrink-0">
									<span class="text-xs text-gray-500 dark:text-gray-400">{{ clinic.count }}</span>
									<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
										<div class="bg-green-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
									</div>
								</div>
							</div>
						</template>
						<div v-if="(clinicFilter !== 'all' ? topProducts : topClinics).length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
							No data available
						</div>
					</div>
				</div>

				<!-- Brand OR Size Distribution -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
							<component :is="brandFilter !== 'all' ? ChartBarSquareIcon : Package" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
						</div>
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ brandFilter !== 'all' ? 'Size Distribution' : 'Brand Distribution' }}</h3>
					</div>
					<div class="flex items-center justify-center mb-3">
						<canvas v-if="brandFilter !== 'all'" ref="sizeChartRef" style="max-width:180px;max-height:180px;"></canvas>
						<canvas v-else ref="brandChartRef" style="max-width:180px;max-height:180px;"></canvas>
					</div>
					<div class="space-y-1.5 max-h-32 overflow-y-auto">
						<template v-if="brandFilter !== 'all'">
							<div v-for="size in sizeDistribution" :key="size.size_id" 
								class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${size.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300">{{ size.name }}</span>
								</div>
								<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ size.count }}</span>
							</div>
						</template>
						<template v-else>
							<div v-for="brand in brandDistribution" :key="brand.brand_id" 
								class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${brand.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300">{{ brand.name }}</span>
								</div>
								<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ brand.count }}</span>
							</div>
						</template>
						<div v-if="(brandFilter !== 'all' ? sizeDistribution : brandDistribution).length === 0" class="text-center py-4 text-xs text-gray-500 dark:text-gray-400">
							No data available
						</div>
					</div>
				</div>
			</div>
			</div>

			<!-- Inventory Report -->
			<div v-else-if="reportType === 'inventory'" class="space-y-5">
				<!-- KPI Cards Row -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Items</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalInventory }}</p>
							</div>
							<div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
								<CubeIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Used</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ usedInventoryCount }}</p>
							</div>
							<div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
								<CheckCircleIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">In Use</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ inUseInventoryCount }}</p>
							</div>
							<div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
								<ArrowPathIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
							</div>
						</div>
					</div>
				</div>

				<!-- Inventory Status Breakdown -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
							<span class="w-1 h-5 bg-green-500 rounded-full mr-2"></span>
							Inventory Status Breakdown
						</h3>
					</div>
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
						<!-- Pie Chart -->
						<div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 flex items-center justify-center">
							<canvas ref="inventoryStatusChartRef" style="max-width:100%;max-height:250px;"></canvas>
						</div>
						<!-- Status List -->
						<div class="space-y-2">
							<div v-for="status in inventoryStatusBreakdown" :key="status.name" 
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
						</div>
					</div>
				</div>

				<!-- Top Clinics and Brand Distribution -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
					<!-- Top Clinics -->
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
								<Building2 class="w-4 h-4 text-blue-600 dark:text-blue-400" />
							</div>
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Clinics</h3>
						</div>
						<div class="space-y-2">
							<div v-for="(clinic, index) in topInventoryClinics.slice(0, 5)" :key="clinic.clinic_id" 
								class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2.5 flex-1 min-w-0">
									<div :class="[
										'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
										index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">{{ index + 1 }}</div>
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3 flex-shrink-0">
									<span class="text-xs text-gray-500 dark:text-gray-400">{{ clinic.count }}</span>
									<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
										<div class="bg-blue-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
									</div>
								</div>
							</div>
							<div v-if="topInventoryClinics.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
								No clinic data available
							</div>
						</div>
					</div>

					<!-- Brand Distribution -->
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
								<Package class="w-4 h-4 text-purple-600 dark:text-purple-400" />
							</div>
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Brand Distribution</h3>
						</div>
						<div class="flex items-center justify-center mb-3">
							<canvas ref="inventoryBrandChartRef" style="max-width:180px;max-height:180px;"></canvas>
						</div>
						<div class="space-y-1.5 max-h-32 overflow-y-auto">
							<div v-for="brand in inventoryBrandDistribution" :key="brand.brand_id" 
								class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${brand.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300">{{ brand.name }}</span>
								</div>
								<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ brand.count }}</span>
							</div>
							<div v-if="inventoryBrandDistribution.length === 0" class="text-center py-4 text-xs text-gray-500 dark:text-gray-400">
								No brand data available
							</div>
						</div>
					</div>
				</div>

				<!-- E. Utilization Rate Card -->
				<div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800">
					<div class="flex items-center justify-between mb-4">
						<div>
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Utilization Rate</h3>
							<p class="text-sm text-gray-600 dark:text-gray-400">Efficiency of inventory usage</p>
						</div>
						<div class="text-right">
							<p class="text-4xl font-bold text-indigo-600 dark:text-indigo-400">{{ utilizationRate }}%</p>
							<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Used / Delivered</p>
						</div>
					</div>
					<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
						<div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-4 rounded-full transition-all duration-500" 
							:style="{ width: `${utilizationRate}%` }"></div>
					</div>
				</div>
			</div>

			<!-- Usage Report -->
			<div v-else-if="reportType === 'usage'" class="space-y-5">
				<!-- KPI Cards Row -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Grafts Used</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ filteredUsageLogs.length }}</p>
							</div>
							<div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
								<ArrowTrendingUpIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Top Wound Part</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ usageByWoundPart[0]?.name || 'N/A' }}</p>
							</div>
							<div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
								<TrendingUp class="w-5 h-5 text-orange-600 dark:text-orange-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Top Brand</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ usageBrandDistribution[0]?.name || 'N/A' }}</p>
							</div>
							<div class="p-2.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
								<Package class="w-5 h-5 text-purple-600 dark:text-purple-400" />
							</div>
						</div>
					</div>
				</div>

				<!-- Usage Analytics Grid -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
					<!-- Usage by Wound Part -->
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
								<TrendingUp class="w-4 h-4 text-orange-600 dark:text-orange-400" />
							</div>
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Usage by Wound Part</h3>
						</div>
						<div class="flex items-center justify-center mb-3">
							<canvas ref="usageWoundPartChartRef" style="max-width:180px;max-height:180px;"></canvas>
						</div>
						<div class="space-y-1.5 max-h-32 overflow-y-auto">
							<div v-for="part in usageByWoundPart" :key="part.name" 
								class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${part.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300">{{ part.name }}</span>
								</div>
								<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ part.count }}</span>
							</div>
							<div v-if="usageByWoundPart.length === 0" class="text-center py-4 text-xs text-gray-500 dark:text-gray-400">
								No usage data available
							</div>
						</div>
					</div>

					<!-- Top Clinics by Usage -->
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
								<Building2 class="w-4 h-4 text-blue-600 dark:text-blue-400" />
							</div>
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Clinics by Usage</h3>
						</div>
						<div class="space-y-2">
							<div v-for="(clinic, index) in topUsageClinics.slice(0, 5)" :key="clinic.clinic_id" 
								class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2.5 flex-1 min-w-0">
									<div :class="[
										'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
										index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">{{ index + 1 }}</div>
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3 flex-shrink-0">
									<span class="text-xs text-gray-500 dark:text-gray-400">{{ clinic.count }}</span>
									<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
										<div class="bg-blue-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
									</div>
								</div>
							</div>
							<div v-if="topUsageClinics.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
								No clinic data available
							</div>
						</div>
					</div>
				</div>

				<!-- Brand Usage Distribution -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
							<Package class="w-4 h-4 text-purple-600 dark:text-purple-400" />
						</div>
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Brand Usage Distribution</h3>
					</div>
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
						<div class="flex items-center justify-center">
							<canvas ref="usageBrandChartRef" style="max-width:180px;max-height:180px;"></canvas>
						</div>
						<div class="space-y-1.5 max-h-40 overflow-y-auto">
							<div v-for="brand in usageBrandDistribution" :key="brand.brand_id" 
								class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${brand.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300">{{ brand.name }}</span>
								</div>
								<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ brand.count }}</span>
							</div>
							<div v-if="usageBrandDistribution.length === 0" class="text-center py-4 text-xs text-gray-500 dark:text-gray-400">
								No brand data available
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Invoice Report -->
			<div v-else-if="reportType === 'invoices'" class="space-y-5">
				<!-- KPI Cards Row -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Invoices</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ filteredInvoices.length }}</p>
							</div>
							<div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
								<DocumentTextIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Amount</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ totalInvoiceAmount.toLocaleString() }}</p>
							</div>
							<div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
								<CurrencyDollarIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Pending</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ pendingInvoiceAmount.toLocaleString() }}</p>
							</div>
							<div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
								<ClockIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
							</div>
						</div>
					</div>
				</div>

				<!-- Invoice Status Breakdown -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center justify-between mb-4">
						<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
							<span class="w-1 h-5 bg-blue-500 rounded-full mr-2"></span>
							Invoice Status Breakdown
						</h3>
					</div>
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
						<!-- Pie Chart -->
						<div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 flex items-center justify-center">
							<canvas ref="invoiceStatusChartRef" style="max-width:100%;max-height:250px;"></canvas>
						</div>
						<!-- Status List -->
						<div class="space-y-2">
							<div v-for="status in invoiceStatusBreakdown" :key="status.name" 
								class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${status.color}`"></div>
									<span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ status.displayName }}</span>
								</div>
								<div class="flex items-center space-x-3">
									<span class="text-sm font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
									<span class="text-xs text-gray-500 dark:text-gray-400">${{ status.amount.toLocaleString() }}</span>
								</div>
							</div>
							<div v-if="invoiceStatusBreakdown.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
								No invoice data available
							</div>
						</div>
					</div>
				</div>

				<!-- Top Clinics by Invoice Amount -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
							<Building2 class="w-4 h-4 text-purple-600 dark:text-purple-400" />
						</div>
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Clinics by Invoice Amount</h3>
					</div>
					<div class="space-y-2">
						<div v-for="(clinic, index) in topInvoiceClinics.slice(0, 5)" :key="clinic.clinic_id" 
							class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
							<div class="flex items-center space-x-2.5 flex-1 min-w-0">
								<div :class="[
									'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
									index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
								]">{{ index + 1 }}</div>
								<div class="flex-1 min-w-0">
									<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3 flex-shrink-0">
								<span class="text-xs font-semibold text-purple-600 dark:text-purple-400">${{ clinic.amount.toLocaleString() }}</span>
								<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
									<div class="bg-purple-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
								</div>
							</div>
						</div>
						<div v-if="topInvoiceClinics.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
							No clinic data available
						</div>
					</div>
				</div>
			</div>
			
			<!-- IVR Report -->
			<div v-else-if="reportType === 'ivr'" class="space-y-5">
				<!-- KPI Cards Row -->
				<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Requests</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ filteredIVRRequests.length }}</p>
							</div>
							<div class="p-2.5 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg">
								<PhoneIcon class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Approval Rate</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ overallApprovalRate }}%</p>
							</div>
							<div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
								<CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
							</div>
						</div>
					</div>
					<div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Top Status</p>
								<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1 capitalize">{{ ivrEligibilityBreakdown[0]?.name || 'N/A' }}</p>
							</div>
							<div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
								<ChartBarIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
						</div>
					</div>
				</div>

				<!-- Eligibility Status & Manufacturers/Insights -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
					<!-- Eligibility Status Breakdown -->
					<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
								<PhoneIcon class="w-4 h-4 text-green-600 dark:text-green-400" />
							</div>
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Eligibility Status</h3>
						</div>
						<div class="flex items-center justify-center mb-3">
							<canvas ref="ivrEligibilityChartRef" style="max-width:180px;max-height:180px;"></canvas>
						</div>
						<div class="space-y-1.5 max-h-32 overflow-y-auto">
							<div v-for="status in ivrEligibilityBreakdown" :key="status.name" 
								class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${status.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300 capitalize">{{ status.name }}</span>
								</div>
								<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ status.count }}</span>
							</div>
							<div v-if="ivrEligibilityBreakdown.length === 0" class="text-center py-4 text-xs text-gray-500 dark:text-gray-400">
								No IVR data available
							</div>
						</div>
					</div>

					<!-- Top Manufacturers OR Manufacturer Insights -->
					<div v-if="manufacturerFilter === 'all'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
								<Package class="w-4 h-4 text-orange-600 dark:text-orange-400" />
							</div>
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Manufacturers</h3>
						</div>
						<div class="space-y-2">
							<div v-for="(mfr, index) in topIVRManufacturers.slice(0, 5)" :key="mfr.manufacturer_id" 
								class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2.5 flex-1 min-w-0">
									<div :class="[
										'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
										index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">{{ index + 1 }}</div>
									<div class="flex-1 min-w-0">
										<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ mfr.name }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-3 flex-shrink-0">
									<span class="text-xs text-gray-500 dark:text-gray-400">{{ mfr.count }}</span>
									<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
										<div class="bg-orange-500 h-1.5 rounded-full" :style="{ width: `${mfr.percentage}%` }"></div>
									</div>
								</div>
							</div>
							<div v-if="topIVRManufacturers.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
								No manufacturer data available
							</div>
						</div>
					</div>

					<!-- Manufacturer Insights - Show when manufacturer is filtered -->
					<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
						<div class="flex items-center space-x-2 mb-4">
							<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
								<Package class="w-4 h-4 text-purple-600 dark:text-purple-400" />
							</div>
							<div class="flex-1 min-w-0">
								<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Manufacturer Insights</h3>
								<p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ manufacturers.find(m => String(m.id) === String(manufacturerFilter))?.name || 'Selected Manufacturer' }}</p>
							</div>
						</div>
						<div class="grid grid-cols-2 gap-3 mb-3">
							<div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100 dark:border-purple-800">
								<p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Total Requests</p>
								<p class="text-xl font-bold text-gray-900 dark:text-white">{{ filteredIVRRequests.length }}</p>
							</div>
							<div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-100 dark:border-green-800">
								<p class="text-xs text-green-600 dark:text-green-400 mb-1">Approval Rate</p>
								<p class="text-xl font-bold text-gray-900 dark:text-white">{{ manufacturerApprovalRate }}%</p>
							</div>
						</div>
						<div class="space-y-1.5 max-h-28 overflow-y-auto">
							<div v-for="status in ivrEligibilityBreakdown" :key="status.name" 
								class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
								<div class="flex items-center space-x-2">
									<div :class="`w-2.5 h-2.5 rounded-full ${status.color}`"></div>
									<span class="text-xs text-gray-700 dark:text-gray-300 capitalize">{{ status.name }}</span>
								</div>
								<div class="flex items-center space-x-2">
									<span class="text-xs font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
									<span class="text-xs text-gray-500 dark:text-gray-400">({{ ((status.count / filteredIVRRequests.length) * 100).toFixed(0) }}%)</span>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Clinic Insights or Top Clinics -->
				<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
					<div class="flex items-center space-x-2 mb-4">
						<div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
							<Building2 class="w-4 h-4 text-blue-600 dark:text-blue-400" />
						</div>
						<div class="flex-1 min-w-0">
							<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ clinicFilter !== 'all' ? 'Clinic Insights' : 'Top Clinics by IVR Requests' }}</h3>
							<p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ clinicFilter !== 'all' ? clinics.find(c => String(c.id) === String(clinicFilter))?.name || 'Selected Clinic' : 'Highest verification volume' }}</p>
						</div>
					</div>

					<!-- Clinic Insights - Show when clinic is filtered -->
					<div v-if="clinicFilter !== 'all'" class="grid grid-cols-2 gap-3">
						<div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-100 dark:border-blue-800">
							<p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Total Requests</p>
							<p class="text-xl font-bold text-gray-900 dark:text-white">{{ filteredIVRRequests.length }}</p>
						</div>
						<div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-100 dark:border-green-800">
							<p class="text-xs text-green-600 dark:text-green-400 mb-1">Approval Rate</p>
							<p class="text-xl font-bold text-gray-900 dark:text-white">{{ clinicApprovalRate }}%</p>
						</div>
					</div>

					<!-- Top Clinics - Show when no specific clinic is selected -->
					<div v-else class="space-y-2">
						<div v-for="(clinic, index) in topIVRClinics.slice(0, 5)" :key="clinic.clinic_id" 
							class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
							<div class="flex items-center space-x-2.5 flex-1 min-w-0">
								<div :class="[
									'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0',
									index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : index === 2 ? 'bg-orange-600' : 'bg-gray-500'
								]">{{ index + 1 }}</div>
								<div class="flex-1 min-w-0">
									<p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
								</div>
							</div>
							<div class="flex items-center space-x-3 flex-shrink-0">
								<span class="text-xs text-gray-500 dark:text-gray-400">{{ clinic.count }}</span>
								<div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5">
									<div class="bg-blue-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
								</div>
							</div>
						</div>
						<div v-if="topIVRClinics.length === 0" class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
							No clinic data available
						</div>
					</div>
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
	UsersIcon,
	ChartBarSquareIcon,
	ClipboardDocumentListIcon,
	DocumentTextIcon,
	PhoneIcon,
	CheckCircleIcon,
	ArrowPathIcon,
	ClockIcon
} from '@heroicons/vue/24/outline'
import { Chart, registerables, type ChartType } from 'chart.js'
import type { Ref } from 'vue'
import { orderService, userService, brandService, inventoryService, invoiceService, ivrService, returnsService, api } from '@/services/api'
import { TrendingUp, Package, Building2 } from 'lucide-vue-next'
import html2canvas from 'html2canvas'
import * as XLSX from 'xlsx'

Chart.register(...registerables)

// --- REAL DATA FROM API ---
const orders = ref<any[]>([])
const clinics = ref<any[]>([])
const brands = ref<any[]>([])
const manufacturers = ref<any[]>([])
const inventory = ref<any[]>([])
const invoices = ref<any[]>([])
const ivrRequests = ref<any[]>([])
const usageLogs = ref<any[]>([])
const isLoadingData = ref(false)
const isExporting = ref(false) // Track if export is in progress
const reportGenerated = ref(false) // Track if report has been generated
const showExportDropdown = ref(false)
const exportDropdownRef = ref<HTMLElement | null>(null)

// Fetch real data from API
async function fetchReportData() {
	isLoadingData.value = true
	try {
		// Fetch all data including manufacturers in parallel
		const [ordersRes, clinicsRes, brandsRes, manufacturersRes, inventoryRes, invoicesRes, ivrRes] = await Promise.all([
			orderService.getAllOrders({ per_page: 10000 }),
			userService.getClinics(),
			brandService.getAllBrands({ per_page: 1000 }),
			userService.getManufacturers(),
			inventoryService.getAllInventory(),
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
				clinic: o.clinic,
				clinic_id: o.clinic?.clinic_id || o.clinic_id,
				items: Array.isArray(rawItems) ? rawItems : [],
				brand: o.brand
			}
		})

		// Parse clinics - API returns user_data
		const clinicRows = Array.isArray(clinicsRes.data?.user_data) ? clinicsRes.data.user_data : 
						  Array.isArray(clinicsRes.data?.clinic_data) ? clinicsRes.data.clinic_data : 
						  Array.isArray(clinicsRes.data) ? clinicsRes.data : []
		clinics.value = clinicRows.map((c: any) => ({
			id: String(c.id || c.clinic_id || c.clinicId),
			name: c.clinic_name || c.clinicName || c.name || c.clinicPubId || 'Unknown Clinic'
		}))

		// Parse brands - API returns brandData
		const brandRows = Array.isArray(brandsRes.data?.brandData) ? brandsRes.data.brandData : 
						  Array.isArray(brandsRes.data?.data) ? brandsRes.data.data : []
		brands.value = brandRows.map((b: any) => ({
			id: String(b.id || b.brand_id || b.brandId),
			name: b.brand_name || b.brandName || b.name || 'Unknown Brand'
		}))

		// Parse manufacturers - Check manufacturerData field first (like fetchFilterOptions does)
		console.log('🔍 Raw manufacturers response:', manufacturersRes.data)
		const manufacturerRows = Array.isArray(manufacturersRes.data?.manufacturerData) ?
								 manufacturersRes.data.manufacturerData :
								 Array.isArray(manufacturersRes.data?.data) ?
								 manufacturersRes.data.data :
								 Array.isArray(manufacturersRes.data?.manufacturers) ?
								 manufacturersRes.data.manufacturers :
								 Array.isArray(manufacturersRes.data) ? manufacturersRes.data : []
		console.log('🔍 Parsed manufacturer rows:', manufacturerRows)
		manufacturers.value = manufacturerRows.map((m: any) => ({
			id: String(m.id || m.manufacturer_id || m.manufacturerId),
			name: m.manufacturer_name || m.manufacturerName || m.name || 'Unknown Manufacturer'
		}))
		console.log('🔍 Final manufacturers array:', manufacturers.value)

		// Parse inventory/usage logs
		const inventoryRows = Array.isArray(inventoryRes.data?.data) ? inventoryRes.data.data : 
							  Array.isArray(inventoryRes.data) ? inventoryRes.data : []
		inventory.value = inventoryRows
		// Usage logs are items with status 2 (used) or 3 (partially used)
		usageLogs.value = inventoryRows.filter((item: any) => {
			const status = Number(item.log_status)
			return status === 2 || status === 3
		})

		// Parse invoices
		const invoiceRows = Array.isArray(invoicesRes.data?.data) ? invoicesRes.data.data : 
						   Array.isArray(invoicesRes.data?.invoices) ? invoicesRes.data.invoices : 
						   Array.isArray(invoicesRes.data) ? invoicesRes.data : []
		invoices.value = invoiceRows

		// Parse IVR requests
		const ivrRows = Array.isArray(ivrRes.data?.ivrRequests) ? ivrRes.data.ivrRequests : 
					   Array.isArray(ivrRes.data?.data) ? ivrRes.data.data : 
					   Array.isArray(ivrRes.data) ? ivrRes.data : []
		ivrRequests.value = ivrRows

		console.log('Loaded orders:', orders.value.length)
		console.log('Loaded clinics:', clinics.value.length)
		console.log('Loaded brands:', brands.value.length)
		console.log('Loaded manufacturers:', manufacturers.value.length)
		console.log('Loaded inventory:', inventory.value.length)
		console.log('Loaded usage logs:', usageLogs.value.length)
		console.log('Loaded invoices:', invoices.value.length)
		console.log('Loaded IVR requests:', ivrRequests.value.length)
	} catch (error) {
		console.error('Error fetching report data:', error)
	} finally {
		isLoadingData.value = false
	}
}

// Fetch only filter options (clinics, brands, manufacturers) on mount
async function fetchFilterOptions() {
	try {
		console.log('🔄 Fetching filter options...')
		const [clinicsRes, brandsRes, manufacturersRes] = await Promise.all([
			userService.getClinics(),
			brandService.getAllBrands({ per_page: 1000 }),
			userService.getManufacturers()
		])

		console.log('✅ Clinics response:', clinicsRes.data)
		console.log('✅ Brands response:', brandsRes.data)
		console.log('✅ Manufacturers response:', manufacturersRes.data)

		// Parse clinics - API returns user_data
		const clinicRows = Array.isArray(clinicsRes.data?.user_data) ? clinicsRes.data.user_data : 
						  Array.isArray(clinicsRes.data?.clinic_data) ? clinicsRes.data.clinic_data : 
						  Array.isArray(clinicsRes.data) ? clinicsRes.data : []
		clinics.value = clinicRows.map((c: any) => ({
			id: String(c.id || c.clinic_id || c.clinicId),
			name: c.clinic_name || c.clinicName || c.name || c.clinicPubId || 'Unknown Clinic'
		}))

		// Parse brands - API returns brandData
		const brandRows = Array.isArray(brandsRes.data?.brandData) ? brandsRes.data.brandData : 
						  Array.isArray(brandsRes.data?.data) ? brandsRes.data.data : []
		brands.value = brandRows.map((b: any) => ({
			id: String(b.id || b.brand_id || b.brandId),
			name: b.brand_name || b.brandName || b.name || 'Unknown Brand'
		}))

		// Parse manufacturers
		const manufacturerRows = Array.isArray(manufacturersRes.data?.manufacturerData) ?
								 manufacturersRes.data.manufacturerData :
								 Array.isArray(manufacturersRes.data?.data) ?
								 manufacturersRes.data.data :
								 Array.isArray(manufacturersRes.data?.manufacturers) ?
								 manufacturersRes.data.manufacturers :
								 Array.isArray(manufacturersRes.data) ? manufacturersRes.data : []
		manufacturers.value = manufacturerRows.map((m: any) => ({
			id: String(m.id || m.manufacturer_id || m.manufacturerId),
			name: m.manufacturer_name || m.manufacturerName || m.name || 'Unknown Manufacturer'
		}))

		console.log('📊 Parsed clinics:', clinics.value.length)
		console.log('📊 Parsed brands:', brands.value.length)
		console.log('📊 Parsed manufacturers:', manufacturers.value)
	} catch (error) {
		console.error('❌ Error fetching filter options:', error)
	}
}

interface ReportType {
	id: string
	name: string
	icon: any
	description: string
}

interface DateRangeOption {
	value: string
	label: string
}

const emit = defineEmits<{
	generateReport: [filters: any]
}>()

const reportType = ref('orders')
const dateRange = ref('last_30_days') // Default to Last 30 Days for better performance
const startDate = ref('')
const endDate = ref('')
const dateRangeError = ref('') // Validation error message for custom date range
const clinicFilter = ref('all')
const brandFilter = ref('all')
const manufacturerFilter = ref('all') // For IVR report
const graphType = ref('area') // 'area', 'line', 'bar'

const reportTypes: ReportType[] = [
	{ 
		id: 'orders', 
		name: 'Orders Report', 
		icon: ClipboardDocumentListIcon, 
		description: 'Orders by clinic, clinician, date range' 
	},
	{ 
		id: 'inventory', 
		name: 'Inventory Report', 
		icon: CubeIcon, 
		description: 'Inventory counts per clinic, brand, size' 
	},
	{ 
		id: 'usage', 
		name: 'Usage Report', 
		icon: ArrowTrendingUpIcon, 
		description: 'Grafts ordered vs used vs unused' 
	},
	{ 
		id: 'invoices', 
		name: 'Invoice Report', 
		icon: CurrencyDollarIcon, 
		description: 'Invoice status per serial' 
	},
	{ 
		id: 'ivr', 
		name: 'IVR Report', 
		icon: PhoneIcon, 
		description: 'Insurance verification status' 
	}
]

const dateRangeOptions: DateRangeOption[] = [
	{ value: 'last_7_days', label: 'Last 7 Days' },
	{ value: 'last_30_days', label: 'Last 30 Days' },
	{ value: 'last_year', label: 'Last Year' },
	{ value: 'custom', label: 'Custom Range' }
]

const selectedReportType = computed(() => {
	return reportTypes.find(type => type.id === reportType.value)
})

// Current timestamp for report generation
const currentTimestamp = computed(() => {
	const now = new Date()
	return now.toLocaleString('en-US', { 
		year: 'numeric', 
		month: 'long', 
		day: 'numeric', 
		hour: '2-digit', 
		minute: '2-digit',
		second: '2-digit',
		hour12: true 
	})
})

const clearFilters = () => {
	dateRange.value = 'last_30_days'
	startDate.value = ''
	endDate.value = ''
	dateRangeError.value = ''
	clinicFilter.value = 'all'
	brandFilter.value = 'all'
	manufacturerFilter.value = 'all'
}

const handleGenerateReport = async () => {
	// Validate custom date range before generating report
	if (dateRange.value === 'custom') {
		if (!startDate.value || !endDate.value) {
			dateRangeError.value = 'Please select both start and end dates.'
			return
		}
		
		if (dateRangeError.value) {
			// Don't generate report if there's a validation error
			return
		}
	}
	
	const filters = {
		reportType: reportType.value,
		dateRange: dateRange.value,
		startDate: dateRange.value === 'custom' ? startDate.value : undefined,
		endDate: dateRange.value === 'custom' ? endDate.value : undefined,
		clinicFilter: clinicFilter.value !== 'all' ? clinicFilter.value : undefined,
		brandFilter: brandFilter.value !== 'all' ? brandFilter.value : undefined
	}

	// Fetch report data when Generate Report is clicked
	await fetchReportData()
	reportGenerated.value = true

	emit('generateReport', filters)
}

// Toggle export dropdown
const toggleExportDropdown = () => {
	showExportDropdown.value = !showExportDropdown.value
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
	if (exportDropdownRef.value && !exportDropdownRef.value.contains(event.target as Node)) {
		showExportDropdown.value = false
	}
}

// Add click outside listener
onMounted(() => {
	document.addEventListener('click', handleClickOutside)
})

// Export as PDF via backend
const exportAsPdf = async () => {
	if (!reportGenerated.value) {
		alert('Please generate a report first before exporting.')
		return
	}
	
	showExportDropdown.value = false
	isExporting.value = true
	
	try {
		const filters: any = {
			report_type: reportType.value,
			date_range: dateRange.value,
			clinic_id: clinicFilter.value,
			brand_id: brandFilter.value,
			manufacturer_id: manufacturerFilter.value,
		}
		// Only include dates if they have values (for custom date range)
		if (startDate.value) filters.start_date = startDate.value
		if (endDate.value) filters.end_date = endDate.value

		const response = await api.post('/reports/export/pdf', filters, {
			responseType: 'blob',
		})

		const blob = new Blob([response.data], { type: 'application/pdf' })
		const link = document.createElement('a')
		link.href = URL.createObjectURL(blob)
		
		const reportName = selectedReportType.value?.name.replace(/\s+/g, '_').toLowerCase() || 'report'
		const dateLabel = dateRange.value.replace('_', '-')
		const timestamp = new Date().toISOString().split('T')[0]
		link.download = `${reportName}_${dateLabel}_${timestamp}.pdf`
		
		link.click()
		URL.revokeObjectURL(link.href)
		
		console.log('✅ PDF exported successfully')
	} catch (error) {
		console.error('❌ Error exporting PDF:', error)
		alert('Failed to export PDF. Please try again.')
	} finally {
		isExporting.value = false
	}
}

// Export as Excel
const exportAsExcel = async () => {
	if (!reportGenerated.value) {
		alert('Please generate a report first before exporting.')
		return
	}
	
	showExportDropdown.value = false
	isExporting.value = true
	
	try {
		const filters: any = {
			report_type: reportType.value,
			date_range: dateRange.value,
			clinic_id: clinicFilter.value,
			brand_id: brandFilter.value,
			manufacturer_id: manufacturerFilter.value,
		}
		// Only include dates if they have values (for custom date range)
		if (startDate.value) filters.start_date = startDate.value
		if (endDate.value) filters.end_date = endDate.value

		const response = await api.post('/reports/export/excel', filters, {
			responseType: 'blob',
		})

		const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
		const link = document.createElement('a')
		link.href = URL.createObjectURL(blob)
		
		const reportName = selectedReportType.value?.name.replace(/\s+/g, '_').toLowerCase() || 'report'
		const dateLabel = dateRange.value.replace('_', '-')
		const timestamp = new Date().toISOString().split('T')[0]
		link.download = `${reportName}_${dateLabel}_${timestamp}.xlsx`
		
		link.click()
		URL.revokeObjectURL(link.href)
		
		console.log('✅ Excel exported successfully')
	} catch (error) {
		console.error('❌ Error exporting Excel:', error)
		alert('Failed to export Excel. Please try again.')
	} finally {
		isExporting.value = false
	}
}

// Legacy export function (fallback to PDF)
const exportReport = exportAsPdf

const chartRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

function getDateLabels(range: string) {
	// Generate date labels for the selected range
	const today = new Date('2025-01-27') // fixed for mock
	
	let days = 7
	if (range === 'last_30_days') days = 30
	else if (range === 'last_year') days = 365
	if (range === 'custom' && startDate.value && endDate.value) {
		const start = new Date(startDate.value)
		const end = new Date(endDate.value)
		const labels = []
		for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
			labels.push(new Date(d).toISOString().split('T')[0])
		}
		return labels
	}
	const labels = []
	for (let i = days - 1; i >= 0; i--) {
		const d = new Date(today)
		d.setDate(today.getDate() - i)
		labels.push(d.toISOString().split('T')[0])
	}
	return labels
}

function getReportData(type: string, range: string) {
	const labels = getDateLabels(range)
	const data = Array(labels.length).fill(0)
	// Helper filter functions
	const clinic = clinicFilter.value !== 'all' ? clinicFilter.value : null
	const brand = brandFilter.value !== 'all' ? brandFilter.value : null
	if (type === 'orders') {
		orders.value.forEach((o: any) => {
			const d = (o.ordered_at || '').split('T')[0]
			if (clinic && String(o.clinic_id) !== String(clinic)) return
			if (brand && o.items && !o.items.some((item: any) => String(item.brand_id) === String(brand))) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Orders' }
	} else if (type === 'usage') {
		usageLogs.value.forEach((u: any) => {
			const d = u.usageDate || u.usage_date
			if (clinic && String(u.clinicId || u.clinic_id) !== String(clinic)) return
			if (brand && String(u.brandId || u.brand_id) !== String(brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Grafts Used' }
	} else if (type === 'inventory') {
		inventory.value.forEach((i: any) => {
			const d = i.expiryDate || i.expiry_date
			if (clinic && String(i.clinicId || i.clinic_id) !== String(clinic)) return
			if (brand && String(i.brandId || i.brand_id) !== String(brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Inventory Expiry' }
	} else if (type === 'invoices') {
		invoices.value.forEach((inv: any) => {
			const d = (inv.createdAt || inv.created_at || '').split('T')[0]
			if (clinic && String(inv.clinicId || inv.clinic_id) !== String(clinic)) return
			if (brand && String(inv.brandId || inv.brand_id) !== String(brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx] += (inv.amount || 0)
		})
		return { labels, data, label: 'Invoice Amount' }
	} else if (type === 'ivr') {
		ivrRequests.value.forEach((req: any) => {
			const d = req.dateSubmitted || req.date_submitted
			if (clinic && String(req.clinicId || req.clinic_id) !== String(clinic)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'IVR Requests' }
	}
	return { labels: [], data: [], label: '' }
}

function getChartType(): ChartType {
	if (graphType.value === 'area') return 'line'
	return graphType.value as ChartType
}

function getChartOptions() {
	return {
		responsive: true,
		plugins: {
			legend: { display: true }, // Show legend
			title: { display: false }
		},
		scales: {
			x: { grid: { display: false } },
			y: { beginAtZero: true }
		}
	}
}

function renderChart() {
	if (!chartRef.value) return
	if (chartInstance) chartInstance.destroy()
	const { labels, data, label } = getReportData(reportType.value, dateRange.value)
	chartInstance = new Chart(chartRef.value, {
		type: getChartType(),
		data: {
			labels,
			datasets: [{
			label,
			data,
			borderColor: '#7c3aed',
			backgroundColor: 'rgba(124, 58, 237, 0.2)',
			tension: 0.4,
			pointRadius: 3,
			pointBackgroundColor: '#7c3aed',
			}]
		},
		options: getChartOptions()
	})
}

watch([reportType, dateRange, graphType], () => {
	setTimeout(renderChart, 100)
})

onMounted(() => {
	// Only fetch filter options on mount, not the full report data
	fetchFilterOptions()
})

// Add a computed property for table data
const tableData = computed(() => {
	const { labels, data, label } = getReportData(reportType.value, dateRange.value)
	if (reportType.value === 'top_sizes') {
		// Show size and count
		return labels.map((size, i) => ({
			metric: size,
			value: data[i],
			change: ''
		}))
	} else {
		// Show date and value (count or sum)
		return labels.map((date, i) => ({
			metric: date,
			value: data[i],
			change: '' // Optionally, calculate change vs previous day
		}))
	}
})

// Orders Report Computed Properties
// Filtered orders based on clinic and brand filters
const filteredOrders = computed(() => {
	let filtered = orders.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((order: any) => {
			const orderClinicId = String(order.clinic_id || order.clinic?.clinic_id || '')
			return orderClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((order: any) => {
			if (order.items && Array.isArray(order.items)) {
				return order.items.some((item: any) => {
					const itemBrandId = String(item.brand_id || item.brandId || '')
					return itemBrandId === String(brandFilter.value)
				})
			}
			return false
		})
	}
	
	// Apply date range filter
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((order: any) => {
			const orderDate = new Date(order.ordered_at || order.created_at)
			return orderDate >= startDateFilter! && orderDate <= endDateFilter!
		})
	}
	
	return filtered
})

const totalOrders = computed(() => {
	return filteredOrders.value.length
})

const totalOrderItems = computed(() => {
	return filteredOrders.value.reduce((sum, order) => {
		const items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || [])
		return sum + items.reduce((itemSum: number, item: any) => itemSum + (item.quantity || 1), 0)
	}, 0)
})

const totalOrderValue = computed(() => {
	return filteredOrders.value.reduce((sum, order) => {
		// Calculate from items - orders use 'asp' (Average Selling Price) field
		const items = typeof order.items === 'string' ? JSON.parse(order.items) : (order.items || [])
		return sum + items.reduce((itemSum: number, item: any) => {
			const price = item.asp || item.price || item.unit_price || item.unitPrice || 0
			const quantity = item.quantity || 1
			return itemSum + (price * quantity)
		}, 0)
	}, 0)
})

const dateRangeLabel = computed(() => {
	const options: Record<string, string> = {
		last_7_days: 'Last 7 Days',
		last_30_days: 'Last 30 Days',
		last_year: 'Last Year',
		custom: 'Custom Range'
	}
	return options[dateRange.value] || 'Last 30 Days'
})

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

const topClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredOrders.value.forEach((order: any) => {
		const clinicId = order.clinic_id || order.clinic?.clinic_id
		const clinicName = order.clinic?.clinic_name || 'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

const brandDistribution = computed(() => {
	const brandCounts: Record<string, { name: string; count: number; brand_id: string }> = {}
	const colors = ['bg-purple-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-pink-500']
	
	filteredOrders.value.forEach((order: any) => {
		if (order.items && Array.isArray(order.items)) {
			order.items.forEach((item: any) => {
				const brandId = item.brand_id || item.brandId
				
				// Look up brand name from brands array
				let brandName = item.brand_name || order.brand?.brand_name
				if (!brandName && brandId) {
					const brandFromList = brands.value.find(b => String(b.id) === String(brandId))
					brandName = brandFromList?.name || 'Unknown Brand'
				}
				
				if (brandId) {
					if (!brandCounts[brandId]) {
						brandCounts[brandId] = {
							brand_id: String(brandId),
							name: brandName || 'Unknown Brand',
							count: 0
						}
					}
					brandCounts[brandId].count++
				}
			})
		}
	})
	
	return Object.values(brandCounts)
		.sort((a, b) => b.count - a.count)
		.map((brand, index) => ({
			...brand,
			color: colors[index % colors.length]
		}))
})

// Top Products (shown when clinic is filtered)
const topProducts = computed(() => {
	const productCounts: Record<string, { name: string; count: number; product_id: string }> = {}
	
	filteredOrders.value.forEach((order: any) => {
		if (order.items && Array.isArray(order.items)) {
			order.items.forEach((item: any) => {
				const productId = item.product_id || item.productId || item.id
				const productName = item.product_name || item.productName || item.name || 'Unknown Product'
				
				if (productId) {
					if (!productCounts[productId]) {
						productCounts[productId] = {
							product_id: String(productId),
							name: productName,
							count: 0
						}
					}
					productCounts[productId].count++
				}
			})
		}
	})
	
	const sortedProducts = Object.values(productCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedProducts[0]?.count || 1
	return sortedProducts.map(product => ({
		...product,
		percentage: ((product.count / maxCount) * 100).toFixed(0)
	}))
})

// Size Distribution (shown when brand is filtered)
const sizeDistribution = computed(() => {
	const sizeCounts: Record<string, { name: string; count: number; size_id: string }> = {}
	const colors = ['bg-orange-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-pink-500']
	
	filteredOrders.value.forEach((order: any) => {
		if (order.items && Array.isArray(order.items)) {
			order.items.forEach((item: any) => {
				const sizeId = item.graft_size_id || item.graftSizeId || item.size_id || item.sizeId
				const sizeName = item.graft_size_name || item.graftSizeName || item.size_name || item.sizeName || `Size ${sizeId}`
				
				if (sizeId) {
					if (!sizeCounts[sizeId]) {
						sizeCounts[sizeId] = {
							size_id: String(sizeId),
							name: sizeName,
							count: 0
						}
					}
					sizeCounts[sizeId].count++
				}
			})
		}
	})
	
	return Object.values(sizeCounts)
		.sort((a, b) => b.count - a.count)
		.map((size, index) => ({
			...size,
			color: colors[index % colors.length]
		}))
})

// Inventory Report Computed Properties
const totalInventory = computed(() => {
	return filteredInventory.value.length
})

const usedInventoryCount = computed(() => {
	// Count from inventory where log_status = 2 (used)
	// Status mapping: 0=expected, 1=delivered, 2=used, 3=partially_used, 4=reassigned, 5=unused, 6=expired
	return filteredInventory.value.filter((item: any) => {
		const status = Number(item.log_status ?? item.logStatus)
		return status === 2
	}).length
})

const inUseInventoryCount = computed(() => {
	// Count from inventory where log_status = 1 (delivered/in use)
	// Status mapping: 0=expected, 1=delivered, 2=used, 3=partially_used, 4=reassigned, 5=unused, 6=expired
	return filteredInventory.value.filter((item: any) => {
		const status = Number(item.log_status ?? item.logStatus)
		return status === 1
	}).length
})

const filteredInventory = computed(() => {
	let filtered = inventory.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			// API returns: clinicId, patientClinicId (camelCase)
			const itemClinicId = String(item.clinicId || item.clinic_id || 
										 item.patientClinicId || item.patient_clinic_id || '')
			return itemClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			// API returns: brandId (camelCase)
			const itemBrandId = String(item.brandId || item.brand_id || '')
			return itemBrandId === String(brandFilter.value)
		})
	}
	
	// Apply date range filter (based on various date fields)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((item: any) => {
			// PRIMARY: Use loggedAt (when item was added to inventory system)
			// FALLBACK: Use other date fields if loggedAt is missing
			const dateValue = item.loggedAt || item.logged_at || 
							  item.dateOfService || item.date_of_service || 
							  item.createdAt || item.created_at || 
							  item.deliveryDate || item.delivery_date || 
							  item.usageDate || item.usage_date ||
							  item.updatedAt || item.updated_at
			
			if (!dateValue) {
				return true // Include items without dates
			}
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const inventoryStatusBreakdown = computed(() => {
	// Map log_status numbers to status names
	// Status mapping: 0=expected, 1=delivered, 2=used, 3=partially_used, 4=reassigned, 5=unused, 6=expired
	const statusMap: Record<number, { name: string; color: string }> = {
		0: { name: 'expected', color: 'bg-gray-500' },
		1: { name: 'delivered', color: 'bg-blue-500' },
		2: { name: 'used', color: 'bg-green-500' },
		3: { name: 'partially used', color: 'bg-yellow-500' },
		4: { name: 'reassigned', color: 'bg-orange-500' },
		5: { name: 'unused', color: 'bg-red-500' },
		6: { name: 'expired', color: 'bg-purple-500' }
	}
	
	const statusCounts: Record<string, { count: number; color: string }> = {}
	
	// Initialize all statuses
	Object.values(statusMap).forEach(status => {
		statusCounts[status.name] = { count: 0, color: status.color }
	})
	
	filteredInventory.value.forEach((item: any) => {
		// The API returns both 'logStatus' (number 0-6) and 'status' (string)
		// We need to use 'logStatus' for correct mapping
		const logStatus = Number(item.logStatus ?? item.log_status ?? 0)
		const statusInfo = statusMap[logStatus] || statusMap[0]
		if (statusCounts[statusInfo.name]) {
			statusCounts[statusInfo.name].count++
		}
	})
	
	const total = filteredInventory.value.length || 1
	return Object.entries(statusCounts).map(([name, data]) => ({
		name,
		count: data.count,
		percentage: ((data.count / total) * 100).toFixed(1),
		color: data.color
	}))
})

const topInventoryClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredInventory.value.forEach((item: any) => {
		// API returns: clinicId, patientClinicId, clinicName, patientClinicName (camelCase)
		const clinicId = item.clinicId || item.clinic_id || 
						 item.patientClinicId || item.patient_clinic_id
		const clinicName = item.clinicName || item.clinic_name || 
						   item.patientClinicName || item.patient_clinic_name ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

const inventoryBrandDistribution = computed(() => {
	const brandCounts: Record<string, { name: string; count: number; brand_id: string }> = {}
	const colors = ['bg-purple-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-pink-500']
	
	filteredInventory.value.forEach((item: any) => {
		// API returns: brandId, brandName (camelCase)
		const brandId = item.brandId || item.brand_id
		let brandName = item.brandName || item.brand_name
		
		if (!brandName && brandId) {
			const brandFromList = brands.value.find(b => String(b.id) === String(brandId))
			brandName = brandFromList?.name || 'Unknown Brand'
		}
		
		if (brandId) {
			if (!brandCounts[brandId]) {
				brandCounts[brandId] = {
					brand_id: String(brandId),
					name: brandName || 'Unknown Brand',
					count: 0
				}
			}
			brandCounts[brandId].count++
		}
	})
	
	return Object.values(brandCounts)
		.sort((a, b) => b.count - a.count)
		.map((brand, index) => ({
			...brand,
			color: colors[index % colors.length]
		}))
})

const utilizationRate = computed(() => {
	// Delivered = status 1 and above (not expected=0)
	const delivered = filteredInventory.value.filter((item: any) => {
		const logStatus = Number(item.logStatus ?? item.log_status ?? 0)
		return logStatus >= 1
	}).length
	
	// Used = status 2 (used) or 3 (partially used)
	const used = filteredInventory.value.filter((item: any) => {
		const logStatus = Number(item.logStatus ?? item.log_status ?? 0)
		return logStatus === 2 || logStatus === 3
	}).length
	
	if (delivered === 0) return 0
	return ((used / delivered) * 100).toFixed(1)
})

// ========== USAGE REPORT COMPUTED PROPERTIES ==========
const filteredUsageLogs = computed(() => {
	let filtered = usageLogs.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			const itemClinicId = String(item.clinicId || item.clinic_id || 
										 item.patientClinicId || item.patient_clinic_id || '')
			return itemClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply brand filter
	if (brandFilter.value !== 'all') {
		filtered = filtered.filter((item: any) => {
			const itemBrandId = String(item.brandId || item.brand_id || '')
			return itemBrandId === String(brandFilter.value)
		})
	}
	
	// Apply date range filter (use loggedAt as primary)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((item: any) => {
			const dateValue = item.loggedAt || item.logged_at || 
							  item.dateOfService || item.date_of_service ||
							  item.createdAt || item.created_at
			if (!dateValue) return true
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const usageByWoundPart = computed(() => {
	const woundPartCounts: Record<string, number> = {}
	const colors = ['bg-orange-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-pink-500']
	
	filteredUsageLogs.value.forEach((item: any) => {
		const woundPart = item.woundPart || item.wound_part || 'Unknown'
		if (!woundPartCounts[woundPart]) {
			woundPartCounts[woundPart] = 0
		}
		woundPartCounts[woundPart]++
	})
	
	return Object.entries(woundPartCounts)
		.sort(([, a], [, b]) => b - a)
		.map(([name, count], index) => ({
			name,
			count,
			color: colors[index % colors.length]
		}))
})

const topUsageClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredUsageLogs.value.forEach((item: any) => {
		const clinicId = item.clinicId || item.clinic_id || item.patientClinicId || item.patient_clinic_id
		const clinicName = item.clinicName || item.clinic_name || 
						   item.patientClinicName || item.patient_clinic_name ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

const usageBrandDistribution = computed(() => {
	const brandCounts: Record<string, { name: string; count: number; brand_id: string }> = {}
	const colors = ['bg-purple-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-indigo-500', 'bg-pink-500']
	
	filteredUsageLogs.value.forEach((item: any) => {
		const brandId = item.brandId || item.brand_id
		let brandName = item.brandName || item.brand_name
		
		if (!brandName && brandId) {
			const brandFromList = brands.value.find(b => String(b.id) === String(brandId))
			brandName = brandFromList?.name || 'Unknown Brand'
		}
		
		if (brandId) {
			if (!brandCounts[brandId]) {
				brandCounts[brandId] = {
					brand_id: String(brandId),
					name: brandName || 'Unknown Brand',
					count: 0
				}
			}
			brandCounts[brandId].count++
		}
	})
	
	return Object.values(brandCounts)
		.sort((a, b) => b.count - a.count)
		.map((brand, index) => ({
			...brand,
			color: colors[index % colors.length]
		}))
})

// ========== INVOICE REPORT COMPUTED PROPERTIES ==========
const filteredInvoices = computed(() => {
	let filtered = invoices.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((invoice: any) => {
			const invoiceClinicId = String(invoice.clinic_id || invoice.clinicId || '')
			return invoiceClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply date range filter (use created_at or invoice_date)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((invoice: any) => {
			const dateValue = invoice.invoice_date || invoice.invoiceDate || 
							  invoice.created_at || invoice.createdAt
			if (!dateValue) {
				return true
			}
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const totalInvoiceAmount = computed(() => {
	return filteredInvoices.value.reduce((sum, invoice: any) => {
		return sum + (Number(invoice.amount) || 0)
	}, 0)
})

const pendingInvoiceAmount = computed(() => {
	return filteredInvoices.value
		.filter((invoice: any) => invoice.status === 'pending' || invoice.status === 'pending_review')
		.reduce((sum, invoice: any) => sum + (Number(invoice.amount) || 0), 0)
})

const invoiceStatusBreakdown = computed(() => {
	const statusCounts: Record<string, { count: number; amount: number; color: string; displayName: string }> = {
		pending: { count: 0, amount: 0, color: 'bg-yellow-500', displayName: 'Pending' },
		pending_review: { count: 0, amount: 0, color: 'bg-orange-500', displayName: 'Pending Review' },
		paid: { count: 0, amount: 0, color: 'bg-green-500', displayName: 'Paid' },
		overdue: { count: 0, amount: 0, color: 'bg-red-500', displayName: 'Overdue' },
		cancelled: { count: 0, amount: 0, color: 'bg-gray-500', displayName: 'Cancelled' }
	}
	
	filteredInvoices.value.forEach((invoice: any) => {
		const status = invoice.status || 'pending'
		if (statusCounts[status]) {
			statusCounts[status].count++
			statusCounts[status].amount += Number(invoice.amount) || 0
		}
	})
	
	return Object.entries(statusCounts)
		.map(([name, data]) => ({
			name,
			displayName: data.displayName,
			count: data.count,
			amount: data.amount,
			color: data.color
		}))
		.filter(status => status.count > 0) // Only show statuses with data
})

const topInvoiceClinics = computed(() => {
	const clinicAmounts: Record<string, { name: string; amount: number; clinic_id: string }> = {}
	
	filteredInvoices.value.forEach((invoice: any) => {
		const clinicId = invoice.clinic_id || invoice.clinicId
		const clinicName = invoice.clinic?.clinic_name || invoice.clinic?.clinicName ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicAmounts[clinicId]) {
				clinicAmounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					amount: 0
				}
			}
			clinicAmounts[clinicId].amount += Number(invoice.amount) || 0
		}
	})
	
	const sortedClinics = Object.values(clinicAmounts)
		.sort((a, b) => b.amount - a.amount)
		.slice(0, 5)
	
	const maxAmount = sortedClinics[0]?.amount || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.amount / maxAmount) * 100).toFixed(0)
	}))
})

// ========== IVR REPORT COMPUTED PROPERTIES ==========
const filteredIVRRequests = computed(() => {
	let filtered = ivrRequests.value
	
	// Apply clinic filter
	if (clinicFilter.value !== 'all') {
		filtered = filtered.filter((ivr: any) => {
			const ivrClinicId = String(ivr.clinic_id || ivr.clinicId || '')
			return ivrClinicId === String(clinicFilter.value)
		})
	}
	
	// Apply manufacturer filter
	if (manufacturerFilter.value !== 'all') {
		filtered = filtered.filter((ivr: any) => {
			const ivrManufacturerId = String(ivr.manufacturer_id || ivr.manufacturerId || '')
			return ivrManufacturerId === String(manufacturerFilter.value)
		})
	}
	
	// Apply date range filter (use submitted_at or created_at)
	const today = new Date()
	let startDateFilter: Date | null = null
	let endDateFilter: Date | null = null
	
	if (dateRange.value === 'last_7_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 7)
		endDateFilter = today
	} else if (dateRange.value === 'last_30_days') {
		startDateFilter = new Date(today)
		startDateFilter.setDate(today.getDate() - 30)
		endDateFilter = today
	} else if (dateRange.value === 'last_year') {
		startDateFilter = new Date(today)
		startDateFilter.setFullYear(today.getFullYear() - 1)
		endDateFilter = today
	} else if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		startDateFilter = new Date(startDate.value)
		endDateFilter = new Date(endDate.value)
	}
	
	if (startDateFilter && endDateFilter) {
		filtered = filtered.filter((ivr: any) => {
			const dateValue = ivr.submitted_at || ivr.submittedAt || 
							  ivr.created_at || ivr.createdAt
			if (!dateValue) {
				return true
			}
			
			const itemDate = new Date(dateValue)
			return itemDate >= startDateFilter! && itemDate <= endDateFilter!
		})
	}
	
	return filtered
})

const ivrEligibilityBreakdown = computed(() => {
	// eligibility_status: 0 = pending, 1 = approved, 2 = denied
	const statusCounts: Record<string, { count: number; color: string }> = {
		pending: { count: 0, color: 'bg-yellow-500' },
		approved: { count: 0, color: 'bg-green-500' },
		denied: { count: 0, color: 'bg-red-500' }
	}
	
	filteredIVRRequests.value.forEach((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		const statusName = eligibilityStatus === 1 ? 'approved' : eligibilityStatus === 2 ? 'denied' : 'pending'
		
		if (statusCounts[statusName]) {
			statusCounts[statusName].count++
		}
	})
	
	return Object.entries(statusCounts)
		.map(([name, data]) => ({
			name,
			count: data.count,
			color: data.color
		}))
		.filter(status => status.count > 0)
})

const topIVRManufacturers = computed(() => {
	const manufacturerCounts: Record<string, { name: string; count: number; manufacturer_id: string }> = {}
	
	filteredIVRRequests.value.forEach((ivr: any) => {
		const manufacturerId = ivr.manufacturer_id || ivr.manufacturerId
		const manufacturerName = ivr.manufacturer?.manufacturer_name || ivr.manufacturer?.manufacturerName ||
								 'Unknown Manufacturer'
		
		if (manufacturerId) {
			if (!manufacturerCounts[manufacturerId]) {
				manufacturerCounts[manufacturerId] = {
					manufacturer_id: String(manufacturerId),
					name: manufacturerName,
					count: 0
				}
			}
			manufacturerCounts[manufacturerId].count++
		}
	})
	
	const sortedManufacturers = Object.values(manufacturerCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedManufacturers[0]?.count || 1
	return sortedManufacturers.map(mfr => ({
		...mfr,
		percentage: ((mfr.count / maxCount) * 100).toFixed(0)
	}))
})

const topIVRClinics = computed(() => {
	const clinicCounts: Record<string, { name: string; count: number; clinic_id: string }> = {}
	
	filteredIVRRequests.value.forEach((ivr: any) => {
		const clinicId = ivr.clinic_id || ivr.clinicId
		const clinicName = ivr.clinic?.clinic_name || ivr.clinic?.clinicName ||
						   clinics.value.find(c => String(c.id) === String(clinicId))?.name || 
						   'Unknown Clinic'
		
		if (clinicId) {
			if (!clinicCounts[clinicId]) {
				clinicCounts[clinicId] = {
					clinic_id: String(clinicId),
					name: clinicName,
					count: 0
				}
			}
			clinicCounts[clinicId].count++
		}
	})
	
	const sortedClinics = Object.values(clinicCounts)
		.sort((a, b) => b.count - a.count)
		.slice(0, 5)
	
	const maxCount = sortedClinics[0]?.count || 1
	return sortedClinics.map(clinic => ({
		...clinic,
		percentage: ((clinic.count / maxCount) * 100).toFixed(0)
	}))
})

// Manufacturer Approval Rate - for filtered manufacturer
const manufacturerApprovalRate = computed(() => {
	const total = filteredIVRRequests.value.length
	if (total === 0) return 0
	
	const approved = filteredIVRRequests.value.filter((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		return eligibilityStatus === 1 // approved
	}).length
	
	return ((approved / total) * 100).toFixed(1)
})

// Overall Approval Rate - for all IVR requests
const overallApprovalRate = computed(() => {
	const total = filteredIVRRequests.value.length
	if (total === 0) return 0
	
	const approved = filteredIVRRequests.value.filter((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		return eligibilityStatus === 1 // approved
	}).length
	
	return ((approved / total) * 100).toFixed(1)
})

// Clinic Approval Rate - for filtered clinic
const clinicApprovalRate = computed(() => {
	const total = filteredIVRRequests.value.length
	if (total === 0) return 0
	
	const approved = filteredIVRRequests.value.filter((ivr: any) => {
		const eligibilityStatus = Number(ivr.eligibility_status ?? ivr.eligibilityStatus ?? 0)
		return eligibilityStatus === 1 // approved
	}).length
	
	return ((approved / total) * 100).toFixed(1)
})

// Chart refs for Orders Report
const statusChartRef = ref<HTMLCanvasElement | null>(null)
const brandChartRef = ref<HTMLCanvasElement | null>(null)
const sizeChartRef = ref<HTMLCanvasElement | null>(null)
const inventoryStatusChartRef = ref<HTMLCanvasElement | null>(null)
const inventoryBrandChartRef = ref<HTMLCanvasElement | null>(null)
// Chart refs for Usage Report
const usageWoundPartChartRef = ref<HTMLCanvasElement | null>(null)
const usageBrandChartRef = ref<HTMLCanvasElement | null>(null)
// Chart refs for Invoice Report
const invoiceStatusChartRef = ref<HTMLCanvasElement | null>(null)
// Chart refs for IVR Report
const ivrEligibilityChartRef = ref<HTMLCanvasElement | null>(null)

let statusChartInstance: Chart | null = null
let brandChartInstance: Chart | null = null
let sizeChartInstance: Chart | null = null
let inventoryStatusChartInstance: Chart | null = null
let inventoryBrandChartInstance: Chart | null = null
let usageWoundPartChartInstance: Chart | null = null
let usageBrandChartInstance: Chart | null = null
let invoiceStatusChartInstance: Chart | null = null
let ivrEligibilityChartInstance: Chart | null = null

// Render status breakdown pie chart
function renderStatusChart() {
	if (!statusChartRef.value) return
	if (statusChartInstance) statusChartInstance.destroy()
	
	const statusData = orderStatusBreakdown.value
	statusChartInstance = new Chart(statusChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1)),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: ['#3b82f6', '#a855f7', '#eab308', '#22c55e', '#ef4444'],
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							const total = (context.dataset.data as number[]).reduce((a, b) => a + b, 0)
							const percentage = ((value / total) * 100).toFixed(1)
							return `${label}: ${value} (${percentage}%)`
						}
					}
				}
			}
		}
	})
}

// Render brand distribution donut chart
function renderBrandChart() {
	if (!brandChartRef.value) return
	if (brandChartInstance) brandChartInstance.destroy()
	
	const brandData = brandDistribution.value
	const colors = ['#a855f7', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	brandChartInstance = new Chart(brandChartRef.value, {
		type: 'doughnut',
		data: {
			labels: brandData.map(b => b.name),
			datasets: [{
				data: brandData.map(b => b.count),
				backgroundColor: colors.slice(0, brandData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} orders`
						}
					}
				}
			}
		}
	})
}

// Render size distribution donut chart
function renderSizeChart() {
	if (!sizeChartRef.value) return
	if (sizeChartInstance) sizeChartInstance.destroy()
	
	const sizeData = sizeDistribution.value
	const colors = ['#f97316', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	sizeChartInstance = new Chart(sizeChartRef.value, {
		type: 'doughnut',
		data: {
			labels: sizeData.map(s => s.name),
			datasets: [{
				data: sizeData.map(s => s.count),
				backgroundColor: colors.slice(0, sizeData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} orders`
						}
					}
				}
			}
		}
	})
}

// Render inventory status breakdown pie chart
function renderInventoryStatusChart() {
	if (!inventoryStatusChartRef.value) return
	if (inventoryStatusChartInstance) inventoryStatusChartInstance.destroy()
	
	const statusData = inventoryStatusBreakdown.value
	const colors = ['#6b7280', '#3b82f6', '#22c55e', '#eab308', '#f97316', '#ef4444', '#a855f7']
	
	inventoryStatusChartInstance = new Chart(inventoryStatusChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1)),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: colors,
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							const total = (context.dataset.data as number[]).reduce((a, b) => a + b, 0)
							const percentage = ((value / total) * 100).toFixed(1)
							return `${label}: ${value} (${percentage}%)`
						}
					}
				}
			}
		}
	})
}

// Render inventory brand distribution donut chart
function renderInventoryBrandChart() {
	if (!inventoryBrandChartRef.value) return
	if (inventoryBrandChartInstance) inventoryBrandChartInstance.destroy()
	
	const brandData = inventoryBrandDistribution.value
	const colors = ['#a855f7', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	inventoryBrandChartInstance = new Chart(inventoryBrandChartRef.value, {
		type: 'doughnut',
		data: {
			labels: brandData.map(b => b.name),
			datasets: [{
				data: brandData.map(b => b.count),
				backgroundColor: colors.slice(0, brandData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} items`
						}
					}
				}
			}
		}
	})
}

// ========== USAGE REPORT CHART RENDERING ==========
function renderUsageWoundPartChart() {
	if (!usageWoundPartChartRef.value) return
	if (usageWoundPartChartInstance) usageWoundPartChartInstance.destroy()
	
	const data = usageByWoundPart.value
	const colors = ['#f97316', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	usageWoundPartChartInstance = new Chart(usageWoundPartChartRef.value, {
		type: 'doughnut',
		data: {
			labels: data.map(d => d.name),
			datasets: [{
				data: data.map(d => d.count),
				backgroundColor: colors.slice(0, data.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} uses`
						}
					}
				}
			}
		}
	})
}

function renderUsageBrandChart() {
	if (!usageBrandChartRef.value) return
	if (usageBrandChartInstance) usageBrandChartInstance.destroy()
	
	const brandData = usageBrandDistribution.value
	const colors = ['#a855f7', '#3b82f6', '#22c55e', '#eab308', '#ef4444', '#6366f1', '#ec4899']
	
	usageBrandChartInstance = new Chart(usageBrandChartRef.value, {
		type: 'doughnut',
		data: {
			labels: brandData.map(b => b.name),
			datasets: [{
				data: brandData.map(b => b.count),
				backgroundColor: colors.slice(0, brandData.length),
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							return `${label}: ${value} uses`
						}
					}
				}
			}
		}
	})
}

// ========== INVOICE REPORT CHART RENDERING ==========
function renderInvoiceStatusChart() {
	if (!invoiceStatusChartRef.value) return
	if (invoiceStatusChartInstance) invoiceStatusChartInstance.destroy()
	
	const statusData = invoiceStatusBreakdown.value
	const colors = ['#eab308', '#f97316', '#22c55e', '#ef4444', '#6b7280']
	
	invoiceStatusChartInstance = new Chart(invoiceStatusChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1).replace('_', ' ')),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: colors,
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const idx = context.dataIndex
							const status = statusData[idx]
							return `${context.label}: ${status.count} ($${status.amount.toLocaleString()})`
						}
					}
				}
			}
		}
	})
}

// ========== IVR REPORT CHART RENDERING ==========
function renderIVREligibilityChart() {
	if (!ivrEligibilityChartRef.value) return
	if (ivrEligibilityChartInstance) ivrEligibilityChartInstance.destroy()
	
	const statusData = ivrEligibilityBreakdown.value
	// Map Tailwind color classes to hex colors for Chart.js
	const colorMap: Record<string, string> = {
		'bg-yellow-500': '#eab308',
		'bg-green-500': '#22c55e',
		'bg-red-500': '#ef4444'
	}
	const colors = statusData.map(s => colorMap[s.color] || '#6b7280')
	
	ivrEligibilityChartInstance = new Chart(ivrEligibilityChartRef.value, {
		type: 'doughnut',
		data: {
			labels: statusData.map(s => s.name.charAt(0).toUpperCase() + s.name.slice(1)),
			datasets: [{
				data: statusData.map(s => s.count),
				backgroundColor: colors,
				borderWidth: 2,
				borderColor: '#fff'
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: true,
			plugins: {
				legend: { display: false },
				tooltip: {
					callbacks: {
						label: (context) => {
							const label = context.label || ''
							const value = context.parsed
							const total = (context.dataset.data as number[]).reduce((a, b) => a + b, 0)
							const percentage = ((value / total) * 100).toFixed(1)
							return `${label}: ${value} (${percentage}%)`
						}
					}
				}
			}
		}
	})
}

// Watch for report type changes to render appropriate charts
watch([reportType, dateRange, orders, inventory, usageLogs, invoices, ivrRequests, clinicFilter, brandFilter, startDate, endDate], () => {
	if (reportType.value === 'orders') {
		setTimeout(() => {
			renderStatusChart()
			// Render brand chart or size chart based on filter
			if (brandFilter.value !== 'all') {
				renderSizeChart()
			} else {
				renderBrandChart()
			}
		}, 100)
	} else if (reportType.value === 'inventory') {
		setTimeout(() => {
			renderInventoryStatusChart()
			renderInventoryBrandChart()
		}, 100)
	} else if (reportType.value === 'usage') {
		setTimeout(() => {
			renderUsageWoundPartChart()
			renderUsageBrandChart()
		}, 100)
	} else if (reportType.value === 'invoices') {
		setTimeout(() => {
			renderInvoiceStatusChart()
		}, 100)
	} else if (reportType.value === 'ivr') {
		setTimeout(() => {
			renderIVREligibilityChart()
		}, 100)
	} else {
		setTimeout(renderChart, 100)
	}
}, { deep: true })

// Validate custom date range (maximum 2 months / 60 days)
watch([startDate, endDate], () => {
	if (dateRange.value === 'custom' && startDate.value && endDate.value) {
		const start = new Date(startDate.value)
		const end = new Date(endDate.value)
		
		// Calculate difference in days
		const diffTime = Math.abs(end.getTime() - start.getTime())
		const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
		
		if (diffDays > 60) {
			dateRangeError.value = `Date range is ${diffDays} days. Please select a range within 2 months (60 days) for optimal performance.`
		} else if (end < start) {
			dateRangeError.value = 'End date must be after start date.'
		} else {
			dateRangeError.value = ''
		}
	} else {
		dateRangeError.value = ''
	}
})
</script>
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
						'p-5 border-2 rounded-xl cursor-pointer transition-all duration-200 group hover:shadow-md',
						reportType === type.id
							? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 shadow-sm'
							: 'border-gray-200 dark:border-gray-600 hover:border-blue-300 dark:hover:border-blue-700'
					]"
				>
					<div class="flex items-start space-x-4">
						<div :class="[
							'p-3 rounded-lg transition-all duration-200',
							reportType === type.id 
								? 'bg-blue-100 dark:bg-blue-900/40' 
								: 'bg-gray-100 dark:bg-gray-700 group-hover:bg-blue-50 dark:group-hover:bg-blue-900/30'
						]">
							<component 
								:is="type.icon" 
								:class="[
									'w-6 h-6 transition-colors duration-200',
									reportType === type.id ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400 group-hover:text-blue-500'
								]" 
							/>
						</div>
						<div class="flex-1">
							<h4 :class="[
								'font-semibold text-base mb-1',
								reportType === type.id ? 'text-blue-900 dark:text-blue-100' : 'text-gray-900 dark:text-white'
							]">
								{{ type.name }}
							</h4>
							<p :class="[
								'text-sm leading-relaxed',
								reportType === type.id ? 'text-blue-700 dark:text-blue-300' : 'text-gray-600 dark:text-gray-400'
							]">
								{{ type.description }}
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Filters Card -->
		<div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
			<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Report Filters</h3>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Date Range
					</label>
					<select
						v-model="dateRange"
						class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
					>
						<option v-for="option in dateRangeOptions" :key="option.value" :value="option.value">
							{{ option.label }}
						</option>
					</select>
				</div>

				<div v-if="dateRange === 'custom'" class="md:col-span-2">
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Custom Date Range <span class="text-xs text-gray-500">(Maximum 2 months)</span>
					</label>
					<div class="grid grid-cols-2 gap-2">
						<input
							v-model="startDate"
							type="date"
							placeholder="Start Date"
							class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
						/>
						<input
							v-model="endDate"
							type="date"
							placeholder="End Date"
							class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
						/>
					</div>
					<p v-if="dateRangeError" class="mt-2 text-sm text-red-600 dark:text-red-400">
						{{ dateRangeError }}
					</p>
				</div>

				<div :class="dateRange === 'custom' ? '' : 'md:col-span-2'">
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Clinic
					</label>
					<select
						v-model="clinicFilter"
						class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Clinics</option>
						<option v-for="clinic in clinics" :key="clinic.id" :value="clinic.id">
							{{ clinic.name }}
						</option>
					</select>
				</div>

				<!-- Brand Filter - Only show for Orders, Inventory, Usage reports -->
				<div v-if="['orders', 'inventory', 'usage'].includes(reportType)">
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Brand
					</label>
					<select
						v-model="brandFilter"
						class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Brands</option>
						<option v-for="brand in brands" :key="brand.id" :value="brand.id">
							{{ brand.name }}
						</option>
					</select>
				</div>

				<!-- Manufacturer Filter - Only show for IVR report -->
				<div v-if="reportType === 'ivr'">
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Manufacturer
					</label>
					<select
						v-model="manufacturerFilter"
						class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Manufacturers</option>
						<option v-for="manufacturer in manufacturers" :key="manufacturer.id" :value="manufacturer.id">
							{{ manufacturer.name }}
						</option>
					</select>
				</div>
			</div>
			
			<div class="mt-6 flex items-center gap-3">
				<button
					@click="handleGenerateReport"
					:disabled="isLoadingData || (dateRange === 'custom' && dateRangeError !== '')"
					class="flex items-center justify-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group disabled:opacity-50 disabled:cursor-not-allowed"
				>
					<ChartBarIcon v-if="!isLoadingData" class="w-5 h-5 text-white group-hover:scale-110 transition-transform" />
					<svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<span>{{ isLoadingData ? 'Generating...' : 'Generate Report' }}</span>
				</button>
				<button
					v-if="selectedReportType"
					@click="exportReport"
					:disabled="isExporting || !reportGenerated"
					class="flex items-center space-x-2 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
				>
					<ArrowDownTrayIcon v-if="!isExporting" class="w-5 h-5" />
					<svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<span>{{ isExporting ? 'Exporting...' : 'Export Report' }}</span>
				</button>
			</div>
		</div>

		<!-- Report Preview -->
		<div v-if="selectedReportType && reportGenerated" class="report-preview-container space-y-6">
			<!-- PDF Header Section - Company Logo, Title, Filters, Timestamp -->
			<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
				<!-- Company Header -->
				<div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
					<div>
						<h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">WoundMed Inc.</h1>
						<p class="text-sm text-gray-600 dark:text-gray-400">Medical Device Management System</p>
					</div>
					<div class="text-right">
						<div class="inline-flex items-center px-4 py-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
							<ChartBarIcon class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" />
							<span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Report Center</span>
						</div>
					</div>
				</div>

				<!-- Report Title and Date Range -->
				<div class="mb-6">
					<h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
						{{ selectedReportType.name }}
					</h2>
					<p class="text-lg text-gray-600 dark:text-gray-400">
						{{ dateRangeLabel }}
					</p>
				</div>

				<!-- Filters Applied Section -->
				<div class="bg-gray-50 dark:bg-gray-700/30 rounded-xl p-4 mb-4">
					<h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
						<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
						</svg>
						Filters Applied
					</h3>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
						<!-- Date Range Filter - Always shown -->
						<div class="bg-white dark:bg-gray-800 rounded-lg p-3">
							<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Date Range</p>
							<p class="text-sm font-medium text-gray-900 dark:text-white">
								{{ dateRange === 'custom' ? `${startDate} to ${endDate}` : dateRangeOptions.find(opt => opt.value === dateRange)?.label }}
							</p>
						</div>
						<!-- Clinic Filter - Show for all reports -->
						<div class="bg-white dark:bg-gray-800 rounded-lg p-3">
							<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Clinic</p>
							<p class="text-sm font-medium text-gray-900 dark:text-white">
								{{ clinicFilter === 'all' ? 'All Clinics' : clinics.find(c => c.id === clinicFilter)?.name || 'Unknown' }}
							</p>
						</div>
						<!-- Brand Filter - Show for orders, inventory, usage reports -->
						<div v-if="['orders', 'inventory', 'usage'].includes(reportType)" class="bg-white dark:bg-gray-800 rounded-lg p-3">
							<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Brand</p>
							<p class="text-sm font-medium text-gray-900 dark:text-white">
								{{ brandFilter === 'all' ? 'All Brands' : brands.find(b => b.id === brandFilter)?.name || 'Unknown' }}
							</p>
						</div>
						<!-- Manufacturer Filter - Show for IVR report -->
						<div v-else-if="reportType === 'ivr'" class="bg-white dark:bg-gray-800 rounded-lg p-3">
							<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Manufacturer</p>
							<p class="text-sm font-medium text-gray-900 dark:text-white">
								{{ manufacturerFilter === 'all' ? 'All Manufacturers' : (manufacturers.find(m => String(m.id) === String(manufacturerFilter))?.name || 'Unknown') }}
							</p>
						</div>
						<!-- Report Type Info - Show for invoice report -->
						<div v-else class="bg-white dark:bg-gray-800 rounded-lg p-3">
							<p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Report Type</p>
							<p class="text-sm font-medium text-gray-900 dark:text-white capitalize">
								{{ selectedReportType?.name.replace(' Report', '') }}
							</p>
						</div>
					</div>
				</div>

				<!-- Generated Timestamp -->
				<div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
					<div class="flex items-center">
						<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
						</svg>
						<span>Generated: {{ currentTimestamp }}</span>
					</div>
					<div class="flex items-center">
						<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
						</svg>
						<span>Generated by: Report Center System</span>
					</div>
				</div>
			</div>
			<!-- Orders Report -->
			<div v-if="reportType === 'orders'" class="space-y-6">
			<!-- A. Order Volume KPI Card -->
			<div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Total Orders</p>
						<p class="text-4xl font-bold text-gray-900 dark:text-white">{{ totalOrders }}</p>
						<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ dateRangeLabel }}</p>
					</div>
					<div class="p-4 bg-blue-100 dark:bg-blue-900/40 rounded-xl">
						<TrendingUp class="w-10 h-10 text-blue-600 dark:text-blue-400" />
					</div>
				</div>
			</div>

			<!-- B. Order Status Breakdown (VERY IMPORTANT) -->
			<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
				<div class="flex items-center justify-between mb-6">
					<div>
						<h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
							<span class="w-1 h-6 bg-blue-600 rounded-full mr-3"></span>
							Order Status Breakdown
							<span class="ml-2 text-xs font-medium px-2 py-0.5 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200 rounded-full">Very Important</span>
						</h3>
						<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Track order fulfillment progress</p>
					</div>
				</div>
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
					<!-- Pie Chart -->
					<div class="bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-700/30 dark:to-gray-800/30 border border-gray-200 dark:border-gray-600 rounded-xl p-6">
						<canvas ref="statusChartRef" style="max-width:100%;max-height:300px;"></canvas>
					</div>
					<!-- Status List -->
					<div class="space-y-3">
						<div v-for="status in orderStatusBreakdown" :key="status.name" 
							class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl hover:shadow-md transition-all">
							<div class="flex items-center space-x-3">
								<div :class="`w-3 h-3 rounded-full ${status.color}`"></div>
								<span class="font-medium text-gray-900 dark:text-white capitalize">{{ status.name }}</span>
							</div>
							<div class="text-right">
								<p class="text-2xl font-bold text-gray-900 dark:text-white">{{ status.count }}</p>
								<p class="text-xs text-gray-500 dark:text-gray-400">{{ status.percentage }}%</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- C & D: Orders by Clinic and Brand -->
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
				<!-- C. Dynamic Section: Top Clinics OR Top Products (when clinic is filtered) -->
				<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
					<!-- Show Top Products when clinic is filtered -->
					<div v-if="clinicFilter !== 'all'">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
								<CubeIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Products</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Most ordered products</p>
							</div>
						</div>
						<div class="space-y-3">
							<div v-for="(product, index) in topProducts" :key="product.product_id" 
								class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
								<div class="flex items-center space-x-3">
									<div :class="[
										'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white',
										index === 0 ? 'bg-yellow-500' : 
										index === 1 ? 'bg-gray-400' : 
										index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">
										{{ index + 1 }}
									</div>
									<div class="flex-1 min-w-0">
										<p class="font-medium text-gray-900 dark:text-white truncate">{{ product.name }}</p>
										<p class="text-xs text-gray-500 dark:text-gray-400">{{ product.count }} orders</p>
									</div>
								</div>
								<div class="flex-shrink-0">
									<div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
										<div class="bg-blue-500 h-2 rounded-full" :style="{ width: `${product.percentage}%` }"></div>
									</div>
								</div>
							</div>
							<div v-if="topProducts.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
								No product data available
							</div>
						</div>
					</div>
					<!-- Show Top Clinics when no clinic filter -->
					<div v-else>
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
								<Building2 class="w-5 h-5 text-green-600 dark:text-green-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clinics</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Orders by clinic</p>
							</div>
						</div>
						<div class="space-y-3">
							<div v-for="(clinic, index) in topClinics" :key="clinic.clinic_id" 
								class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
								<div class="flex items-center space-x-3">
									<div :class="[
										'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white',
										index === 0 ? 'bg-yellow-500' : 
										index === 1 ? 'bg-gray-400' : 
										index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">
										{{ index + 1 }}
									</div>
									<div class="flex-1 min-w-0">
										<p class="font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
										<p class="text-xs text-gray-500 dark:text-gray-400">{{ clinic.count }} orders</p>
									</div>
								</div>
								<div class="flex-shrink-0">
									<div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
										<div class="bg-green-500 h-2 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
									</div>
								</div>
							</div>
							<div v-if="topClinics.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
								No clinic data available
							</div>
						</div>
					</div>
				</div>

				<!-- D. Dynamic Section: Brand Distribution OR Size Distribution (when brand is filtered) -->
				<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
					<!-- Show Size Distribution when brand is filtered -->
					<div v-if="brandFilter !== 'all'">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
								<ChartBarSquareIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Size Distribution</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Orders by graft size</p>
							</div>
						</div>
						<div class="flex items-center justify-center mb-4">
							<canvas ref="sizeChartRef" style="max-width:250px;max-height:250px;"></canvas>
						</div>
						<div class="space-y-2">
							<div v-for="size in sizeDistribution" :key="size.size_id" 
								class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-3 h-3 rounded-full ${size.color}`"></div>
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ size.name }}</span>
								</div>
								<span class="text-sm font-bold text-gray-900 dark:text-white">{{ size.count }}</span>
							</div>
							<div v-if="sizeDistribution.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
								No size data available
							</div>
						</div>
					</div>
					<!-- Show Brand Distribution when no brand filter -->
					<div v-else>
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
								<Package class="w-5 h-5 text-purple-600 dark:text-purple-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Brand Distribution</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Orders by brand</p>
							</div>
						</div>
						<div class="flex items-center justify-center mb-4">
							<canvas ref="brandChartRef" style="max-width:250px;max-height:250px;"></canvas>
						</div>
						<div class="space-y-2">
							<div v-for="brand in brandDistribution" :key="brand.brand_id" 
								class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-3 h-3 rounded-full ${brand.color}`"></div>
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ brand.name }}</span>
								</div>
								<span class="text-sm font-bold text-gray-900 dark:text-white">{{ brand.count }}</span>
							</div>
							<div v-if="brandDistribution.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
								No brand data available
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>

			<!-- Inventory Report (Enhanced) -->
			<div v-else-if="reportType === 'inventory'" class="space-y-6">
				<!-- A. Total Inventory KPI Card -->
				<div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-100 dark:border-green-800">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Total Inventory Items</p>
							<p class="text-4xl font-bold text-gray-900 dark:text-white">{{ totalInventory }}</p>
							<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ dateRangeLabel }}</p>
						</div>
						<div class="p-4 bg-green-100 dark:bg-green-900/40 rounded-xl">
							<CubeIcon class="w-10 h-10 text-green-600 dark:text-green-400" />
						</div>
					</div>
				</div>

				<!-- B. Inventory Status Breakdown (VERY IMPORTANT) -->
				<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
					<div class="flex items-center justify-between mb-6">
						<div>
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
								<span class="w-1 h-6 bg-green-600 rounded-full mr-3"></span>
								Inventory Status Breakdown
								<span class="ml-2 text-xs font-medium px-2 py-0.5 bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200 rounded-full">Very Important</span>
							</h3>
							<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Track inventory lifecycle and utilization</p>
						</div>
					</div>
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
						<!-- Pie Chart -->
						<div class="bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-700/30 dark:to-gray-800/30 border border-gray-200 dark:border-gray-600 rounded-xl p-6">
							<canvas ref="inventoryStatusChartRef" style="max-width:100%;max-height:300px;"></canvas>
						</div>
						<!-- Status List -->
						<div class="space-y-3">
							<div v-for="status in inventoryStatusBreakdown" :key="status.name" 
								class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/30 rounded-xl hover:shadow-md transition-all">
								<div class="flex items-center space-x-3">
									<div :class="`w-3 h-3 rounded-full ${status.color}`"></div>
									<span class="font-medium text-gray-900 dark:text-white capitalize">{{ status.name }}</span>
								</div>
								<div class="text-right">
									<p class="text-2xl font-bold text-gray-900 dark:text-white">{{ status.count }}</p>
									<p class="text-xs text-gray-500 dark:text-gray-400">{{ status.percentage }}%</p>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- C & D: Top Clinics and Brand Distribution -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
					<!-- C. Top Clinics by Inventory -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
								<Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clinics</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Inventory by clinic</p>
							</div>
						</div>
						<div class="space-y-3">
							<div v-for="(clinic, index) in topInventoryClinics" :key="clinic.clinic_id" 
								class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">
								<div class="flex items-center space-x-3">
									<div :class="[
										'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white',
										index === 0 ? 'bg-yellow-500' : 
										index === 1 ? 'bg-gray-400' : 
										index === 2 ? 'bg-orange-600' : 'bg-gray-500'
									]">
										{{ index + 1 }}
									</div>
									<div class="flex-1 min-w-0">
										<p class="font-medium text-gray-900 dark:text-white truncate">{{ clinic.name }}</p>
										<p class="text-xs text-gray-500 dark:text-gray-400">{{ clinic.count }} items</p>
									</div>
								</div>
								<div class="flex-shrink-0">
									<div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
										<div class="bg-blue-500 h-2 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
									</div>
								</div>
							</div>
							<div v-if="topInventoryClinics.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
								No clinic data available
							</div>
						</div>
					</div>

					<!-- D. Inventory by Brand -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
								<Package class="w-5 h-5 text-purple-600 dark:text-purple-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Brand Distribution</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Inventory by brand</p>
							</div>
						</div>
						<div class="flex items-center justify-center mb-4">
							<canvas ref="inventoryBrandChartRef" style="max-width:250px;max-height:250px;"></canvas>
						</div>
						<div class="space-y-2">
							<div v-for="brand in inventoryBrandDistribution" :key="brand.brand_id" 
								class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-3 h-3 rounded-full ${brand.color}`"></div>
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ brand.name }}</span>
								</div>
								<span class="text-sm font-bold text-gray-900 dark:text-white">{{ brand.count }}</span>
							</div>
							<div v-if="inventoryBrandDistribution.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
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

			<!-- Other Report Types (Usage, Invoices, IVR) -->
			<div v-else-if="reportType === 'usage'" class="space-y-6">
				<!-- Usage Report: Track graft usage patterns -->
				
				<!-- A. Total Usage KPI -->
				<div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-100 dark:border-green-800">
					<div class="flex items-center justify-between">
						<div>
							<p class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Total Grafts Used</p>
							<p class="text-4xl font-bold text-gray-900 dark:text-white">{{ filteredUsageLogs.length }}</p>
							<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ dateRangeLabel }}</p>
						</div>
						<div class="p-4 bg-green-100 dark:bg-green-900/40 rounded-xl">
							<ArrowTrendingUpIcon class="w-10 h-10 text-green-600 dark:text-green-400" />
						</div>
					</div>
				</div>

				<!-- B. Usage by Wound Part & C. Top Clinics -->
				<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
					<!-- Usage by Wound Part -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
								<TrendingUp class="w-5 h-5 text-orange-600 dark:text-orange-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Usage by Wound Part</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Distribution by body part</p>
							</div>
						</div>
						<div class="flex items-center justify-center mb-4">
							<canvas ref="usageWoundPartChartRef" style="max-width:250px;max-height:250px;"></canvas>
						</div>
						<div class="space-y-2">
							<div v-for="part in usageByWoundPart" :key="part.name" 
								class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-3 h-3 rounded-full ${part.color}`"></div>
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ part.name }}</span>
								</div>
								<span class="text-sm font-bold text-gray-900 dark:text-white">{{ part.count }}</span>
							</div>
							<div v-if="usageByWoundPart.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
								No usage data available
							</div>
						</div>
					</div>

					<!-- Top Clinics by Usage -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
								<Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clinics by Usage</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Highest usage volume</p>
							</div>
						</div>
						<div class="space-y-3">
							<div v-for="clinic in topUsageClinics" :key="clinic.clinic_id" class="space-y-1">
								<div class="flex items-center justify-between">
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ clinic.name }}</span>
									<span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ clinic.count }}</span>
								</div>
								<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
									<div class="bg-blue-500 h-2 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
								</div>
							</div>
							<div v-if="topUsageClinics.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
								No clinic data available
							</div>
						</div>
					</div>
				</div>

				<!-- D. Brand Usage Distribution -->
				<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
					<div class="flex items-center space-x-2 mb-6">
						<div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
							<Package class="w-5 h-5 text-purple-600 dark:text-purple-400" />
						</div>
						<div>
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Brand Usage Distribution</h3>
							<p class="text-xs text-gray-600 dark:text-gray-400">Usage by brand</p>
						</div>
					</div>
					<div class="flex items-center justify-center mb-4">
						<canvas ref="usageBrandChartRef" style="max-width:250px;max-height:250px;"></canvas>
					</div>
					<div class="space-y-2">
						<div v-for="brand in usageBrandDistribution" :key="brand.brand_id" 
							class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
							<div class="flex items-center space-x-2">
								<div :class="`w-3 h-3 rounded-full ${brand.color}`"></div>
								<span class="text-sm font-medium text-gray-900 dark:text-white">{{ brand.name }}</span>
							</div>
							<span class="text-sm font-bold text-gray-900 dark:text-white">{{ brand.count }}</span>
						</div>
						<div v-if="usageBrandDistribution.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
							No brand data available
						</div>
					</div>
				</div>
			</div>
			
				<!-- Invoice Report -->
				<div v-else-if="reportType === 'invoices'" class="space-y-6">
					<!-- A. Invoice Summary KPIs -->
					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<!-- Total Invoices -->
						<div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800">
							<div>
								<p class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">Total Invoices</p>
								<p class="text-4xl font-bold text-gray-900 dark:text-white">{{ filteredInvoices.length }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ dateRangeLabel }}</p>
							</div>
						</div>
			
						<!-- Total Amount -->
						<div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-100 dark:border-green-800">
							<div>
								<p class="text-sm font-medium text-green-600 dark:text-green-400 mb-1">Total Amount</p>
								<p class="text-4xl font-bold text-gray-900 dark:text-white">${{ totalInvoiceAmount.toLocaleString() }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">All invoices</p>
							</div>
						</div>
			
						<!-- Pending Amount -->
						<div class="bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-2xl p-6 border border-orange-100 dark:border-orange-800">
							<div>
								<p class="text-sm font-medium text-orange-600 dark:text-orange-400 mb-1">Pending Amount</p>
								<p class="text-4xl font-bold text-gray-900 dark:text-white">${{ pendingInvoiceAmount.toLocaleString() }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Awaiting payment</p>
							</div>
						</div>
					</div>
			
					<!-- B. Invoice Status Breakdown -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
								<CurrencyDollarIcon class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Invoice Status Breakdown</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Payment status distribution</p>
							</div>
						</div>
						<div class="flex items-center justify-center mb-4">
							<canvas ref="invoiceStatusChartRef" style="max-width:250px;max-height:250px;"></canvas>
						</div>
						<div class="space-y-2">
							<div v-for="status in invoiceStatusBreakdown" :key="status.name" 
								class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
								<div class="flex items-center space-x-2">
									<div :class="`w-3 h-3 rounded-full ${status.color}`"></div>
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ status.displayName }}</span>
								</div>
								<div class="text-right">
									<span class="text-sm font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
									<span class="text-xs text-gray-500 ml-2">${{ status.amount.toLocaleString() }}</span>
								</div>
							</div>
							<div v-if="invoiceStatusBreakdown.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
								No invoice data available
							</div>
						</div>
					</div>
			
					<!-- C. Top Clinics by Invoice Amount -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
								<Building2 class="w-5 h-5 text-purple-600 dark:text-purple-400" />
							</div>
							<div>
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Clinics by Invoice Amount</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">Highest billing volume</p>
							</div>
						</div>
						<div class="space-y-3">
							<div v-for="clinic in topInvoiceClinics" :key="clinic.clinic_id" class="space-y-1">
								<div class="flex items-center justify-between">
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ clinic.name }}</span>
									<span class="text-sm font-bold text-purple-600 dark:text-purple-400">${{ clinic.amount.toLocaleString() }}</span>
								</div>
								<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
									<div class="bg-purple-500 h-2 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
								</div>
							</div>
							<div v-if="topInvoiceClinics.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
								No clinic data available
							</div>
						</div>
					</div>
				</div>
			
				<!-- IVR Report -->
				<div v-else-if="reportType === 'ivr'" class="space-y-6">
					<!-- A. IVR Summary KPI -->
					<div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl p-6 border border-indigo-100 dark:border-indigo-800">
						<div class="flex items-center justify-between">
							<div>
								<p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mb-1">Total IVR Requests</p>
								<p class="text-4xl font-bold text-gray-900 dark:text-white">{{ filteredIVRRequests.length }}</p>
								<p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ dateRangeLabel }}</p>
							</div>
							<div class="p-4 bg-indigo-100 dark:bg-indigo-900/40 rounded-xl">
								<PhoneIcon class="w-10 h-10 text-indigo-600 dark:text-indigo-400" />
							</div>
						</div>
					</div>
			
					<!-- B. Eligibility Status Breakdown & C. Top Manufacturers -->
					<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
						<!-- Eligibility Status Breakdown -->
						<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
							<div class="flex items-center space-x-2 mb-6">
								<div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
									<PhoneIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
								</div>
								<div>
									<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Eligibility Status</h3>
									<p class="text-xs text-gray-600 dark:text-gray-400">Verification status breakdown</p>
								</div>
							</div>
							<div class="flex items-center justify-center mb-4">
								<canvas ref="ivrEligibilityChartRef" style="max-width:250px;max-height:250px;"></canvas>
							</div>
							<div class="space-y-2">
								<div v-for="status in ivrEligibilityBreakdown" :key="status.name" 
									class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
									<div class="flex items-center space-x-2">
										<div :class="`w-3 h-3 rounded-full ${status.color}`"></div>
										<span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ status.name }}</span>
									</div>
									<span class="text-sm font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
								</div>
								<div v-if="ivrEligibilityBreakdown.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
									No IVR data available
								</div>
							</div>
						</div>
			
						<!-- Top Manufacturers by IVR Volume - Only show when no manufacturer filter -->
						<div v-if="manufacturerFilter === 'all'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
							<div class="flex items-center space-x-2 mb-6">
								<div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg">
									<Package class="w-5 h-5 text-orange-600 dark:text-orange-400" />
								</div>
								<div>
									<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Manufacturers</h3>
									<p class="text-xs text-gray-600 dark:text-gray-400">By IVR request volume</p>
								</div>
							</div>
							<div class="space-y-3">
								<div v-for="mfr in topIVRManufacturers" :key="mfr.manufacturer_id" class="space-y-1">
									<div class="flex items-center justify-between">
										<span class="text-sm font-medium text-gray-900 dark:text-white">{{ mfr.name }}</span>
										<span class="text-sm font-bold text-orange-600 dark:text-orange-400">{{ mfr.count }}</span>
									</div>
									<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
										<div class="bg-orange-500 h-2 rounded-full" :style="{ width: `${mfr.percentage}%` }"></div>
									</div>
								</div>
								<div v-if="topIVRManufacturers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
									No manufacturer data available
								</div>
							</div>
						</div>

						<!-- Manufacturer Insights - Show when manufacturer is filtered -->
						<div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
							<div class="flex items-center space-x-2 mb-6">
								<div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
									<Package class="w-5 h-5 text-purple-600 dark:text-purple-400" />
								</div>
								<div class="flex-1">
									<h3 class="text-lg font-semibold text-gray-900 dark:text-white">Manufacturer Insights</h3>
									<p class="text-xs text-gray-600 dark:text-gray-400">{{ manufacturers.find(m => String(m.id) === String(manufacturerFilter))?.name || 'Selected Manufacturer' }}</p>
								</div>
							</div>
							<div class="grid grid-cols-2 gap-4">
								<!-- Total Requests -->
								<div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-purple-100 dark:border-purple-800">
									<p class="text-xs font-medium text-purple-600 dark:text-purple-400 mb-1">Total Requests</p>
									<p class="text-3xl font-bold text-gray-900 dark:text-white">{{ filteredIVRRequests.length }}</p>
								</div>
								<!-- Approval Rate -->
								<div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800">
									<p class="text-xs font-medium text-green-600 dark:text-green-400 mb-1">Approval Rate</p>
									<p class="text-3xl font-bold text-gray-900 dark:text-white">{{ manufacturerApprovalRate }}%</p>
								</div>
							</div>
							<!-- Status Breakdown -->
							<div class="mt-4 space-y-2">
								<div v-for="status in ivrEligibilityBreakdown" :key="status.name" 
									class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
									<div class="flex items-center space-x-2">
										<div :class="`w-3 h-3 rounded-full ${status.color}`"></div>
										<span class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ status.name }}</span>
									</div>
									<div class="flex items-center space-x-3">
										<span class="text-sm font-bold text-gray-900 dark:text-white">{{ status.count }}</span>
										<span class="text-xs text-gray-500 dark:text-gray-400">({{ ((status.count / filteredIVRRequests.length) * 100).toFixed(1) }}%)</span>
									</div>
								</div>
								<div v-if="ivrEligibilityBreakdown.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
									No data available for this manufacturer
								</div>
							</div>
						</div>
					</div>
			
					<!-- D. Clinic Insights or Top Clinics by IVR Requests -->
					<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
						<div class="flex items-center space-x-2 mb-6">
							<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
								<Building2 class="w-5 h-5 text-blue-600 dark:text-blue-400" />
							</div>
							<div class="flex-1">
								<h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ clinicFilter !== 'all' ? 'Clinic Insights' : 'Top Clinics by IVR Requests' }}</h3>
								<p class="text-xs text-gray-600 dark:text-gray-400">{{ clinicFilter !== 'all' ? clinics.find(c => String(c.id) === String(clinicFilter))?.name || 'Selected Clinic' : 'Highest verification volume' }}</p>
							</div>
						</div>
						
						<!-- Clinic Insights - Show when clinic is filtered -->
						<div v-if="clinicFilter !== 'all'" class="grid grid-cols-2 gap-4">
							<!-- Total Requests -->
							<div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
								<p class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">Total Requests</p>
								<p class="text-3xl font-bold text-gray-900 dark:text-white">{{ filteredIVRRequests.length }}</p>
							</div>
							<!-- Approval Rate -->
							<div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-100 dark:border-green-800">
								<p class="text-xs font-medium text-green-600 dark:text-green-400 mb-1">Approval Rate</p>
								<p class="text-3xl font-bold text-gray-900 dark:text-white">{{ clinicApprovalRate }}%</p>
							</div>
						</div>
						
						<!-- Top Clinics - Show when no specific clinic is selected -->
						<div v-else class="space-y-3">
							<div v-for="clinic in topIVRClinics" :key="clinic.clinic_id" class="space-y-1">
								<div class="flex items-center justify-between">
									<span class="text-sm font-medium text-gray-900 dark:text-white">{{ clinic.name }}</span>
									<span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ clinic.count }}</span>
								</div>
								<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
									<div class="bg-blue-500 h-2 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
								</div>
							</div>
							<div v-if="topIVRClinics.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
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
	PhoneIcon
} from '@heroicons/vue/24/outline'
import { Chart, registerables, type ChartType } from 'chart.js'
import type { Ref } from 'vue'
import { orderService, userService, brandService, inventoryService, invoiceService, ivrService, returnsService } from '@/services/api'
import { TrendingUp, Package, Building2 } from 'lucide-vue-next'
import html2canvas from 'html2canvas'

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

const exportReport = async () => {
	if (!reportGenerated.value) {
		alert('Please generate a report first before exporting.')
		return
	}
	
	isExporting.value = true
	
	try {
		// Find the report preview container
		const reportElement = document.querySelector('.report-preview-container') as HTMLElement
		
		if (!reportElement) {
			console.error('Report preview container not found')
			alert('Unable to export report. Please try again.')
			isExporting.value = false
			return
		}
		
		// Capture the report as canvas
		const canvas = await html2canvas(reportElement, {
			scale: 2,
			useCORS: true,
			logging: false,
			backgroundColor: '#ffffff',
			windowWidth: reportElement.scrollWidth,
			windowHeight: reportElement.scrollHeight
		})
		
		const imgData = canvas.toDataURL('image/png')
		
		// Dynamic import for jsPDF
		const { default: jsPDF } = await import('jspdf')
		
		// Create PDF with proper typing
		const pdf = new (jsPDF as any)({
			orientation: 'portrait',
			unit: 'mm',
			format: 'a4'
		})
		
		const pdfWidth = pdf.internal.pageSize.getWidth()
		const pdfHeight = pdf.internal.pageSize.getHeight()
		
		// Calculate dimensions
		const imgWidth = pdfWidth - 20
		const imgHeight = (canvas.height * imgWidth) / canvas.width
		
		let heightLeft = imgHeight
		let position = 10
		
		// Add first page
		pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight)
		heightLeft -= (pdfHeight - 20)
		
		// Add additional pages if needed
		while (heightLeft > 0) {
			position = heightLeft - imgHeight + 10
			pdf.addPage()
			pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight)
			heightLeft -= (pdfHeight - 20)
		}
		
		// Generate filename
		const reportName = selectedReportType.value?.name.replace(/\s+/g, '_').toLowerCase() || 'report'
		const dateLabel = dateRange.value.replace('_', '-')
		const timestamp = new Date().toISOString().split('T')[0]
		const filename = `${reportName}_${dateLabel}_${timestamp}.pdf`
		
		// Save PDF
		pdf.save(filename)
		
		console.log('✅ Report exported successfully:', filename)
	} catch (error) {
		console.error('❌ Error exporting report:', error)
		alert('Failed to export report. Please try again.')
	} finally {
		isExporting.value = false
	}
}

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
	const colors = ['#eab308', '#22c55e', '#ef4444']
	
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
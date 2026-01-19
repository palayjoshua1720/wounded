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
						Custom Date Range
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
						<option value="clinic-1">Metro Wound Care Center</option>
						<option value="clinic-2">Advanced Healing Institute</option>
					</select>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
						Brand
					</label>
					<select
						v-model="brandFilter"
						class="w-full px-3 py-3 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white dark:focus:bg-gray-700 text-gray-900 dark:text-white transition-all duration-200"
					>
						<option value="all">All Brands</option>
						<option value="brand-1">DermaGraft Pro</option>
						<option value="brand-2">HealMatrix Advanced</option>
						<option value="brand-3">Biolab Skin Graft</option>
					</select>
				</div>
			</div>
			
			<div class="mt-6 flex items-center gap-3">
				<button
					@click="handleGenerateReport"
					class="flex items-center justify-center space-x-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg group"
				>
					<ChartBarIcon class="w-5 h-5 text-white group-hover:scale-110 transition-transform" />
					<span>Generate Report</span>
				</button>
				<button
					v-if="selectedReportType"
					@click="exportReport"
					class="flex items-center space-x-2 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200"
				>
					<ArrowDownTrayIcon class="w-5 h-5" />
					<span>Export Report</span>
				</button>
			</div>
		</div>

		<!-- Report Preview -->
		<div v-if="selectedReportType" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
			<div class="p-6">
				<div class="flex items-center justify-between mb-6">
					<div class="flex items-center space-x-3">
						<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
							<component :is="selectedReportType.icon" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
						</div>
						<div>
							<h3 class="text-lg font-semibold text-gray-900 dark:text-white">
								{{ selectedReportType.name }}
							</h3>
							<p class="text-sm text-gray-600 dark:text-gray-400">{{ selectedReportType.description }}</p>
						</div>
					</div>
					<div class="relative">
						<label class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">Visualization:</label>
						<select v-model="graphType" class="px-3 py-2 border-0 bg-gray-50 dark:bg-gray-700/50 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white">
							<option value="area">Area Chart</option>
							<option value="line">Line Chart</option>
							<option value="bar">Bar Chart</option>
						</select>
					</div>
				</div>

				<!-- Chart Visualization -->
				<div class="mb-6">
					<div class="bg-gradient-to-br from-gray-50 to-gray-100/50 dark:from-gray-700/30 dark:to-gray-800/30 border border-gray-200 dark:border-gray-600 rounded-xl p-6">
						<canvas ref="chartRef" style="max-width:100%;max-height:400px;"></canvas>
					</div>
				</div>
			</div>

			<!-- Data Table -->
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
					<thead class="bg-gray-50/80 dark:bg-gray-700/50 backdrop-blur-sm">
						<tr>
							<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Metric
							</th>
							<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Value
							</th>
							<th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
								Change
							</th>
						</tr>
					</thead>
					<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
						<tr v-for="row in tableData" :key="row.metric" class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors duration-150">
							<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
								{{ row.metric }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
								{{ row.value }}
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
								{{ row.change }}
							</td>
						</tr>
					</tbody>
				</table>
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

Chart.register(...registerables)

// --- MOCK DATA IMPORTS (copied from views) ---
// Orders
const orders = [
	{ id: 'ORD-001', dateOfOrder: '2025-01-27T10:00:00Z', status: 'delivered', clinicId: '1', items: [{ brandId: '1' }] },
	{ id: 'ORD-002', dateOfOrder: '2025-01-26T14:00:00Z', status: 'shipped', clinicId: '2', items: [{ brandId: '2' }] },
	{ id: 'ORD-003', dateOfOrder: '2025-01-25T16:00:00Z', status: 'acknowledged', clinicId: '1', items: [{ brandId: '1' }] },
	{ id: 'ORD-004', dateOfOrder: '2025-01-27T08:00:00Z', status: 'submitted', clinicId: '2', items: [{ brandId: '2' }] },
]
// Usage
const usageLogs = [
	{ id: '1', usageDate: '2025-01-27', clinicId: '1', brandId: '1' },
	{ id: '2', usageDate: '2025-01-26', clinicId: '2', brandId: '2' },
	{ id: '3', usageDate: '2025-01-25', clinicId: '1', brandId: '1' },
	{ id: '4', usageDate: '2025-01-24', clinicId: '2', brandId: '2' },
	{ id: '5', usageDate: '2025-01-23', clinicId: '1', brandId: '1' },
]
// Inventory
const inventory = [
	{ id: '1', expiryDate: '2024-12-31', clinicId: '1', brandId: '1' },
	{ id: '2', expiryDate: '2025-03-15', clinicId: '2', brandId: '2' },
	{ id: '3', expiryDate: '2025-06-20', clinicId: '1', brandId: '1' },
	{ id: '4', expiryDate: '2025-09-10', clinicId: '2', brandId: '2' },
	{ id: '5', expiryDate: '2025-11-05', clinicId: '1', brandId: '1' },
]
// Invoices
const invoices = [
	{ id: '1', createdAt: '2025-01-15T10:00:00Z', amount: 2500, clinicId: '1', brandId: '1' },
	{ id: '2', createdAt: '2025-01-20T14:30:00Z', amount: 1800, clinicId: '2', brandId: '2' },
	{ id: '3', createdAt: '2025-01-01T09:00:00Z', amount: 3200, clinicId: '1', brandId: '1' },
	{ id: '4', createdAt: '2025-01-25T16:45:00Z', amount: 4500, clinicId: '2', brandId: '2' },
	{ id: '5', createdAt: '2025-01-10T11:20:00Z', amount: 1200, clinicId: '1', brandId: '1' },
]
// IVR
const ivrRequests = [
	{ id: '1', dateSubmitted: '2025-01-15', clinicId: '1' },
	{ id: '2', dateSubmitted: '2025-01-20', clinicId: '2' },
	{ id: '3', dateSubmitted: '2025-01-18', clinicId: '1' },
	{ id: '4', dateSubmitted: '2025-01-22', clinicId: '2' },
	{ id: '5', dateSubmitted: '2025-01-25', clinicId: '1' },
]

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
const dateRange = ref('last_7_days')
const startDate = ref('')
const endDate = ref('')
const clinicFilter = ref('all')
const brandFilter = ref('all')
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

const handleGenerateReport = () => {
	const filters = {
		reportType: reportType.value,
		dateRange: dateRange.value,
		startDate: dateRange.value === 'custom' ? startDate.value : undefined,
		endDate: dateRange.value === 'custom' ? endDate.value : undefined,
		clinicFilter: clinicFilter.value !== 'all' ? clinicFilter.value : undefined,
		brandFilter: brandFilter.value !== 'all' ? brandFilter.value : undefined
	}

	emit('generateReport', filters)
}

const exportReport = () => {
	// Implement export functionality
	console.log('Exporting report:', selectedReportType.value?.name)
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
		orders.forEach(o => {
			const d = o.dateOfOrder.split('T')[0]
			// For demo, assume orders have clinicId and brandId (mocked as '1' for all)
			if (clinic && o.clinicId !== clinic) return
			if (brand && o.items && !o.items.some(item => item.brandId === brand)) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Orders' }
	} else if (type === 'usage') {
		usageLogs.forEach(u => {
			const d = u.usageDate
			if (clinic && u.clinicId !== clinic) return
			if (brand && u.brandId !== brand) return // Add brandId to mock if needed
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Grafts Used' }
	} else if (type === 'inventory') {
		inventory.forEach(i => {
			const d = i.expiryDate
			if (clinic && i.clinicId !== clinic) return
			if (brand && i.brandId !== brand) return
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx]++
		})
		return { labels, data, label: 'Inventory Expiry' }
	} else if (type === 'invoices') {
		invoices.forEach(inv => {
			const d = inv.createdAt.split('T')[0]
			if (clinic && inv.clinicId !== clinic) return
			if (brand && inv.brandId !== brand) return // Add brandId to mock if needed
			const idx = labels.indexOf(d)
			if (idx !== -1) data[idx] += inv.amount
		})
		return { labels, data, label: 'Invoice Amount' }
	} else if (type === 'ivr') {
		ivrRequests.forEach(req => {
			const d = req.dateSubmitted
			if (clinic && req.clinicId !== clinic) return
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
onMounted(() => setTimeout(renderChart, 100))

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
</script>
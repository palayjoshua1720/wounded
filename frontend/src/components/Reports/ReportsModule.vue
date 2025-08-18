<template>
	<div class="space-y-6">
		<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
			<div class="flex items-center space-x-3 mb-6">
				<div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
					<ChartBarSquareIcon class="w-6 h-6 text-purple-600 dark:text-purple-400" />
				</div>
				<div>
					<h2 class="text-xl font-semibold text-gray-900 dark:text-white">Reports & Analytics</h2>
					<p class="text-gray-600 dark:text-gray-400">Generate comprehensive reports and insights</p>
				</div>
			</div>

			<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
				<!-- Report Type Selection -->
				<div class="lg:col-span-2">
					<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Select Report Type</h3>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div
						v-for="type in reportTypes"
						:key="type.id"
						@click="reportType = type.id"
						:class="[
						'p-4 border rounded-lg cursor-pointer transition-colors',
						reportType === type.id
							? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20'
							: 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'
						]"
					>
						<div class="flex items-center space-x-3">
						<component 
							:is="type.icon" 
							:class="[
							'w-6 h-6',
							reportType === type.id ? 'text-purple-600 dark:text-purple-400' : 'text-gray-400 dark:text-gray-500'
							]" 
						/>
						<div>
							<h4 :class="[
							'font-medium',
							reportType === type.id ? 'text-purple-900 dark:text-purple-100' : 'text-gray-900 dark:text-white'
							]">
							{{ type.name }}
							</h4>
							<p :class="[
							'text-sm',
							reportType === type.id ? 'text-purple-700 dark:text-purple-300' : 'text-gray-600 dark:text-gray-400'
							]">
							{{ type.description }}
							</p>
						</div>
						</div>
					</div>
					</div>
				</div>

				<!-- Filters -->
				<div>
					<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filters</h3>
					<div class="space-y-4">
						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Date Range
							</label>
							<select
							v-model="dateRange"
							class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							>
								<option v-for="option in dateRangeOptions" :key="option.value" :value="option.value">
									{{ option.label }}
								</option>
							</select>
						</div>

						<div v-if="dateRange === 'custom'" class="grid grid-cols-2 gap-2">
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									Start Date
								</label>
								<input
									v-model="startDate"
									type="date"
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
							<div>
								<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
									End Date
								</label>
								<input
									v-model="endDate"
									type="date"
									class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
								/>
							</div>
						</div>

						<div>
							<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
							Clinic
							</label>
							<select
							v-model="clinicFilter"
							class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
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
							class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
							>
								<option value="all">All Brands</option>
								<option value="brand-1">DermaGraft Pro</option>
								<option value="brand-2">HealMatrix Advanced</option>
								<option value="brand-3">Biolab Skin Graft</option>
							</select>
						</div>

						<button
							@click="handleGenerateReport"
							class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
						>
							<ChartBarIcon class="w-4 h-4 text-white" />
							<span>Generate Report</span>
						</button>
					</div>
				</div>
			</div>
		</div>

		<!-- Report Preview -->
		<div v-if="selectedReportType" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
			<div class="flex items-center justify-between mb-6">
				<h3 class="text-lg font-medium text-gray-900 dark:text-white">
					{{ selectedReportType.name }} Preview
				</h3>
				<button 
					@click="exportReport"
					class="flex items-center space-x-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
				>
					<ArrowDownTrayIcon class="w-4 h-4 text-gray-700 dark:text-gray-300" />
					<span>Export</span>
				</button>
			</div>

			<div class="space-y-6">
				<!-- Graph Type Selector -->
				<div class="flex items-center mb-2">
					<label class="mr-2 text-sm font-medium text-gray-700 dark:text-gray-300">Graph Type:</label>
					<select v-model="graphType" class="px-2 py-1 border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-purple-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
						<option value="area">Area</option>
						<option value="line">Line</option>
						<option value="bar">Bar</option>
					</select>
				</div>
				<!-- Chart.js Line Graph Area -->
				<div class="h-64 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg flex items-center justify-center">
					<canvas ref="chartRef" style="max-width:100%;max-height:100%;"></canvas>
				</div>

				<!-- Sample Data Table -->
				<div class="overflow-x-auto">
					<table class="w-full">
						<thead class="bg-gray-50 dark:bg-gray-700">
							<tr>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
									Metric
								</th>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
									Value
								</th>
								<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
									Change
								</th>
							</tr>
						</thead>
						<tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
							<tr v-for="row in tableData" :key="row.metric">
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
// Top Graft Sizes (mocked as usage count per size)
const topGraftSizes = [
	{ size: '4cm²', count: 12 },
	{ size: '6cm²', count: 8 },
	{ size: '10cm²', count: 5 },
	{ size: '16cm²', count: 3 },
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
	},
	{ 
		id: 'top_sizes', 
		name: 'Top Graft Sizes', 
		icon: ChartBarSquareIcon, 
		description: 'Most used graft sizes per clinic/patient' 
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
	} else if (type === 'top_sizes') {
		// For top graft sizes, use bar chart style data
		return {
			labels: topGraftSizes.map(g => g.size),
			data: topGraftSizes.map(g => g.count),
			label: 'Top Graft Sizes'
		}
	}
	return { labels: [], data: [], label: '' }
}

function getChartType(): ChartType {
	if (reportType.value === 'top_sizes') return 'bar'
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
			fill: graphType.value === 'area' || reportType.value === 'top_sizes',
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
<template>
	<div class="space-y-5">
		<!-- KPI Cards Row -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Orders</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalOrders }}</p>
					</div>
					<div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<ClipboardDocumentListIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
					</div>
				</div>
			</div>
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
					<span class="w-1 h-5 bg-red-500 rounded-full mr-2"></span>
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
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<component :is="clinicFilter !== 'all' ? CubeIcon : Building2" class="w-4 h-4 text-red-600 dark:text-red-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ clinicFilter !== 'all' ? 'Top Products' : 'Top Clinics' }}</h3>
				</div>
				<div class="space-y-2 flex-1 min-h-[120px]">
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
									<div class="bg-red-500 h-1.5 rounded-full" :style="{ width: `${product.percentage}%` }"></div>
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
					<div v-if="reportGenerated && !isLoadingData && (clinicFilter !== 'all' ? topProducts : topClinics).length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No data available
					</div>
				</div>
			</div>

			<!-- Brand OR Size Distribution -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
						<component :is="brandFilter !== 'all' ? ChartBarSquareIcon : Package" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ brandFilter !== 'all' ? 'Size Distribution' : 'Brand Distribution' }}</h3>
				</div>
				<div v-if="reportGenerated && !isLoadingData && (brandFilter !== 'all' ? sizeDistribution : brandDistribution).length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No data available
					</div>
				<div class="flex items-center justify-center mb-3">
					<canvas v-if="brandFilter !== 'all'" ref="sizeChartRef" style="max-width:180px;max-height:180px;"></canvas>
					<canvas v-else ref="brandChartRef" style="max-width:180px;max-height:180px;"></canvas>
				</div>
				<div class="space-y-1.5 flex-1 min-h-[80px]">
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
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import {
	ClipboardDocumentListIcon,
	CubeIcon,
	CurrencyDollarIcon,
	ChartBarSquareIcon
} from '@heroicons/vue/24/outline'
import { Package, Building2 } from 'lucide-vue-next'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps<{
	totalOrders: number
	totalOrderItems: number
	totalOrderValue: number
	orderStatusBreakdown: any[]
	topClinics: any[]
	topProducts: any[]
	brandDistribution: any[]
	sizeDistribution: any[]
	clinicFilter: string
	brandFilter: string
	reportGenerated: boolean
	isLoadingData: boolean
}>()

const statusChartRef = ref<HTMLCanvasElement | null>(null)
const brandChartRef = ref<HTMLCanvasElement | null>(null)
const sizeChartRef = ref<HTMLCanvasElement | null>(null)

let statusChartInstance: Chart | null = null
let brandChartInstance: Chart | null = null
let sizeChartInstance: Chart | null = null

function renderStatusChart() {
	if (!statusChartRef.value) return
	if (statusChartInstance) statusChartInstance.destroy()
	
	const statusData = props.orderStatusBreakdown
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

function renderBrandChart() {
	if (!brandChartRef.value) return
	if (brandChartInstance) brandChartInstance.destroy()
	
	const brandData = props.brandDistribution
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

function renderSizeChart() {
	if (!sizeChartRef.value) return
	if (sizeChartInstance) sizeChartInstance.destroy()
	
	const sizeData = props.sizeDistribution
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

onMounted(() => {
	setTimeout(() => {
		renderStatusChart()
		if (props.brandFilter !== 'all') {
			renderSizeChart()
		} else {
			renderBrandChart()
		}
	}, 100)
})

watch(() => [props.brandFilter, props.orderStatusBreakdown, props.brandDistribution, props.sizeDistribution], () => {
	setTimeout(() => {
		renderStatusChart()
		if (props.brandFilter !== 'all') {
			renderSizeChart()
		} else {
			renderBrandChart()
		}
	}, 100)
}, { deep: true })
</script>

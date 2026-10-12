<template>
	<div class="space-y-5">
		<!-- KPI Cards Row -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Used</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ usedInventoryCount }}</p>
					</div>
					<div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<CheckCircleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
					</div>
				</div>
			</div>
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<Building2 class="w-4 h-4 text-red-600 dark:text-red-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Clinics</h3>
				</div>
				<div class="space-y-2 flex-1 min-h-[120px]">
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
								<div class="bg-red-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
							</div>
						</div>
					</div>
					<div v-if="reportGenerated && !isLoadingData && topInventoryClinics.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No clinic data available
					</div>
				</div>
			</div>

			<!-- Brand Distribution -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
						<Package class="w-4 h-4 text-purple-600 dark:text-purple-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Brand Distribution</h3>
				</div>
				<div v-if="reportGenerated && !isLoadingData && inventoryBrandDistribution.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No brand data available
					</div>
				<div class="flex items-center justify-center mb-3">
					<canvas ref="inventoryBrandChartRef" style="max-width:180px;max-height:180px;"></canvas>
				</div>
				<div class="space-y-1.5 flex-1 min-h-[80px]">
					<div v-for="brand in inventoryBrandDistribution" :key="brand.brand_id" 
						class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
						<div class="flex items-center space-x-2">
							<div :class="`w-2.5 h-2.5 rounded-full ${brand.color}`"></div>
							<span class="text-xs text-gray-700 dark:text-gray-300">{{ brand.name }}</span>
						</div>
						<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ brand.count }}</span>
					</div>
				</div>
			</div>
		</div>

		<!-- E. Utilization Rate Card -->
		<div class="bg-gradient-to-br from-rose-50 to-purple-50 dark:from-rose-900/20 dark:to-purple-900/20 rounded-xl p-6 border border-rose-100 dark:border-rose-800">
			<div class="flex items-center justify-between mb-4">
				<div>
					<h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Utilization Rate</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400">Efficiency of inventory usage</p>
				</div>
				<div class="text-right">
					<p class="text-4xl font-bold text-rose-600 dark:text-rose-400">{{ utilizationRate }}%</p>
					<p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Used / Delivered</p>
				</div>
			</div>
			<div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
				<div class="bg-gradient-to-r from-rose-500 to-purple-500 h-4 rounded-full transition-all duration-500" 
					:style="{ width: `${utilizationRate}%` }"></div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import {
	CubeIcon,
	CheckCircleIcon,
	ArrowPathIcon
} from '@heroicons/vue/24/outline'
import { Package, Building2 } from 'lucide-vue-next'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps<{
	totalInventory: number
	usedInventoryCount: number
	inUseInventoryCount: number
	inventoryStatusBreakdown: any[]
	topInventoryClinics: any[]
	inventoryBrandDistribution: any[]
	utilizationRate: string
	reportGenerated: boolean
	isLoadingData: boolean
}>()

const inventoryStatusChartRef = ref<HTMLCanvasElement | null>(null)
const inventoryBrandChartRef = ref<HTMLCanvasElement | null>(null)

let inventoryStatusChartInstance: Chart | null = null
let inventoryBrandChartInstance: Chart | null = null

function renderInventoryStatusChart() {
	if (!inventoryStatusChartRef.value) return
	if (inventoryStatusChartInstance) inventoryStatusChartInstance.destroy()
	
	const statusData = props.inventoryStatusBreakdown
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

function renderInventoryBrandChart() {
	if (!inventoryBrandChartRef.value) return
	if (inventoryBrandChartInstance) inventoryBrandChartInstance.destroy()
	
	const brandData = props.inventoryBrandDistribution
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

onMounted(() => {
	setTimeout(() => {
		renderInventoryStatusChart()
		renderInventoryBrandChart()
	}, 100)
})

watch(() => [props.inventoryStatusBreakdown, props.inventoryBrandDistribution], () => {
	setTimeout(() => {
		renderInventoryStatusChart()
		renderInventoryBrandChart()
	}, 100)
}, { deep: true })
</script>

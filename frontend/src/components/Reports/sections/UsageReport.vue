<template>
	<div class="space-y-5">
		<!-- KPI Cards Row -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Grafts Used</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalUsageLogs }}</p>
					</div>
					<div class="p-2.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
						<ArrowTrendingUpIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
					</div>
				</div>
			</div>
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Top Wound Part</p>
						<p class="mt-1 text-sm italic font-normal text-gray-400 dark:text-gray-500">{{ usageByWoundPart[0]?.name || 'No data available' }}</p>
					</div>
					<div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
						<BeakerIcon class="w-5 h-5 text-orange-600 dark:text-orange-400" />
					</div>
				</div>
			</div>
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Top Brand</p>
						<p class="mt-1 text-sm italic font-normal text-gray-400 dark:text-gray-500">{{ usageBrandDistribution[0]?.name || 'No data available' }}</p>
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
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
						<BeakerIcon class="w-4 h-4 text-orange-600 dark:text-orange-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Usage by Wound Part</h3>
				</div>
				<div v-if="reportGenerated && !isLoadingData && usageByWoundPart.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No usage data available
					</div>
				<div class="flex items-center justify-center mb-3">
					<canvas ref="usageWoundPartChartRef" style="max-width:180px;max-height:180px;"></canvas>
				</div>
				<div class="space-y-1.5 flex-1 min-h-[80px]">
					<div v-for="part in usageByWoundPart" :key="part.name" 
						class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
						<div class="flex items-center space-x-2">
							<div :class="`w-2.5 h-2.5 rounded-full ${part.color}`"></div>
							<span class="text-xs text-gray-700 dark:text-gray-300">{{ part.name }}</span>
						</div>
						<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ part.count }}</span>
					</div>
				</div>
			</div>

			<!-- Top Clinics by Usage -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<Building2 class="w-4 h-4 text-red-600 dark:text-red-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Clinics by Usage</h3>
				</div>
				<div class="space-y-2 flex-1 min-h-[120px]">
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
								<div class="bg-red-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
							</div>
						</div>
					</div>
					<div v-if="reportGenerated && !isLoadingData && topUsageClinics.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No clinic data available
					</div>
				</div>
			</div>
		</div>

		<!-- Brand Usage Distribution -->
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
			<div class="flex items-center space-x-2 mb-4">
				<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
					<Package class="w-4 h-4 text-purple-600 dark:text-purple-400" />
				</div>
				<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Brand Usage Distribution</h3>
			</div>
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 flex-1 relative">
				<div v-if="reportGenerated && !isLoadingData && usageBrandDistribution.length === 0" class="absolute inset-0 flex items-center justify-center text-sm text-gray-500 dark:text-gray-400 z-10">
					No brand data available
				</div>
				<div class="flex items-center justify-center">
					<canvas ref="usageBrandChartRef" style="max-width:180px;max-height:180px;"></canvas>
				</div>
				<div class="space-y-1.5 flex-1 min-h-[80px]">
					<div v-for="brand in usageBrandDistribution" :key="brand.brand_id" 
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
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import {
	ArrowTrendingUpIcon,
	BeakerIcon
} from '@heroicons/vue/24/outline'
import { Package, Building2 } from 'lucide-vue-next'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps<{
	totalUsageLogs: number
	usageByWoundPart: any[]
	topUsageClinics: any[]
	usageBrandDistribution: any[]
	reportGenerated: boolean
	isLoadingData: boolean
}>()

const usageWoundPartChartRef = ref<HTMLCanvasElement | null>(null)
const usageBrandChartRef = ref<HTMLCanvasElement | null>(null)

let usageWoundPartChartInstance: Chart | null = null
let usageBrandChartInstance: Chart | null = null

function renderUsageWoundPartChart() {
	if (!usageWoundPartChartRef.value) return
	if (usageWoundPartChartInstance) usageWoundPartChartInstance.destroy()
	
	const data = props.usageByWoundPart
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
	
	const brandData = props.usageBrandDistribution
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

onMounted(() => {
	setTimeout(() => {
		renderUsageWoundPartChart()
		renderUsageBrandChart()
	}, 100)
})

watch(() => [props.usageByWoundPart, props.usageBrandDistribution], () => {
	setTimeout(() => {
		renderUsageWoundPartChart()
		renderUsageBrandChart()
	}, 100)
}, { deep: true })
</script>

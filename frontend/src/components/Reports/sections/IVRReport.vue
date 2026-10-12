<template>
	<div class="space-y-5">
		<!-- KPI Cards Row -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Requests</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalIVRRequests }}</p>
					</div>
					<div class="p-2.5 bg-rose-50 dark:bg-rose-900/30 rounded-lg">
						<ShieldCheckIcon class="w-5 h-5 text-rose-600 dark:text-rose-400" />
					</div>
				</div>
			</div>
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Top Status</p>
						<p class="mt-1 text-sm italic font-normal text-gray-400 dark:text-gray-500 capitalize">{{ ivrEligibilityBreakdown[0]?.name || 'No data available' }}</p>
					</div>
					<div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<ClipboardDocumentCheckIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
					</div>
				</div>
			</div>
		</div>

		<!-- Eligibility Status & Manufacturers/Insights -->
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
			<!-- Eligibility Status Breakdown -->
			<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-green-50 dark:bg-green-900/30 rounded-lg">
						<ShieldCheckIcon class="w-4 h-4 text-green-600 dark:text-green-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Eligibility Status</h3>
				</div>
				<div v-if="reportGenerated && !isLoadingData && ivrEligibilityBreakdown.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No IVR data available
					</div>
				<div class="flex items-center justify-center mb-3">
					<canvas ref="ivrEligibilityChartRef" style="max-width:180px;max-height:180px;"></canvas>
				</div>
				<div class="space-y-1.5 flex-1 min-h-[80px]">
					<div v-for="status in ivrEligibilityBreakdown" :key="status.name" 
						class="flex items-center justify-between p-1.5 rounded hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
						<div class="flex items-center space-x-2">
							<div :class="`w-2.5 h-2.5 rounded-full ${status.color}`"></div>
							<span class="text-xs text-gray-700 dark:text-gray-300 capitalize">{{ status.name }}</span>
						</div>
						<span class="text-xs font-semibold text-gray-900 dark:text-white">{{ status.count }}</span>
					</div>
				</div>
			</div>

			<!-- Top Manufacturers OR Manufacturer Insights -->
			<div v-if="manufacturerFilter === 'all'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-orange-50 dark:bg-orange-900/30 rounded-lg">
						<BuildingStorefrontIcon class="w-4 h-4 text-orange-600 dark:text-orange-400" />
					</div>
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Manufacturers</h3>
				</div>
				<div class="space-y-2 flex-1 min-h-[120px]">
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
					<div v-if="reportGenerated && !isLoadingData && topIVRManufacturers.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No manufacturer data available
					</div>
				</div>
			</div>

			<!-- Manufacturer Insights - Show when manufacturer is filtered -->
			<div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
				<div class="flex items-center space-x-2 mb-4">
					<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
						<BuildingStorefrontIcon class="w-4 h-4 text-purple-600 dark:text-purple-400" />
					</div>
					<div class="flex-1 min-w-0">
						<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Manufacturer Insights</h3>
						<p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ manufacturerName }}</p>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-3 mb-3">
					<div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100 dark:border-purple-800">
						<p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Total Requests</p>
						<p class="text-xl font-bold text-gray-900 dark:text-white">{{ totalIVRRequests }}</p>
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
							<span class="text-xs text-gray-500 dark:text-gray-400">({{ ((status.count / totalIVRRequests) * 100).toFixed(0) }}%)</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Clinic Insights or Top Clinics -->
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
			<div class="flex items-center space-x-2 mb-4">
				<div class="p-1.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
					<Building2 class="w-4 h-4 text-red-600 dark:text-red-400" />
				</div>
				<div class="flex-1 min-w-0">
					<h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ clinicFilter !== 'all' ? 'Clinic Insights' : 'Top Clinics by IVR Requests' }}</h3>
					<p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ clinicFilter !== 'all' ? clinicName : 'Highest verification volume' }}</p>
				</div>
			</div>

			<!-- Clinic Insights - Show when clinic is filtered -->
			<div v-if="clinicFilter !== 'all'" class="grid grid-cols-2 gap-3 flex-1">
				<div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-100 dark:border-red-800">
					<p class="text-xs text-red-600 dark:text-red-400 mb-1">Total Requests</p>
					<p class="text-xl font-bold text-gray-900 dark:text-white">{{ totalIVRRequests }}</p>
				</div>
				<div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-100 dark:border-green-800">
					<p class="text-xs text-green-600 dark:text-green-400 mb-1">Approval Rate</p>
					<p class="text-xl font-bold text-gray-900 dark:text-white">{{ clinicApprovalRate }}%</p>
				</div>
			</div>

			<!-- Top Clinics - Show when no specific clinic is selected -->
			<div v-else class="space-y-2 flex-1 min-h-[120px]">
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
							<div class="bg-red-500 h-1.5 rounded-full" :style="{ width: `${clinic.percentage}%` }"></div>
						</div>
					</div>
				</div>
				<div v-if="reportGenerated && !isLoadingData && topIVRClinics.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
					No clinic data available
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue'
import {
	ShieldCheckIcon,
	CheckCircleIcon,
	ClipboardDocumentCheckIcon,
	BuildingStorefrontIcon
} from '@heroicons/vue/24/outline'
import { Building2 } from 'lucide-vue-next'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps<{
	totalIVRRequests: number
	overallApprovalRate: string
	manufacturerApprovalRate: string
	clinicApprovalRate: string
	ivrEligibilityBreakdown: any[]
	topIVRManufacturers: any[]
	topIVRClinics: any[]
	manufacturerFilter: string
	manufacturers: any[]
	clinicFilter: string
	clinics: any[]
	reportGenerated: boolean
	isLoadingData: boolean
}>()

const ivrEligibilityChartRef = ref<HTMLCanvasElement | null>(null)

let ivrEligibilityChartInstance: Chart | null = null

const manufacturerName = computed(() => {
	return props.manufacturers.find((m: any) => String(m.id) === String(props.manufacturerFilter))?.name || 'Selected Manufacturer'
})

const clinicName = computed(() => {
	return props.clinics.find((c: any) => String(c.id) === String(props.clinicFilter))?.name || 'Selected Clinic'
})

function renderIVREligibilityChart() {
	if (!ivrEligibilityChartRef.value) return
	if (ivrEligibilityChartInstance) ivrEligibilityChartInstance.destroy()
	
	const statusData = props.ivrEligibilityBreakdown
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

onMounted(() => {
	setTimeout(() => {
		renderIVREligibilityChart()
	}, 100)
})

watch(() => props.ivrEligibilityBreakdown, () => {
	setTimeout(() => {
		renderIVREligibilityChart()
	}, 100)
}, { deep: true })
</script>

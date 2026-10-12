<template>
	<div class="space-y-5">
		<!-- KPI Cards Row -->
		<div class="grid grid-cols-1 md:grid-cols-3 gap-5">
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
				<div class="flex items-center justify-between">
					<div>
						<p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Invoices</p>
						<p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalInvoices }}</p>
					</div>
					<div class="p-2.5 bg-red-50 dark:bg-red-900/30 rounded-lg">
						<DocumentTextIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
					</div>
				</div>
			</div>
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
			<div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
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
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center">
					<span class="w-1 h-5 bg-red-500 rounded-full mr-2"></span>
					Invoice Status Breakdown
				</h3>
			</div>
			<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 flex-1">
				<!-- Pie Chart -->
				<div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 flex items-center justify-center">
					<canvas ref="invoiceStatusChartRef" style="max-width:100%;max-height:250px;"></canvas>
				</div>
				<!-- Status List -->
				<div class="space-y-2 flex-1 min-h-[120px]">
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
					<div v-if="reportGenerated && !isLoadingData && invoiceStatusBreakdown.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
						No invoice data available
					</div>
				</div>
			</div>
		</div>

		<!-- Top Clinics by Invoice Amount -->
		<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col">
			<div class="flex items-center space-x-2 mb-4">
				<div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-lg">
					<Building2 class="w-4 h-4 text-purple-600 dark:text-purple-400" />
				</div>
				<h3 class="text-sm font-semibold text-gray-900 dark:text-white">Top Clinics by Invoice Amount</h3>
			</div>
			<div class="space-y-2 flex-1 min-h-[120px]">
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
				<div v-if="reportGenerated && !isLoadingData && topInvoiceClinics.length === 0" class="flex-1 flex items-center justify-center py-8 text-sm text-gray-500 dark:text-gray-400">
					No clinic data available
				</div>
			</div>
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import {
	DocumentTextIcon,
	CurrencyDollarIcon,
	ClockIcon
} from '@heroicons/vue/24/outline'
import { Building2 } from 'lucide-vue-next'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps<{
	totalInvoices: number
	totalInvoiceAmount: number
	pendingInvoiceAmount: number
	invoiceStatusBreakdown: any[]
	topInvoiceClinics: any[]
	reportGenerated: boolean
	isLoadingData: boolean
}>()

const invoiceStatusChartRef = ref<HTMLCanvasElement | null>(null)

let invoiceStatusChartInstance: Chart | null = null

function renderInvoiceStatusChart() {
	if (!invoiceStatusChartRef.value) return
	if (invoiceStatusChartInstance) invoiceStatusChartInstance.destroy()
	
	const statusData = props.invoiceStatusBreakdown
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

onMounted(() => {
	setTimeout(() => {
		renderInvoiceStatusChart()
	}, 100)
})

watch(() => props.invoiceStatusBreakdown, () => {
	setTimeout(() => {
		renderInvoiceStatusChart()
	}, 100)
}, { deep: true })
</script>
